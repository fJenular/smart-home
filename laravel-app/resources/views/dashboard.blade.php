<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AETHER - Smart Home Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Outfit', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                    boxShadow: {
                        'glass': '0 8px 32px 0 rgba(0, 0, 0, 0.37)',
                        'glow-green': '0 0 15px rgba(34, 197, 94, 0.4)',
                        'glow-blue': '0 0 15px rgba(59, 130, 246, 0.4)',
                        'glow-red': '0 0 15px rgba(239, 68, 68, 0.4)',
                    }
                }
            }
        }
    </script>
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: radial-gradient(circle at 50% 50%, #111827 0%, #030712 100%);
            min-height: 100vh;
        }
        .glass {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-hover:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="text-slate-100 font-sans antialiased overflow-x-hidden" 
      x-data="dashboardHandler({{ json_encode($chartData) }})">

    <div class="flex min-h-screen">
        
        <!-- SIDEBAR -->
        <aside class="w-64 glass border-r border-white/5 flex flex-col shrink-0">
            <!-- Brand Logo -->
            <div class="p-6 border-b border-white/5 flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-glow-blue">
                    <i class="fa-solid fa-house-signal text-lg"></i>
                </div>
                <div>
                    <h1 class="font-outfit font-bold text-lg tracking-wide bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">AETHER</h1>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest font-semibold">SMART HOME IoT</p>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 px-4 py-6 space-y-1.5">
                <a href="#" class="flex items-center gap-3.5 px-4 py-3 rounded-xl bg-white/5 text-cyan-400 border border-white/10 font-medium transition-all">
                    <i class="fa-solid fa-gauge-high text-lg"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-400 hover:bg-white/5 hover:text-white transition-all">
                    <i class="fa-solid fa-microchip text-lg"></i>
                    <span>Devices</span>
                </a>
                <a href="#" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-400 hover:bg-white/5 hover:text-white transition-all">
                    <i class="fa-solid fa-door-open text-lg"></i>
                    <span>Rooms</span>
                </a>
                <a href="#" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-400 hover:bg-white/5 hover:text-white transition-all">
                    <i class="fa-solid fa-wand-magic-sparkles text-lg"></i>
                    <span>Automation</span>
                </a>
                <a href="#" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-400 hover:bg-white/5 hover:text-white transition-all">
                    <i class="fa-solid fa-bolt text-lg"></i>
                    <span>Energy</span>
                </a>
                <a href="#" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-400 hover:bg-white/5 hover:text-white transition-all">
                    <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                    <span>History</span>
                </a>
                <a href="#" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-400 hover:bg-white/5 hover:text-white transition-all">
                    <i class="fa-solid fa-sliders text-lg"></i>
                    <span>Settings</span>
                </a>
            </nav>

            <!-- Bottom Profile & Status -->
            <div class="p-4 border-t border-white/5">
                <div class="p-3.5 rounded-xl bg-white/5 border border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="h-2 w-2 rounded-full bg-green-500 animate-ping shadow-glow-green"></div>
                        <span class="text-xs font-semibold text-slate-300">SYSTEM RUNNING</span>
                    </div>
                    <span class="text-[10px] bg-cyan-500/20 text-cyan-400 px-2 py-0.5 rounded font-mono border border-cyan-500/30">SIJA SMK</span>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-8 overflow-y-auto">
            
            <!-- HEADER -->
            <header class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-bold font-outfit text-white">Hello, Chief!</h2>
                    <p class="text-sm text-slate-400 mt-1">Welcome back. Everything in your home looks perfect.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="glass px-4 py-2.5 rounded-xl border border-white/5 flex items-center gap-2">
                        <i class="fa-solid fa-clock text-cyan-400"></i>
                        <span class="text-sm font-mono text-slate-200" x-text="timeString">--:--:--</span>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center cursor-pointer hover:bg-white/10 transition-all">
                        <i class="fa-solid fa-bell text-slate-300"></i>
                    </div>
                </div>
            </header>

            <!-- TOP STATISTICS -->
            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-5 mb-8">
                <!-- Indoor Temp -->
                <div class="glass p-5 rounded-2xl border border-white/5 transition-all">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-xs text-slate-400 font-semibold tracking-wider uppercase">IN TEMP</span>
                        <div class="p-2 bg-orange-500/10 rounded-xl text-orange-400">
                            <i class="fa-solid fa-thermometer text-lg"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold font-outfit text-white" x-text="metrics.temperature.toFixed(1)">--.-</span>
                        <span class="text-lg text-slate-400 font-medium">°C</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Target set to 24.0°C</p>
                </div>

                <!-- Outdoor Temp -->
                <div class="glass p-5 rounded-2xl border border-white/5 transition-all">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-xs text-slate-400 font-semibold tracking-wider uppercase">OUT TEMP</span>
                        <div class="p-2 bg-amber-500/10 rounded-xl text-amber-400">
                            <i class="fa-solid fa-cloud-sun text-lg"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold font-outfit text-white" x-text="metrics.outdoor_temp.toFixed(1)">--.-</span>
                        <span class="text-lg text-slate-400 font-medium">°C</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Humid & Cloudy</p>
                </div>

                <!-- Humidity -->
                <div class="glass p-5 rounded-2xl border border-white/5 transition-all">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-xs text-slate-400 font-semibold tracking-wider uppercase">HUMIDITY</span>
                        <div class="p-2 bg-blue-500/10 rounded-xl text-blue-400">
                            <i class="fa-solid fa-droplet text-lg"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold font-outfit text-white" x-text="metrics.humidity.toFixed(0)">--</span>
                        <span class="text-lg text-slate-400 font-medium">%</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Optimal air condition</p>
                </div>

                <!-- Energy Usage -->
                <div class="glass p-5 rounded-2xl border border-white/5 transition-all">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-xs text-slate-400 font-semibold tracking-wider uppercase">POWER DRAW</span>
                        <div class="p-2 bg-yellow-500/10 rounded-xl text-yellow-400">
                            <i class="fa-solid fa-bolt text-lg"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold font-outfit text-white" x-text="metrics.energy_usage.toFixed(3)">-.---</span>
                        <span class="text-xs text-slate-400 font-semibold uppercase ml-1">kW</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Dynamic power usage</p>
                </div>

                <!-- Solar Energy -->
                <div class="glass p-5 rounded-2xl border border-white/5 transition-all">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-xs text-slate-400 font-semibold tracking-wider uppercase">SOLAR GEN</span>
                        <div class="p-2 bg-emerald-500/10 rounded-xl text-emerald-400">
                            <i class="fa-solid fa-solar-panel text-lg"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold font-outfit text-white" x-text="metrics.solar_kw.toFixed(2)">--.--</span>
                        <span class="text-xs text-slate-400 font-semibold uppercase ml-1">kW</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2" x-text="'Sunlight: ' + metrics.light.toFixed(0) + '%'"></p>
                </div>

                <!-- Online Devices -->
                <div class="glass p-5 rounded-2xl border border-white/5 transition-all">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-xs text-slate-400 font-semibold tracking-wider uppercase">DEVICES</span>
                        <div class="p-2 bg-indigo-500/10 rounded-xl text-indigo-400">
                            <i class="fa-solid fa-circle-check text-lg"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold font-outfit text-white" x-text="activeDevicesCount">--</span>
                        <span class="text-lg text-slate-400 font-medium" x-text="'/ ' + totalDevices"></span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Devices online</p>
                </div>
            </section>

            <!-- REALTIME MONITORING & CHARTS -->
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                
                <!-- Telemetry Status Panel -->
                <div class="glass p-6 rounded-2xl border border-white/5 flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-semibold font-outfit text-white mb-4">Realtime Status</h3>
                        <div class="space-y-4">
                            <!-- Temp -->
                            <div class="flex justify-between items-center py-2 border-b border-white/5">
                                <span class="text-slate-400 text-sm"><i class="fa-solid fa-temperature-half w-5 text-orange-400"></i>Temperature</span>
                                <span class="font-semibold text-white font-mono text-sm" x-text="metrics.temperature.toFixed(1) + ' °C'"></span>
                            </div>
                            <!-- Humid -->
                            <div class="flex justify-between items-center py-2 border-b border-white/5">
                                <span class="text-slate-400 text-sm"><i class="fa-solid fa-droplet w-5 text-blue-400"></i>Humidity</span>
                                <span class="font-semibold text-white font-mono text-sm" x-text="metrics.humidity.toFixed(1) + ' %'"></span>
                            </div>
                            <!-- Light -->
                            <div class="flex justify-between items-center py-2 border-b border-white/5">
                                <span class="text-slate-400 text-sm"><i class="fa-solid fa-sun w-5 text-yellow-400"></i>Light Level</span>
                                <span class="font-semibold text-white font-mono text-sm" x-text="metrics.light.toFixed(1) + ' %'"></span>
                            </div>
                            <!-- Motion -->
                            <div class="flex justify-between items-center py-2 border-b border-white/5">
                                <span class="text-slate-400 text-sm"><i class="fa-solid fa-person-running w-5 text-cyan-400"></i>Motion Detection</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :class="metrics.motion === 'MOTION' ? 'bg-red-500/20 text-red-400 shadow-glow-red animate-pulse' : 'bg-slate-500/20 text-slate-400'"
                                      x-text="metrics.motion === 'MOTION' ? 'DETECTED' : 'SECURE'">
                                </span>
                            </div>
                            <!-- Door -->
                            <div class="flex justify-between items-center py-2">
                                <span class="text-slate-400 text-sm"><i class="fa-solid fa-door-closed w-5 text-emerald-400"></i>Smart Door</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :class="isDeviceActive('Door') ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400'"
                                      x-text="isDeviceActive('Door') ? 'UNLOCKED / OPEN' : 'LOCKED'">
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-white/5 flex items-center justify-between text-xs text-slate-400">
                        <span>Last sync: <span x-text="lastUpdateString">Just now</span></span>
                        <button @click="fetchSensors()" class="text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1 transition-all">
                            <i class="fa-solid fa-arrows-rotate" :class="loading ? 'animate-spin' : ''"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- Charts -->
                <div class="glass p-6 rounded-2xl border border-white/5 lg:col-span-2">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold font-outfit text-white">Live Environmental History</h3>
                        <div class="flex gap-2">
                            <span class="text-[10px] bg-orange-500/20 text-orange-400 px-2 py-0.5 rounded font-semibold border border-orange-500/30">Temp (°C)</span>
                            <span class="text-[10px] bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded font-semibold border border-blue-500/30">Humidity (%)</span>
                            <span class="text-[10px] bg-yellow-500/20 text-yellow-400 px-2 py-0.5 rounded font-semibold border border-yellow-500/30">Light (%)</span>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="realtimeChart"></canvas>
                    </div>
                </div>

            </section>

            <!-- SMART ROOMS SECTION -->
            <section class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold font-outfit text-white">Smart Rooms Control</h3>
                    <p class="text-xs text-slate-400">Configure devices in real-time</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
                    @foreach ($rooms as $room)
                    <div class="glass p-6 rounded-2xl border border-white/5 flex flex-col justify-between transition-all hover:border-white/10">
                        <div>
                            <!-- Room Header -->
                            <div class="flex justify-between items-center mb-5">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-white/5 border border-white/5 flex items-center justify-center text-slate-300">
                                        @if ($room->icon === 'living')
                                            <i class="fa-solid fa-couch text-lg text-cyan-400"></i>
                                        @elseif ($room->icon === 'kitchen')
                                            <i class="fa-solid fa-kitchen-set text-lg text-yellow-400"></i>
                                        @elseif ($room->icon === 'bedroom')
                                            <i class="fa-solid fa-bed text-lg text-purple-400"></i>
                                        @elseif ($room->icon === 'bathroom')
                                            <i class="fa-solid fa-bath text-lg text-blue-400"></i>
                                        @else
                                            <i class="fa-solid fa-warehouse text-lg text-emerald-400"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-white text-base leading-tight">{{ $room->name }}</h4>
                                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">
                                            {{ $room->devices->count() }} Devices
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Room Devices List -->
                            <div class="space-y-4">
                                @foreach ($room->devices as $device)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5">
                                    <div class="flex items-center gap-2.5">
                                        <!-- Device Icon -->
                                        <span class="text-slate-400 text-sm">
                                            @if (str_contains(strtolower($device->name), 'lamp'))
                                                <i class="fa-regular fa-lightbulb" :class="isDeviceActiveById({{ $device->id }}) ? 'text-yellow-400' : ''"></i>
                                            @elseif (str_contains(strtolower($device->name), 'fan'))
                                                <i class="fa-solid fa-fan" :class="isDeviceActiveById({{ $device->id }}) ? 'text-cyan-400 animate-spin' : ''"></i>
                                            @elseif (str_contains(strtolower($device->name), 'door') || str_contains(strtolower($device->name), 'lock'))
                                                <i class="fa-solid fa-key" :class="isDeviceActiveById({{ $device->id }}) ? 'text-emerald-400' : ''"></i>
                                            @elseif (str_contains(strtolower($device->name), 'plug'))
                                                <i class="fa-solid fa-plug" :class="isDeviceActiveById({{ $device->id }}) ? 'text-blue-400' : ''"></i>
                                            @elseif (str_contains(strtolower($device->name), 'alarm'))
                                                <i class="fa-solid fa-triangle-exclamation" :class="isDeviceActiveById({{ $device->id }}) ? 'text-red-500 animate-bounce' : ''"></i>
                                            @else
                                                <i class="fa-solid fa-toggle-on"></i>
                                            @endif
                                        </span>
                                        <div>
                                            <p class="text-xs font-semibold text-slate-200">{{ $device->name }}</p>
                                            <span class="text-[9px] font-mono text-slate-400 lowercase">{{ $device->topic }}</span>
                                        </div>
                                    </div>

                                    <!-- Switch -->
                                    <button 
                                        @click="toggleDevice({{ $device->id }}, isDeviceActiveById({{ $device->id }}) ? 'OFF' : 'ON')"
                                        class="w-10 h-5 rounded-full p-0.5 transition-all duration-300 focus:outline-none"
                                        :class="isDeviceActiveById({{ $device->id }}) ? 'bg-cyan-500' : 'bg-slate-600'"
                                    >
                                        <div 
                                            class="w-4 h-4 rounded-full bg-white shadow-md transform transition-all duration-300"
                                            :class="isDeviceActiveById({{ $device->id }}) ? 'translate-x-5' : 'translate-x-0'"
                                        ></div>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
        </main>
    </div>

    <!-- Script logic -->
    <script>
        function dashboardHandler(initialChartData) {
            return {
                metrics: {
                    temperature: {{ $indoorTemp }},
                    outdoor_temp: {{ $outdoorTemp }},
                    humidity: {{ $humidity }},
                    light: {{ $latestLight ? floatval($latestLight->value) : 40.0 }},
                    solar_kw: {{ $solarKw }},
                    energy_usage: {{ $activeEnergyUsage }},
                    motion: '{{ $latestMotion ? $latestMotion->value : "NO_MOTION" }}',
                    devices: []
                },
                totalDevices: {{ $totalDevices }},
                activeDevicesCount: {{ $activeDevicesCount }},
                timeString: '',
                lastUpdateString: 'Just now',
                loading: false,
                chart: null,

                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                    
                    // Populate initial device list from PHP
                    @php
                        $deviceList = App\Models\Device::all()->map(fn($d) => [
                            'id' => $d->id,
                            'name' => $d->name,
                            'status' => $d->status,
                            'is_active' => $d->isActive()
                        ]);
                    @endphp
                    this.metrics.devices = @json($deviceList);

                    // Initialize Chart
                    this.initChart(initialChartData);

                    // Start polling sensors/devices every 2 seconds
                    setInterval(() => this.fetchSensors(), 2000);
                    setInterval(() => this.fetchChartHistory(), 5000);
                },

                updateTime() {
                    const now = new Date();
                    this.timeString = now.toLocaleTimeString('en-US', { hour12: false });
                },

                isDeviceActive(nameSnippet) {
                    const dev = this.metrics.devices.find(d => d.name.toLowerCase().includes(nameSnippet.toLowerCase()));
                    return dev ? dev.is_active : false;
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
                            this.metrics.outdoor_temp = data.temperature + 1.8;
                            this.metrics.humidity = data.humidity;
                            this.metrics.light = data.light;
                            this.metrics.solar_kw = data.solar_kw;
                            this.metrics.energy_usage = data.energy_usage;
                            this.metrics.motion = data.motion;
                            
                            // Synchronize UI switch states
                            this.metrics.devices = data.devices;
                            
                            // Recalculate counts
                            this.activeDevicesCount = data.devices.filter(d => d.is_active).length;
                            
                            const now = new Date();
                            this.lastUpdateString = now.toLocaleTimeString('en-US', { hour12: false });
                            this.loading = false;
                        })
                        .catch(err => {
                            console.error('Error fetching sensor state:', err);
                            this.loading = false;
                        });
                },

                fetchChartHistory() {
                    fetch('/api/sensors/history')
                        .then(res => res.json())
                        .then(data => {
                            this.updateChart(data);
                        });
                },

                toggleDevice(id, status) {
                    const dev = this.metrics.devices.find(d => d.id === id);
                    if (dev) {
                        // Optimistically toggle UI switch state locally
                        dev.is_active = (status === 'ON');
                    }

                    fetch(`/api/devices/${id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ status: status })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            console.log(data.message);
                            // Refresh metrics to sync
                            this.fetchSensors();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(err => {
                        console.error('Error toggling device:', err);
                        // Revert optimistic change on error
                        this.fetchSensors();
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
                                    label: 'Temperature (°C)',
                                    data: data.temperature,
                                    borderColor: 'rgba(249, 115, 22, 1)',
                                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                                    borderWidth: 2.5,
                                    tension: 0.4,
                                    fill: true,
                                    yAxisID: 'yTemp'
                                },
                                {
                                    label: 'Humidity (%)',
                                    data: data.humidity,
                                    borderColor: 'rgba(59, 130, 246, 1)',
                                    backgroundColor: 'rgba(59, 130, 246, 0.05)',
                                    borderWidth: 2,
                                    tension: 0.4,
                                    yAxisID: 'yHum'
                                },
                                {
                                    label: 'Light Level (%)',
                                    data: data.light,
                                    borderColor: 'rgba(234, 179, 8, 1)',
                                    backgroundColor: 'rgba(234, 179, 8, 0.05)',
                                    borderWidth: 1.5,
                                    tension: 0.4,
                                    yAxisID: 'yLight'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.03)'
                                    },
                                    ticks: {
                                        color: 'rgba(255, 255, 255, 0.4)',
                                        font: {
                                            size: 9
                                        }
                                    }
                                },
                                yTemp: {
                                    type: 'linear',
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Temp (°C)',
                                        color: 'rgba(249, 115, 22, 0.8)'
                                    },
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.03)'
                                    },
                                    ticks: {
                                        color: 'rgba(255, 255, 255, 0.5)'
                                    }
                                },
                                yHum: {
                                    type: 'linear',
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Hum (%) / Light (%)',
                                        color: 'rgba(59, 130, 246, 0.8)'
                                    },
                                    grid: {
                                        drawOnChartArea: false
                                    },
                                    ticks: {
                                        color: 'rgba(255, 255, 255, 0.5)'
                                    },
                                    min: 0,
                                    max: 100
                                },
                                yLight: {
                                    display: false,
                                    min: 0,
                                    max: 100
                                }
                            }
                        }
                    });
                },

                updateChart(data) {
                    if (this.chart) {
                        this.chart.data.labels = data.labels;
                        this.chart.data.datasets[0].data = data.temperature;
                        this.chart.data.datasets[1].data = data.humidity;
                        this.chart.data.datasets[2].data = data.light;
                        this.chart.update('none'); // Update without animation for smooth realtime look
                    }
                }
            };
        }
    </script>
</body>
</html>
