<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to {{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
        }

        .navbar {
            background-color: #0033a0;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .logo img {
            height: 30px;
            margin-right: 10px;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
        }

        .welcome-section {
            text-align: center;
            padding: 40px 20px;
        }

        .welcome-title {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .blue-text {
            color: #0033a0;
        }

        .gold-text {
            color: #ffc107;
        }

        .welcome-description {
            max-width: 1200px;
            margin: 0 auto;
            color: #555;
            font-size: 18px;
            line-height: 1.6;
        }

        .features-section {
            padding: 20px;
            text-align: center;
        }

        .features-title {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 40px;
            color: #333;
        }

        .feature-cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 250px;
            text-align: center;
        }

        .feature-icon {
            color: #0d6efd;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .feature-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }

        .feature-description {
            color: #555;
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="KAI Logo">
            PT. KERETA API PERSERO
        </div>
        <div class="nav-links">
            {{-- <a href="{{ url('/') }}">Fitur</a>
            <a href="{{ url('/') }}">Kontak</a> --}}
            @auth
                <a href="{{ url('/dashboard') }}">Dashboard</a>
            @else
                <a href="{{ url('/admin') }}">Login</a>
            @endauth
        </div>
    </nav>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <h1 class="welcome-title">
            <span class="blue-text">Selamat Datang di </span>
            <span class="gold-text">PT.KERETA API PERSERO</span>
        </h1>
        <p class="welcome-description">
            Platform Digital untuk Efisiensi dan Inovasi Layanan Kereta Api, menghadirkan sistem terintegrasi untuk
            pengelolaan operasional, manajemen penumpang, logistik, serta pemantauan transaksi secara real-time
            guna meningkatkan kualitas layanan dan pengalaman pelanggan.
        </p>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <h2 class="features-title">Fitur Utama</h2>
        <div class="feature-cards">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-box"></i>
                    {{ svg('fluentui-box-16-o') }}
                </div>
                <h3 class="feature-title">Manajemen Konten Hiburan</h3>
                <p class="feature-description">
                    Mengelola dan mengunggah berbagai jenis hiburan seperti film, musik, game, e-book, dan
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                    {{ svg('fluentui-chart-multiple-24-o') }}
                </div>
                <h3 class="feature-title">Analitik & Laporan</h3>
                <p class="feature-description">
                    Menyediakan data konsumsi konten, termasuk konten terpopuler, durasi rata-rata penggunaan hiburan
                    per
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-shield"></i>
                    {{ svg('fluentui-people-12') }}
                </div>
                <h3 class="feature-title">Manajemen Hak Akses Pengguna</h3>
                <p class="feature-description">
                    Mengatur operator dan user dengan sistem hak akses berbasis peran serta pencatatan
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-network-wired"></i>
                    {{ svg('fluentui-text-bullet-list-square-settings-20-o') }}
                </div>
                <h3 class="feature-title">Pengaturan Sistem & Konektivitas</h3>
                <p class="feature-description">
                    Mengelola server dan bandwidth untuk streaming, mendukung mode offline saat perjalanan
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-gray-800 via-gray-700 to-black text-white py-8" style="display: none;">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                @putraananta08templates</p>
        </div>
    </footer>
</body>

</html>
