<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\SensorService;
use Illuminate\Support\Facades\Log;

class SensorDataProcessorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $topic;
    protected string $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(string $topic, string $payload)
    {
        $this->topic = $topic;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(SensorService $sensorService): void
    {
        Log::info("Handling SensorDataProcessorJob for topic: {$this->topic}");

        // If the topic is a sensor telemetry topic
        if (str_starts_with($this->topic, 'home/') && !str_contains($this->topic, 'control') && !str_contains($this->topic, 'status')) {
            $sensorService->processTelemetry($this->topic, $this->payload);
        }
        // If the topic is a device status reporting topic
        elseif (str_starts_with($this->topic, 'home/status/')) {
            $sensorService->processDeviceStatus($this->topic, $this->payload);
        }
    }
}
