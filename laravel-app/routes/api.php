<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SensorController;

Route::post('/devices/{id}/toggle', [DeviceController::class, 'toggle'])->name('api.devices.toggle');
Route::get('/sensors/latest', [SensorController::class, 'latest'])->name('api.sensors.latest');
Route::get('/sensors/history', [SensorController::class, 'history'])->name('api.sensors.history');
