<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Device;
use App\Models\AutomationRule;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Clear existing data to prevent duplicate seed issues
        AutomationRule::truncate();
        Device::truncate();
        Room::truncate();

        // 2. Create Rooms
        $livingRoom = Room::create(['name' => 'Living Room', 'icon' => 'living']);
        $kitchen = Room::create(['name' => 'Kitchen', 'icon' => 'kitchen']);
        $bedroom = Room::create(['name' => 'Bedroom', 'icon' => 'bedroom']);
        $bathroom = Room::create(['name' => 'Bathroom', 'icon' => 'bathroom']);
        $garage = Room::create(['name' => 'Garage', 'icon' => 'garage']);

        // 3. Create Controllable Devices
        // Living Room Devices
        $lampLiving = Device::create([
            'name' => 'Smart Lamp',
            'room_id' => $livingRoom->id,
            'topic' => 'home/control/lamp',
            'status' => 'OFF'
        ]);

        $fanLiving = Device::create([
            'name' => 'Smart Fan',
            'room_id' => $livingRoom->id,
            'topic' => 'home/control/fan',
            'status' => 'OFF'
        ]);

        // Kitchen Devices
        $alarmKitchen = Device::create([
            'name' => 'Security Alarm',
            'room_id' => $kitchen->id,
            'topic' => 'home/control/alarm',
            'status' => 'OFF'
        ]);

        // Bedroom Devices
        $lampBedroom = Device::create([
            'name' => 'Bed Lamp',
            'room_id' => $bedroom->id,
            'topic' => 'home/control/lamp', // Can share or use distinct subtopics
            'status' => 'OFF'
        ]);

        // Garage Devices
        $doorGarage = Device::create([
            'name' => 'Garage Smart Door',
            'room_id' => $garage->id,
            'topic' => 'home/control/door',
            'status' => 'LOCKED'
        ]);

        // 4. Create Automation Rules
        AutomationRule::create([
            'name' => 'Auto Cooling Fan',
            'condition' => [
                'sensor' => 'temperature',
                'operator' => '>',
                'value' => 30.0
            ],
            'action' => [
                'device_id' => $fanLiving->id,
                'status' => 'ON'
            ],
            'status' => true
        ]);

        AutomationRule::create([
            'name' => 'Security Motion Alarm',
            'condition' => [
                'sensor' => 'motion',
                'operator' => '==',
                'value' => 'MOTION'
            ],
            'action' => [
                'device_id' => $alarmKitchen->id,
                'status' => 'ON'
            ],
            'status' => true
        ]);
    }
}
