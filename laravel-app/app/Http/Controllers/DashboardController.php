<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Device;
use App\Models\SensorData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the Smart Home Dashboard.
     */
    public function index()
    {
        // 1. Fetch all rooms with their devices
        $rooms = Room::with('devices')->get();

        // 2. Fetch latest telemetry values
        $latestTemp = SensorData::where('sensor_type', 'temperature')
            ->latest('created_at')
            ->first();
        
        $latestHum = SensorData::where('sensor_type', 'humidity')
            ->latest('created_at')
            ->first();
        
        $latestLight = SensorData::where('sensor_type', 'light')
            ->latest('created_at')
            ->first();

        $latestMotion = SensorData::where('sensor_type', 'motion')
            ->latest('created_at')
            ->first();

        // 3. Process current metrics
        $indoorTemp = $latestTemp ? floatval($latestTemp->value) : 24.5;
        // Outdoor temperature can be simulated as slightly warmer or cooler
        $outdoorTemp = $indoorTemp + 1.8;
        
        $humidity = $latestHum ? floatval($latestHum->value) : 55.0;
        
        // Solar energy generation is simulated based on LDR light percentage
        $lightPercent = $latestLight ? floatval($latestLight->value) : 40.0;
        $solarKw = round(($lightPercent / 100.0) * 5.2, 2); // Map LDR max to 5.2kW solar peak

        // Online devices are those whose status is set (or count active)
        $totalDevices = Device::count();
        $activeDevicesCount = Device::all()->filter(fn($d) => $d->isActive())->count();

        // Calculate simulated current energy usage based on active devices
        // Lamp uses 15W, Fan uses 60W, Alarm uses 10W, Servo uses 5W
        $activeEnergyUsage = 0.05; // baseline standby usage in kW
        foreach (Device::all() as $device) {
            if ($device->isActive()) {
                if (str_contains(strtolower($device->name), 'lamp')) $activeEnergyUsage += 0.015;
                if (str_contains(strtolower($device->name), 'fan')) $activeEnergyUsage += 0.060;
                if (str_contains(strtolower($device->name), 'alarm')) $activeEnergyUsage += 0.010;
                if (str_contains(strtolower($device->name), 'door')) $activeEnergyUsage += 0.005;
            }
        }
        $activeEnergyUsage = round($activeEnergyUsage, 3);

        // 4. Fetch telemetry history for Chart.js (past 12 records)
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

        // Format history for JavaScript consumption
        $chartData = [
            'labels' => $tempHistory->map(fn($item) => $item->created_at->format('H:i:s'))->values()->toArray(),
            'temperature' => $tempHistory->map(fn($item) => floatval($item->value))->values()->toArray(),
            'humidity' => $humHistory->map(fn($item) => floatval($item->value))->values()->toArray(),
            'light' => $lightHistory->map(fn($item) => floatval($item->value))->values()->toArray(),
        ];

        // Ensure fallback arrays if no records yet
        if (empty($chartData['labels'])) {
            $chartData['labels'] = ['10:00', '11:00', '12:00', '13:00', '14:00'];
            $chartData['temperature'] = [24.0, 24.5, 25.0, 24.8, 24.5];
            $chartData['humidity'] = [50.0, 52.0, 55.0, 53.0, 51.0];
            $chartData['light'] = [30.0, 45.0, 60.0, 55.0, 40.0];
        }

        return view('dashboard', compact(
            'rooms',
            'indoorTemp',
            'outdoorTemp',
            'humidity',
            'solarKw',
            'activeEnergyUsage',
            'totalDevices',
            'activeDevicesCount',
            'latestMotion',
            'latestLight',
            'chartData'
        ));
    }
}
