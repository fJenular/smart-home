<?php

namespace App\Services;

use App\Models\SensorData;
use App\Models\Device;
use App\Models\AutomationRule;
use Illuminate\Support\Facades\Log;

class SensorService
{
    protected MQTTService $mqttService;

    public function __construct(MQTTService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Process telemetry data from sensors and check rules.
     */
    public function processTelemetry(string $topic, string $value): void
    {
        $sensorType = $this->determineSensorType($topic);
        if (!$sensorType) {
            Log::warning("Unknown telemetry topic: {$topic}");
            return;
        }

        // Cerdas decode JSON telemetry payload jika berbentuk JSON
        $parsedValue = $value;
        $data = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($data['value'])) {
                $parsedValue = $data['value'];
            } elseif (isset($data['motion'])) {
                // Konversi 1/true menjadi "MOTION", 0/false menjadi "NO_MOTION"
                $parsedValue = ($data['motion'] == 1 || $data['motion'] === 'MOTION' || $data['motion'] === true) ? 'MOTION' : 'NO_MOTION';
            }
        }

        // 1. Save telemetry to DB
        SensorData::create([
            'sensor_type' => $sensorType,
            'value' => (string)$parsedValue,
            'topic' => $topic
        ]);

        // 2. Evaluate automation rules
        $this->evaluateRules($sensorType, (string)$parsedValue);
    }

    /**
     * Process status updates from devices (to keep DB in sync).
     */
    public function processDeviceStatus(string $topic, string $payload): void
    {
        // Topic example: home/status/lamp, Payload example: {"status": "ON"}
        $deviceName = $this->determineDeviceNameFromTopic($topic);
        if (!$deviceName) {
            return;
        }

        $status = $this->extractStatusFromPayload($payload);

        // Update device in DB
        $device = Device::where('topic', 'home/control/' . $deviceName)->first();
        if ($device) {
            $device->update(['status' => $status]);
            Log::info("Synced DB device {$device->name} status to: {$status}");
        }
    }

    /**
     * Helper to map topic to sensor type.
     */
    protected function determineSensorType(string $topic): ?string
    {
        switch ($topic) {
            case 'home/temperature':
                return 'temperature';
            case 'home/humidity':
                return 'humidity';
            case 'home/motion':
                return 'motion';
            case 'home/light':
                return 'light';
            default:
                return null;
        }
    }

    /**
     * Helper to map status topic to device name.
     */
    protected function determineDeviceNameFromTopic(string $topic): ?string
    {
        switch ($topic) {
            case 'home/status/lamp':
                return 'lamp';
            case 'home/status/fan':
                return 'fan';
            case 'home/status/door':
                return 'door';
            case 'home/status/alarm':
                return 'alarm';
            default:
                return null;
        }
    }

    /**
     * Parse the status value from raw payload.
     */
    protected function extractStatusFromPayload(string $payload): string
    {
        $data = json_decode($payload, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($data['status'])) {
            return strtoupper($data['status']);
        }
        
        // Return raw if not JSON
        $val = strtoupper(trim($payload));
        return $val;
    }

    /**
     * Evaluate rules and trigger actions.
     */
    protected function evaluateRules(string $sensorType, string $value): void
    {
        $rules = AutomationRule::where('status', true)->get();

        foreach ($rules as $rule) {
            $condition = $rule->condition; // {"sensor": "temperature", "operator": ">", "value": 30.0}
            $action = $rule->action;       // {"device_id": 2, "status": "ON"}

            if (!isset($condition['sensor']) || $condition['sensor'] !== $sensorType) {
                continue;
            }

            $currentVal = floatval($value);
            $targetVal = floatval($condition['value']);
            $operator = $condition['operator'];
            $isTriggered = false;

            // Handle string comparisons for motion (e.g. MOTION / NO_MOTION)
            if ($sensorType === 'motion') {
                $isTriggered = ($value === $condition['value']);
            } else {
                switch ($operator) {
                    case '>':  $isTriggered = ($currentVal > $targetVal); break;
                    case '<':  $isTriggered = ($currentVal < $targetVal); break;
                    case '>=': $isTriggered = ($currentVal >= $targetVal); break;
                    case '<=': $isTriggered = ($currentVal <= $targetVal); break;
                    case '==': $isTriggered = ($currentVal == $targetVal); break;
                }
            }

            if ($isTriggered) {
                // Find device and execute action
                $device = Device::find($action['device_id']);
                if ($device) {
                    // Only trigger if device is not already in the target state
                    if (strtoupper($device->status) !== strtoupper($action['status'])) {
                        Log::info("Automation rule [{$rule->name}] triggered! Action: Set {$device->name} to {$action['status']}");
                        
                        // Publish control command to MQTT
                        $payload = json_encode(['status' => $action['status']]);
                        $this->mqttService->publish($device->topic, $payload);
                        
                        // Optimistically update status in database (synced later by home/status)
                        $device->update(['status' => $action['status']]);
                    }
                }
            }
        }
    }
}
