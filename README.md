# AETHER - Realtime Smart Home IoT Dashboard
> **Laporan Tugas Akhir Praktik - Jurusan Sistem Informatika Jaringan dan Aplikasi (SIJA)**

Aplikasi Web Smart Home Dashboard berbasis IoT ini dirancang untuk memantau sensor (suhu, kelembapan, cahaya, gerakan) dan mengontrol aktuator (lampu, kipas angin, servo pintu, sirine alarm) secara realtime. Sistem ini menghubungkan Simulator Wokwi ESP32 dengan Laravel 12 melalui protokol MQTT menggunakan Broker Shiftr.io dan menyimpan histori data di database Supabase PostgreSQL.

---

## 1. Arsitektur Sistem (Realtime Architecture)

Sistem ini terbagi menjadi dua arah komunikasi utama:
1. **Arah Sensor (Telemetry Flow)**: ESP32 membaca data fisik -> Publikasi MQTT ke Shiftr.io -> Daemon Laravel mendeteksi pesan masuk -> Data diproses asinkron & disimpan ke database Supabase -> Dashboard diupdate via Polling Realtime.
2. **Arah Kontrol (Control Loop Flow)**: Switch Dashboard ditekan -> HTTP POST API dipanggil -> Laravel mempublikasikan payload JSON ke MQTT Broker -> ESP32 menerima payload -> ESP32 mengeksekusi pin hardware.

### Diagram Arsitektur (ASCII)

```
+---------------------------------------------------------------------------------+
|                               SYSTEM ARCHITECTURE                               |
+---------------------------------------------------------------------------------+

     [ ESP32 (Wokwi Sim) ]                               [ Laravel Web App ]
       |             ^                                     |             ^
       |             |                                     |             |
       | Publish     | Subscribe                           | Publish     | Subscribe
       | Telemetry   | Commands                            | Commands    | Telemetry
       v             |                                     v             |
    +-------------------------------------------------------------------------+
    |                             Shiftr.io Broker                            |
    |                         (broker.shiftr.io:1883)                         |
    +-------------------------------------------------------------------------+
                                                           |             ^
                                                           |             |
                                              Writes to DB |             | HTTP API Polling
                                                           v             | (Alpine.js Web App)
                                                     [ Supabase PG ] ----+
                                                      (PostgreSQL)
```

---

## 2. Struktur Folder Project

```
smart-home-sija/
├── wokwi-esp32/                     # IoT Simulation Files
│   ├── diagram.json                 # Wiring layout virtual Wokwi
│   ├── wokwi-project.json           # Wokwi project config & libraries
│   └── wokwi-esp32.ino              # C++ Arduino code for ESP32
└── laravel-app/                     # Web Application Backend/Frontend
    ├── app/
    │   ├── Console/Commands/
    │   │   └── MqttListen.php       # Daemon/Background worker listener
    │   ├── Http/Controllers/
    │   │   ├── DashboardController.php
    │   │   ├── DeviceController.php # Command publisher toggle
    │   │   └── SensorController.php  # API telemetry feeds
    │   ├── Jobs/
    │   │   └── SensorDataProcessorJob.php # Asynchronous handler
    │   ├── Models/
    │   │   ├── AutomationRule.php
    │   │   ├── Device.php
    │   │   ├── Room.php
    │   │   └── SensorData.php
    │   └── Services/
    │       ├── MQTTService.php
    │       └── SensorService.php    # Business logic & automation rules
    ├── config/
    │   └── mqtt.php                 # MQTT Client credentials
    ├── database/
    │   ├── migrations/
    │   │   ├── 2026_06_14_000001_create_rooms_table.php
    │   │   ├── 2026_06_14_000002_create_devices_table.php
    │   │   ├── 2026_06_14_000003_create_sensor_data_table.php
    │   │   └── 2026_06_14_000004_create_automation_rules_table.php
    │   └── seeders/
    │       └── DatabaseSeeder.php   # Initial rooms & devices
    ├── resources/
    │   └── views/
    │       └── dashboard.blade.php  # Glassmorphism dark web interface
    ├── routes/
    │   ├── api.php                  # API endpoints
    │   └── web.php                  # Web views
    ├── .env                         # Environment settings (with credentials)
    ├── .env.example
    └── composer.json                # Project declarations & php-mqtt
```

---

## 3. Desain Database (ERD)

Database di-host di Supabase PostgreSQL dengan skema relasi sebagai berikut:

1. **rooms**: Menyimpan nama dan ikon visual ruangan.
   - `id` (BIGINT, Primary Key, Auto Increment)
   - `name` (VARCHAR) - Nama ruangan (Living Room, Kitchen, dll)
   - `icon` (VARCHAR) - Ikon CSS/FontAwesome
   - `created_at` / `updated_at` (TIMESTAMP)

2. **devices**: Menyimpan daftar aktuator yang dikontrol.
   - `id` (BIGINT, Primary Key, Auto Increment)
   - `name` (VARCHAR) - Nama perangkat (Smart Lamp, Smart Fan, dll)
   - `room_id` (BIGINT, Foreign Key references `rooms.id` on cascade)
   - `topic` (VARCHAR) - MQTT Topic kontrol
   - `status` (VARCHAR, default 'OFF') - Status (ON/OFF/LOCKED/OPEN)
   - `created_at` / `updated_at` (TIMESTAMP)

3. **sensor_data**: Menyimpan log sensor untuk grafik riwayat (Time Series).
   - `id` (BIGINT, Primary Key, Auto Increment)
   - `sensor_type` (VARCHAR) - Jenis data (temperature, humidity, light, motion)
   - `value` (VARCHAR) - Data sensor (e.g. '28.5', 'MOTION')
   - `topic` (VARCHAR) - MQTT Topic asal
   - `created_at` (TIMESTAMP, default current_timestamp)

4. **automation_rules**: Aturan if-this-then-that (IFTTT).
   - `id` (BIGINT, Primary Key, Auto Increment)
   - `name` (VARCHAR) - Nama aturan
   - `condition` (TEXT/JSON) - e.g. `{"sensor": "temperature", "operator": ">", "value": 30.0}`
   - `action` (TEXT/JSON) - e.g. `{"device_id": 2, "status": "ON"}`
   - `status` (BOOLEAN) - Aturan aktif / tidak aktif
   - `created_at` / `updated_at` (TIMESTAMP)

---

## 4. Panduan Simulasi Wokwi (ESP32 Setup)

### Wiring Diagram (Koneksi Pin)

| Komponen | Pin ESP32 | Jenis Pin | Deskripsi Fungsi |
| :--- | :--- | :--- | :--- |
| **DHT22** | GPIO 15 | Digital (SDA) | Membaca Suhu & Kelembapan ruangan |
| **LED Merah** | GPIO 2 | Digital Out | Smart Lamp (Indikator Lampu Ruang) |
| **Relay Module** | GPIO 4 | Digital Out | Smart Fan (Aktuator Kipas Angin) |
| **PIR Sensor** | GPIO 12 | Digital In | Motion Detector (Sensor Gerakan) |
| **Servo Motor** | GPIO 13 | PWM Out | Smart Door Lock (Gerbang/Pintu Garasi) |
| **LDR Sensor** | GPIO 34 | Analog In (ADC)| Mengukur intensitas cahaya (Solar panel simulation) |
| **LED Kuning** | GPIO 14 | Digital Out | Sirene Alarm (Sistem Keamanan) |

### File Konfigurasi Wokwi

Seluruh file konfigurasi simulasi telah dibuat di folder [wokwi-esp32/](file:///c:/Users/Lutfan%20Izzat/OneDrive/Documents/Jen/take-your-time/smart-home-sija/wokwi-esp32/):
1. [diagram.json](file:///c:/Users/Lutfan%20Izzat/OneDrive/Documents/Jen/take-your-time/smart-home-sija/wokwi-esp32/diagram.json): Mendefinisikan letak koordinat dan kabel sirkuit virtual.
2. [wokwi-project.json](file:///c:/Users/Lutfan%20Izzat/OneDrive/Documents/Jen/take-your-time/smart-home-sija/wokwi-esp32/wokwi-project.json): Daftar library compiler Wokwi (`PubSubClient`, `DHTesp`, `ESP32Servo`, `ArduinoJson`).
3. [wokwi-esp32.ino](file:///c:/Users/Lutfan%20Izzat/OneDrive/Documents/Jen/take-your-time/smart-home-sija/wokwi-esp32/wokwi-esp32.ino): Kode firmware ESP32 lengkap.

---

## 5. Panduan Konfigurasi Shiftr.io

Shiftr.io adalah broker MQTT berbasis visualisasi grafis yang sangat interaktif dan cocok untuk demonstrasi tugas sekolah.

### Cara Pembuatan Account & Broker:
1. Buka website [shiftr.io](https://shiftr.io) di browser Anda.
2. Daftar akun baru secara gratis.
3. Setelah login, klik **Create Namespace** atau buat instance broker baru (misalnya diberi nama `jenular-smarthome`).
4. Masuk ke tab **Credentials**, lalu catat **Token Username** dan **Token Password** (Token username default biasanya adalah nama namespace Anda, contoh: `jenul`).
5. Pada panel utama, Anda dapat melihat visualisasi grafis realtime berupa node (lingkaran) yang saling terhubung saat ESP32 dan Laravel mulai mengirim data (Publish/Subscribe).

### Pengujian Menggunakan MQTT Explorer:
1. Download dan buka aplikasi **MQTT Explorer**.
2. Masukkan konfigurasi koneksi:
   - **Host**: `broker.shiftr.io`
   - **Port**: `1883`
   - **Username**: `jenul` (Token Username Anda)
   - **Password**: *(Token Password)*
3. Klik **Connect**.
4. Di panel sebelah kanan (Publish), coba publish data manual:
   - Topic: `home/control/lamp`
   - Payload (JSON): `{"status": "ON"}` atau `{"device":"lamp","status":"ON"}`
5. Verifikasi apakah status LED pada Wokwi berubah menjadi menyala.

---

## 6. Panduan Instalasi Laravel & Supabase

### Prerequisites (Prasyarat):
- **PHP**: versi 8.3
- **Composer**: Dependency Manager PHP
- **Git**

### Langkah-langkah Instalasi:
1. Masuk ke direktori laravel app:
   ```bash
   cd smart-home-sija/laravel-app
   ```
2. Instal dependensi PHP menggunakan Composer:
   ```bash
   composer install
   ```
3. Copy file `.env.example` menjadi `.env` (Jika belum terisi kunci enkripsinya):
   ```bash
   copy .env.example .env
   ```
4. Generate Application Key:
   ```bash
   php artisan key:generate
   ```
5. Konfigurasi koneksi database Supabase PostgreSQL dan MQTT di `.env` (Sudah terisi default sesuai konfigurasi Anda):
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=db.jjyvzcsxuhwnhshjgszt.supabase.co
   DB_PORT=5432
   DB_DATABASE=postgres
   DB_USERNAME=postgres
   DB_PASSWORD=smarthomedb123

   MQTT_HOST=broker.shiftr.io
   MQTT_PORT=1883
   MQTT_USERNAME=jenul
   MQTT_PASSWORD=""
   ```
6. Jalankan Migrasi Database dan Seed Awal:
   ```bash
   php artisan migrate --seed
   ```
   *Perintah ini akan membuat tabel-tabel di Supabase PostgreSQL dan mengisi otomatis ruangan beserta perangkat kontrolnya.*

---

## 7. Panduan Pengujian Sistem (Testing Guide)

Untuk menguji interkoneksi realtime secara lengkap, jalankan 3 sistem secara bersamaan:

### Langkah 1: Jalankan Web Server Laravel
Di terminal pertama, jalankan perintah web server:
```bash
php artisan serve
```
Akses dashboard di browser melalui URL: [http://localhost:8000](http://localhost:8000)

### Langkah 2: Jalankan Daemon MQTT Listener
Di terminal kedua, jalankan command daemon untuk mendengarkan kiriman data sensor dari ESP32 secara realtime:
```bash
php artisan mqtt:listen
```
*Daemon ini akan menerima payload suhu/kelembapan/cahaya dari broker Shiftr.io lalu menyimpannya ke database Supabase PostgreSQL.*

### Langkah 3: Jalankan Simulasi Wokwi
1. Buka browser dan pergi ke website [wokwi.com](https://wokwi.com).
2. Buat project baru dengan tipe **ESP32** -> **Arduino**.
3. Copy-paste isi file `wokwi-esp32.ino` ke editor kode Wokwi.
4. Copy-paste isi file `diagram.json` ke editor sirkuit diagram Wokwi.
5. Jalankan simulasi dengan menekan tombol **Play** (berwarna hijau).

### Skenario Pengujian:
1. **Pengujian Telemetri (Sensor to Dashboard)**:
   - Geser slider suhu atau kelembapan pada komponen DHT22 di simulator Wokwi.
   - Lihat konsol output Wokwi yang mempublikasikan data ke MQTT (`home/temperature` / `home/humidity`).
   - Terminal Laravel daemon (`php artisan mqtt:listen`) akan menampilkan log pesan masuk.
   - Lihat grafik di halaman dashboard browser Anda; grafik histori suhu & kelembapan akan bergeser secara realtime tanpa reload halaman!

2. **Pengujian Kontrol (Dashboard to ESP32)**:
   - Buka halaman dashboard di browser.
   - Cari kartu ruangan **Living Room** dan klik switch toggle pada **Smart Lamp** ke posisi **ON**.
   - Dashboard akan memanggil API Laravel yang mengirimkan MQTT message `{"device":"lamp","status":"ON"}` ke topic `home/control/lamp`.
   - Perhatikan lampu LED Merah pada simulator Wokwi akan langsung menyala secara realtime.
   - Begitupun jika Anda mengunci/membuka **Garage Smart Door**; motor servo Wokwi akan berputar 90 derajat (terbuka) atau kembali ke 0 derajat (terkunci).

3. **Pengujian Otomatisasi (Automation Trigger)**:
   - Naikkan slider suhu DHT22 di Wokwi hingga di atas **30.0°C**.
   - SensorService di Laravel akan membaca data masuk, mencocokkannya dengan tabel `automation_rules`, lalu otomatis mempublikasikan perintah menyalakan kipas (`home/control/fan` -> `{"status":"ON"}`).
   - Relay kipas pada sirkuit Wokwi akan otomatis berpindah ke status aktif.

---

## 8. Panduan Deployment Produksi

### Server Deployment:
Untuk me-deploy Laravel ke VPS linux atau cloud provider (seperti Heroku, Fly.io, Railway, atau VPS Ubuntu):
1. **Database**: Menggunakan Supabase PG sangat menguntungkan karena ter-host secara cloud, sehingga server Laravel Anda bisa dideploy di server manapun tanpa perlu menyeting server DB lokal.
2. **Web Server**: Gunakan Nginx + PHP-FPM 8.3.
3. **MQTT Worker Daemon (Sangat Penting)**:
   Karena perintah `php artisan mqtt:listen` berjalan sebagai loop tak terbatas (infinite loop), perintah ini harus diatur menggunakan process manager seperti **Supervisor** di server Linux.
   
   #### Contoh Konfigurasi Supervisor (`/etc/supervisor/conf.d/mqtt-listener.conf`):
   ```ini
   [program:mqtt-listener]
   process_name=%(program_name)s_%(process_num)02d
   command=php /var/www/smart-home-sija/laravel-app/artisan mqtt:listen
   autostart=true
   autorestart=true
   user=www-data
   numprocs=1
   redirect_stderr=true
   stdout_logfile=/var/www/smart-home-sija/laravel-app/storage/logs/mqtt-listener.log
   ```
   Aktifkan Supervisor:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start mqtt-listener:*
   ```
   Dengan menggunakan Supervisor, jika daemon listener mengalami crash atau server restart, Supervisor akan mendeteksi kegagalan tersebut dan otomatis menjalankannya kembali, memastikan pemantauan smart home berjalan 24 jam nonstop.
