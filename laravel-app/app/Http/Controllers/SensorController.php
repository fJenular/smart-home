<?php

namespace App\Http\Controllers;

use App\Models\SensorData;
use App\Models\Device;
use Illuminate\Http\JsonResponse;

class SensorController extends Controller
{
    /**
     * Get the latest sensor telemetry.
     */
    public function latest(): JsonResponse
    {
        $latestTemp = SensorData::where('sensor_type', 'temperature')->latest('created_at')->first();
        $latestHum = SensorData::where('sensor_type', 'humidity')->latest('created_at')->first();
        $latestLight = SensorData::where('sensor_type', 'light')->latest('created_at')->first();
        $latestMotion = SensorData::where('sensor_type', 'motion')->latest('created_at')->first();

        // Calculate dynamic solar Kw from LDR percent
        $lightPercent = $latestLight ? floatval($latestLight->value) : 40.0;
        $solarKw = round(($lightPercent / 100.0) * 5.2, 2);

        // Sum active device energy
        $activeEnergyUsage = 0.05;
        foreach (Device::all() as $device) {
            if ($device->isActive()) {
                if (str_contains(strtolower($device->name), 'lamp')) $activeEnergyUsage += 0.015;
                if (str_contains(strtolower($device->name), 'fan')) $activeEnergyUsage += 0.060;
                if (str_contains(strtolower($device->name), 'alarm')) $activeEnergyUsage += 0.010;
                if (str_contains(strtolower($device->name), 'door')) $activeEnergyUsage += 0.005;
            }
        }

        return response()->json([
            'temperature' => $latestTemp ? floatval($latestTemp->value) : 24.5,
            'humidity' => $latestHum ? floatval($latestHum->value) : 55.0,
            'light' => $lightPercent,
            'solar_kw' => $solarKw,
            'energy_usage' => round($activeEnergyUsage, 3),
            'motion' => $latestMotion ? $latestMotion->value : 'NO_MOTION',
            'devices' => Device::all()->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'status' => $d->status,
                'is_active' => $d->isActive()
            ])
        ]);
    }

    /**
     * Get historical telemetry data for chart refreshes.
     */
    public function history(): JsonResponse
    {
        $tempHistory = SensorData::where('sensor_type', 'temperature')
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->reverse();

        $humHistory = SensorData::where('sensor_type', 'humidity')
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->reverse();

        $lightHistory = SensorData::where('sensor_type', 'light')
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->reverse();

        return response()->json([
            'labels' => $tempHistory->map(fn($item) => $item->created_at->format('H:i:s'))->values()->toArray(),
            'temperature' => $tempHistory->map(fn($item) => floatval($item->value))->values()->toArray(),
            'humidity' => $humHistory->map(fn($item) => floatval($item->value))->values()->toArray(),
            'light' => $lightHistory->map(fn($item) => floatval($item->value))->values()->toArray(),
        ]);
    }
}
