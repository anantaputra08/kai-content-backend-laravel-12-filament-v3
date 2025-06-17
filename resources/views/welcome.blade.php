<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to PT. Kereta API Persero</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated background particles */
        .bg-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 8s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.5; }
            50% { transform: translateY(-20px) rotate(180deg); opacity: 1; }
        }

        /* Glass morphism navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(0, 51, 160, 0.95);
            backdrop-filter: blur(10px);
        }

        .logo {
            display: flex;
            align-items: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .logo i {
            font-size: 32px;
            margin-right: 12px;
            color: #ffc107;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Hero section with modern styling */
        .hero-section {
            padding: 120px 20px 80px;
            text-align: center;
            background: transparent;
            position: relative;
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 50%, #ffc107 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
            animation: fadeInUp 1s ease-out;
        }

        .hero-description {
            max-width: 800px;
            margin: 0 auto 40px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 18px;
            line-height: 1.8;
            font-weight: 400;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%);
            color: #333;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 10px 30px rgba(255, 193, 7, 0.4);
            transition: all 0.3s ease;
            animation: fadeInUp 1s ease-out 0.4s both;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 193, 7, 0.6);
        }

        /* Features section with cards */
        .features-section {
            padding: 80px 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            margin: 40px 20px;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .features-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 60px;
            color: white;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .feature-card:hover::before {
            left: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ffc107, #ff8f00);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 24px;
            color: #333;
            box-shadow: 0 10px 30px rgba(255, 193, 7, 0.3);
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 15px 40px rgba(255, 193, 7, 0.5);
        }

        .feature-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: white;
        }

        .feature-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 15px;
            line-height: 1.6;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .navbar {
                padding: 10px 20px;
            }
            
            .logo {
                font-size: 16px;
            }
            
            .hero-section {
                padding: 100px 20px 60px;
            }
            
            .features-section {
                margin: 20px 10px;
                padding: 60px 20px;
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        /* Scroll animations */
        .scroll-animate {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .scroll-animate.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body>
    <!-- Animated background -->
    <div class="bg-particles" id="particles"></div>

    <!-- Modern Navbar -->
    <nav class="navbar" id="navbar">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
            <div class="logo">
                <i class="fas fa-train"></i>
                PT. KERETA API PERSERO
            </div>
            <div class="nav-links flex gap-6">
                @if (auth()->check())
                    <a href="/admin">Dashboard</a>
                @else
                    <a href="/admin/login">Login</a>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <h1 class="hero-title">
            Selamat Datang di<br>
            <span style="color: #ffc107;">PT. KERETA API PERSERO</span>
        </h1>
        <p class="hero-description">
            Platform Digital untuk Efisiensi dan Inovasi Layanan Kereta Api, menghadirkan sistem terintegrasi untuk
            pengelolaan operasional, manajemen penumpang, logistik, serta pemantauan transaksi secara real-time
            guna meningkatkan kualitas layanan dan pengalaman pelanggan.
        </p>
        <a href="#features" class="cta-button">
            <i class="fas fa-rocket"></i> Jelajahi Fitur
        </a>
    </section>

    <!-- Features Section -->
    <section class="features-section scroll-animate" id="features">
        <h2 class="features-title">Fitur Unggulan</h2>
        <div class="feature-grid">
            <div class="feature-card scroll-animate">
                <div class="feature-icon">
                    <i class="fas fa-play-circle"></i>
                </div>
                <h3 class="feature-title">Manajemen Konten Hiburan</h3>
                <p class="feature-description">
                    Mengelola dan mengunggah berbagai jenis hiburan seperti film, musik, game, e-book dengan sistem yang mudah dan intuitif untuk pengalaman perjalanan yang menyenangkan.
                </p>
            </div>

            <div class="feature-card scroll-animate">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="feature-title">Analitik & Laporan</h3>
                <p class="feature-description">
                    Menyediakan data konsumsi konten real-time, termasuk konten terpopuler, durasi rata-rata penggunaan, dan insights mendalam untuk optimasi layanan.
                </p>
            </div>

            <div class="feature-card scroll-animate">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="feature-title">Manajemen Hak Akses</h3>
                <p class="feature-description">
                    Mengatur operator dan user dengan sistem hak akses berbasis peran yang aman, serta pencatatan aktivitas lengkap untuk audit dan keamanan.
                </p>
            </div>

            <div class="feature-card scroll-animate">
                <div class="feature-icon">
                    <i class="fas fa-network-wired"></i>
                </div>
                <h3 class="feature-title">Sistem & Konektivitas</h3>
                <p class="feature-description">
                    Mengelola server dan bandwidth untuk streaming optimal, mendukung mode offline saat perjalanan untuk pengalaman tanpa gangguan.
                </p>
            </div>
        </div>
    </section>

    <script>
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.width = Math.random() * 6 + 2 + 'px';
                particle.style.height = particle.style.width;
                particle.style.animationDelay = Math.random() * 8 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 5) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Scroll animations
        function handleScrollAnimations() {
            const elements = document.querySelectorAll('.scroll-animate');
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < window.innerHeight - elementVisible) {
                    element.classList.add('visible');
                }
            });
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            handleScrollAnimations();
            
            window.addEventListener('scroll', handleScrollAnimations);
            
            // Add stagger animation to feature cards
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.1) + 's';
            });
        });

        // Add parallax effect to hero section
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const heroSection = document.querySelector('.hero-section');
            heroSection.style.transform = `translateY(${scrolled * 0.5}px)`;
        });
    </script>
</body>

</html>