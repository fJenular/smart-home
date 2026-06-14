<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\MQTTService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    protected MQTTService $mqttService;

    public function __construct(MQTTService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Toggle device status (ON/OFF).
     */
    public function toggle(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:ON,OFF,OPEN,LOCKED,status'
        ]);

        $device = Device::findOrFail($id);
        $status = strtoupper($request->status);

        // Map general naming for payload structure
        $deviceName = strtolower(str_replace('Smart ', '', $device->name));
        $deviceName = explode(' ', $deviceName)[0]; // e.g. "lamp" or "fan" or "door"

        // Prepare JSON payload matching user requirements
        $payload = json_encode([
            'device' => $deviceName,
            'status' => $status
        ]);

        // Publish to MQTT topic
        $publishSuccess = $this->mqttService->publish($device->topic, $payload);

        if ($publishSuccess) {
            // Optimistically update device status in database
            // (The ESP32 status callback will verify and lock this status)
            $device->update(['status' => $status]);

            return response()->json([
                'success' => true,
                'message' => "Successfully sent {$status} command to {$device->name}",
                'status' => $status
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Failed to communicate with MQTT Broker"
        ], 500);
    }
}
