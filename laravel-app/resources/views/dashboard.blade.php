<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AETHER - Single Dashboard Wokwi</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                    boxShadow: {
                        'glow-green': '0 0 15px rgba(34, 197, 94, 0.4)',
                        'glow-red': '0 0 15px rgba(239, 68, 68, 0.4)',
                        'glow-cyan': '0 0 15px rgba(6, 182, 212, 0.4)',
                    }
                }
            }
        }
    </script>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: radial-gradient(circle at 50% 0%, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
        }
        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="text-slate-100 font-sans antialiased p-4 md:p-8" x-data="dashboardHandler({{ json_encode($chartData) }})">

    <div class="max-w-6xl mx-auto space-y-8">
        
        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-center gap-4 glass p-6 rounded-2xl">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center shadow-glow-cyan text-white text-2xl">
                    <i class="fa-solid fa-microchip"></i>
                </div>
                <div>
                    <h1 class="font-outfit font-bold text-2xl tracking-wide bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">WOKWI SMART HOME</h1>
                    <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold mt-1">Single Page Controller</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="h-3 w-3 rounded-full" :class="metrics.devices.length > 0 ? 'bg-green-500 shadow-glow-green animate-pulse' : 'bg-red-500 shadow-glow-red'"></div>
                    <span class="text-sm font-semibold text-slate-300" x-text="metrics.devices.length > 0 ? 'SYSTEM ONLINE' : 'OFFLINE'"></span>
                </div>
                <div class="px-4 py-2 bg-black/30 rounded-xl border border-white/5 font-mono text-cyan-400">
                    <i class="fa-regular fa-clock mr-2"></i><span x-text="timeString"></span>
                </div>
            </div>
        </header>

        <!-- SENSORS GRID (DHT22, LDR, PIR) -->
        <section>
            <h2 class="text-xl font-bold font-outfit mb-4 text-white flex items-center gap-2">
                <i class="fa-solid fa-satellite-dish text-cyan-400"></i> Sensor Readings
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- DHT22 Temperature -->
                <div class="glass p-5 rounded-2xl relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fa-solid fa-temperature-half text-9xl"></i>
                    </div>
                    <div class="flex items-center gap-3 mb-2 text-orange-400">
                        <i class="fa-solid fa-temperature-half text-xl"></i>
                        <span class="font-semibold tracking-wider text-sm">TEMPERATURE</span>
                    </div>
                    <div class="flex items-baseline gap-1 mt-4">
                        <span class="text-4xl font-bold font-outfit" x-text="metrics.temperature.toFixed(1)"></span>
                        <span class="text-xl text-slate-400">°C</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">DHT22 Sensor</p>
                </div>

                <!-- DHT22 Humidity -->
                <div class="glass p-5 rounded-2xl relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fa-solid fa-droplet text-9xl"></i>
                    </div>
                    <div class="flex items-center gap-3 mb-2 text-blue-400">
                        <i class="fa-solid fa-droplet text-xl"></i>
                        <span class="font-semibold tracking-wider text-sm">HUMIDITY</span>
                    </div>
                    <div class="flex items-baseline gap-1 mt-4">
                        <span class="text-4xl font-bold font-outfit" x-text="metrics.humidity.toFixed(0)"></span>
                        <span class="text-xl text-slate-400">%</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">DHT22 Sensor</p>
                </div>

                <!-- LDR Light -->
                <div class="glass p-5 rounded-2xl relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fa-regular fa-sun text-9xl"></i>
                    </div>
                    <div class="flex items-center gap-3 mb-2 text-yellow-400">
                        <i class="fa-regular fa-sun text-xl"></i>
                        <span class="font-semibold tracking-wider text-sm">LIGHT LEVEL</span>
                    </div>
                    <div class="flex items-baseline gap-1 mt-4">
                        <span class="text-4xl font-bold font-outfit" x-text="metrics.light.toFixed(0)"></span>
                        <span class="text-xl text-slate-400">%</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Photoresistor (LDR)</p>
                </div>

                <!-- PIR Motion -->
                <div class="glass p-5 rounded-2xl relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fa-solid fa-person-running text-9xl"></i>
                    </div>
                    <div class="flex items-center gap-3 mb-2 text-emerald-400">
                        <i class="fa-solid fa-person-running text-xl"></i>
                        <span class="font-semibold tracking-wider text-sm">MOTION (PIR)</span>
                    </div>
                    <div class="mt-4 flex items-center h-10">
                        <span class="px-4 py-2 rounded-xl text-sm font-bold tracking-wider"
                              :class="metrics.motion === 'MOTION' ? 'bg-red-500 text-white shadow-glow-red animate-pulse' : 'bg-slate-800 text-emerald-400 border border-emerald-500/30'"
                              x-text="metrics.motion === 'MOTION' ? 'DETECTED' : 'STANDBY'">
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">PIR Sensor</p>
                </div>
            </div>
        </section>

        <!-- ACTUATORS & CHART -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Devices Control (LED & Servo) -->
            <div class="lg:col-span-1 space-y-4">
                <h2 class="text-xl font-bold font-outfit mb-4 text-white flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-cyan-400"></i> Actuator Control
                </h2>
                
                <div class="glass p-6 rounded-2xl space-y-4">
                    <p class="text-sm text-slate-400 mb-6">Control your Wokwi components directly from here.</p>
                    
                    @foreach ($rooms as $room)
                        @foreach ($room->devices as $device)
                        <div class="p-4 rounded-xl border transition-all"
                             :class="isDeviceActiveById({{ $device->id }}) ? 'bg-cyan-900/20 border-cyan-500/50' : 'bg-black/20 border-white/5'">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-full flex items-center justify-center text-xl"
                                         :class="isDeviceActiveById({{ $device->id }}) ? 'bg-cyan-500 text-white shadow-glow-cyan' : 'bg-slate-800 text-slate-400'">
                                        @if (str_contains(strtolower($device->name), 'lamp') || str_contains(strtolower($device->name), 'led'))
                                            <i class="fa-regular fa-lightbulb"></i>
                                        @elseif (str_contains(strtolower($device->name), 'door') || str_contains(strtolower($device->name), 'servo'))
                                            <i class="fa-solid fa-door-open"></i>
                                        @else
                                            <i class="fa-solid fa-power-off"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-white text-lg">{{ $device->name }}</h3>
                                        <p class="text-xs text-slate-400 font-mono">{{ $device->topic }}</p>
                                    </div>
                                </div>
                                
                                <!-- Toggle Switch -->
                                <button @click="toggleDevice({{ $device->id }})"
                                        class="relative w-14 h-8 rounded-full transition-colors duration-300 focus:outline-none"
                                        :class="isDeviceActiveById({{ $device->id }}) ? 'bg-cyan-500' : 'bg-slate-700'">
                                    <div class="absolute top-1 left-1 w-6 h-6 rounded-full bg-white transition-transform duration-300"
                                         :class="isDeviceActiveById({{ $device->id }}) ? 'translate-x-6' : 'translate-x-0'"></div>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @endforeach
                    
                    <div class="text-center pt-4 mt-4 border-t border-white/10">
                        <button @click="fetchSensors()" class="text-sm text-cyan-400 hover:text-cyan-300 font-semibold inline-flex items-center gap-2">
                            <i class="fa-solid fa-rotate-right" :class="loading ? 'animate-spin' : ''"></i>
                            Sync Status
                        </button>
                    </div>
                </div>
            </div>

            <!-- Live Chart -->
            <div class="lg:col-span-2">
                <h2 class="text-xl font-bold font-outfit mb-4 text-white flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-cyan-400"></i> Environmental History
                </h2>
                <div class="glass p-6 rounded-2xl h-full min-h-[300px] flex flex-col">
                    <div class="flex gap-4 mb-4 justify-end">
                        <span class="text-xs bg-orange-500/20 text-orange-400 px-2 py-1 rounded font-semibold border border-orange-500/30"><i class="fa-solid fa-circle text-[8px] mr-1"></i>Temperature</span>
                        <span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-1 rounded font-semibold border border-blue-500/30"><i class="fa-solid fa-circle text-[8px] mr-1"></i>Humidity</span>
                        <span class="text-xs bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded font-semibold border border-yellow-500/30"><i class="fa-solid fa-circle text-[8px] mr-1"></i>Light</span>
                    </div>
                    <div class="flex-1 relative w-full h-full">
                        <canvas id="realtimeChart"></canvas>
                    </div>
                </div>
            </div>

        </section>
    </div>

    <!-- Script Logic (Alpine + Chart.js) -->
    <script>
        function dashboardHandler(initialChartData) {
            return {
                metrics: {
                    temperature: {{ $indoorTemp ?? 0 }},
                    humidity: {{ $humidity ?? 0 }},
                    light: {{ $latestLight ? floatval($latestLight->value) : 0 }},
                    motion: '{{ $latestMotion ? $latestMotion->value : "NO_MOTION" }}',
                    devices: []
                },
                timeString: '',
                loading: false,
                chart: null,

                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                    
                    @php
                        $deviceList = App\Models\Device::all()->map(fn($d) => [
                            'id' => $d->id,
                            'name' => $d->name,
                            'status' => $d->status,
                            'is_active' => $d->isActive()
                        ]);
                    @endphp
                    this.metrics.devices = @json($deviceList);

                    this.initChart(initialChartData);

                    // Poll every 2s
                    setInterval(() => this.fetchSensors(), 2000);
                    setInterval(() => this.fetchChartHistory(), 5000);
                },

                updateTime() {
                    this.timeString = new Date().toLocaleTimeString('en-US', { hour12: false });
                },

                isDeviceActiveById(id) {
                    const dev = this.metrics.devices.find(d => d.id === id);
                    return dev ? dev.is_active : false;
                },

                fetchSensors() {
                    this.loading = true;
                    fetch('/api/sensors/latest')
                        .then(res => res.json())
                        .then(data => {
                            this.metrics.temperature = data.temperature;
                            this.metrics.humidity = data.humidity;
                            this.metrics.light = data.light;
                            this.metrics.motion = data.motion;
                            this.metrics.devices = data.devices;
                            this.loading = false;
                        })
                        .catch(err => {
                            console.error('Error fetching data:', err);
                            this.loading = false;
                        });
                },

                fetchChartHistory() {
                    fetch('/api/sensors/history')
                        .then(res => res.json())
                        .then(data => {
                            if (this.chart) {
                                this.chart.data.labels = data.labels;
                                this.chart.data.datasets[0].data = data.temperature;
                                this.chart.data.datasets[1].data = data.humidity;
                                this.chart.data.datasets[2].data = data.light;
                                this.chart.update('none');
                            }
                        });
                },

                toggleDevice(id) {
                    const dev = this.metrics.devices.find(d => d.id === id);
                    if (!dev) return;
                    
                    const isDoor = dev.name.toLowerCase().includes('door') || dev.name.toLowerCase().includes('servo');
                    const targetStatus = dev.is_active ? (isDoor ? 'LOCKED' : 'OFF') : (isDoor ? 'OPEN' : 'ON');

                    // Optimistic update
                    dev.is_active = !dev.is_active;

                    fetch(`/api/devices/${id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ status: targetStatus })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            alert(data.message);
                            this.fetchSensors(); // Revert
                        }
                    })
                    .catch(err => {
                        console.error('Toggle error:', err);
                        this.fetchSensors(); // Revert
                    });
                },

                initChart(data) {
                    const ctx = document.getElementById('realtimeChart').getContext('2d');
                    this.chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Temperature',
                                    data: data.temperature,
                                    borderColor: '#f97316',
                                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                                    borderWidth: 2,
                                    tension: 0.4,
                                    fill: true
                                },
                                {
                                    label: 'Humidity',
                                    data: data.humidity,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.05)',
                                    borderWidth: 2,
                                    tension: 0.4
                                },
                                {
                                    label: 'Light',
                                    data: data.light,
                                    borderColor: '#eab308',
                                    backgroundColor: 'rgba(234, 179, 8, 0.05)',
                                    borderWidth: 2,
                                    tension: 0.4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { ticks: { color: 'rgba(255,255,255,0.4)', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.05)' } },
                                y: { ticks: { color: 'rgba(255,255,255,0.4)' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                            }
                        }
                    });
                }
            };
        }
    </script>
</body>
</html>
