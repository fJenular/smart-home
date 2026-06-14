<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Log;

class MQTTService
{
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $password;
    protected string $clientId;

    public function __construct()
    {
        $this->host = config('mqtt.host', env('MQTT_HOST', 'broker.shiftr.io'));
        $this->port = (int) config('mqtt.port', env('MQTT_PORT', 1883));
        $this->username = config('mqtt.username', env('MQTT_USERNAME', 'jenul'));
        $this->password = config('mqtt.password', env('MQTT_PASSWORD', ''));
        $this->clientId = 'laravel_client_' . uniqid();
    }

    /**
     * Publish a message to a specific topic.
     */
    public function publish(string $topic, string $message, bool $retain = false): bool
    {
        try {
            $mqtt = new MqttClient($this->host, $this->port, $this->clientId);
            
            $settings = (new ConnectionSettings)
                ->setUsername($this->username)
                ->setPassword($this->password)
                ->setKeepAliveInterval(60);

            $mqtt->connect($settings, true);
            $mqtt->publish($topic, $message, 0, $retain);
            $mqtt->disconnect();

            Log::info("MQTT Published to {$topic}: {$message}");
            return true;
        } catch (\Exception $e) {
            Log::error("MQTT Publish failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Start a subscription loop on a specific topic.
     * Note: This is a blocking action and should be run in a daemon or console command.
     */
    public function subscribe(array $topics, callable $callback): void
    {
        $mqtt = new MqttClient($this->host, $this->port, $this->clientId);
        
        $settings = (new ConnectionSettings)
            ->setUsername($this->username)
            ->setPassword($this->password)
            ->setKeepAliveInterval(60);

        $mqtt->connect($settings, true);

        foreach ($topics as $topic) {
            $mqtt->subscribe($topic, function ($topic, $message) use ($callback) {
                Log::debug("MQTT Received on {$topic}: {$message}");
                $callback($topic, $message);
            }, 0);
        }

        // Loop to process incoming messages (runs indefinitely)
        $mqtt->loop(true);
    }
}
