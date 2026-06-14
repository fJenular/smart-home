<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MQTTService;
use App\Jobs\SensorDataProcessorJob;
use Illuminate\Support\Facades\Log;

class MqttListen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the MQTT Listener daemon to subscribe and capture telemetry';

    protected MQTTService $mqttService;

    public function __construct(MQTTService $mqttService)
    {
        parent::__construct();
        $this->mqttService = $mqttService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Starting Smart Home MQTT Listener Daemon...");
        $this->info("Subscribing to broker.shiftr.io...");

        $topics = [
            'home/temperature',
            'home/humidity',
            'home/motion',
            'home/light',
            'home/status/lamp',
            'home/status/fan',
            'home/status/door',
            'home/status/alarm'
        ];

        try {
            $this->mqttService->subscribe($topics, function (string $topic, string $message) {
                $this->info("Received message on [{$topic}]: {$message}");
                
                // Dispatch job to process database write and rule logic
                SensorDataProcessorJob::dispatch($topic, $message);
            });
        } catch (\Exception $e) {
            $this->error("MQTT Listener connection error: " . $e->getMessage());
            Log::error("MQTT daemon exception: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
