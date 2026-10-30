<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVAS — Mobile Vehicle Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0a1628 0%, #0d1f3c 40%, #0f2347 70%, #0a1628 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        #particles-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .container {
            text-align: center;
            padding: 40px 24px;
            max-width: 420px;
            width: 100%;
        }

        .logo-wrapper {
            width: 175px; height: 175px;
            border-radius: 50%;
            background: linear-gradient(145deg, #0d1f3c, #162a50);
            border: 2px solid rgba(0,180,216,0.4);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 0 30px rgba(0,180,216,0.2);
            animation: pulse 2.5s ease-in-out infinite;
        }

        .logo-wrapper img {
            width: 120px; height: 120px;
            object-fit: contain;
        }

        @keyframes pulse {
            0%,100% { box-shadow: 0 0 20px rgba(0,180,216,0.2); }
            50%      { box-shadow: 0 0 40px rgba(0,180,216,0.4); }
        }

        .brand-name {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 4px;
            text-transform: uppercase;
            background: linear-gradient(135deg, #ffffff, #00b4d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
        }

        .brand-sub {
            font-size: 12px;
            color: rgba(255,255,255,0.4);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 32px;
        }

        .divider {
            width: 40px; height: 2px;
            background: linear-gradient(90deg, transparent, #00b4d8, transparent);
            margin: 0 auto 32px;
        }

        .description {
            font-size: 14px;
            color: rgba(255,255,255,0.6);
            line-height: 1.7;
            margin-bottom: 32px;
        }

        .features {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 36px;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 10px;
            padding: 12px 16px;
        }

        .feature-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: rgba(0,180,216,0.15);
            display: flex; align-items: center; justify-content: center;
            color: #00b4d8;
            font-size: 14px;
            flex-shrink: 0;
        }

        .feature-text {
            font-size: 13px;
            color: rgba(255,255,255,0.7);
        }

        .feature-text strong {
            color: #ffffff;
            display: block;
            font-size: 13px;
            margin-bottom: 2px;
        }

        .info-box {
            background: rgba(0,180,216,0.08);
            border: 1px solid rgba(0,180,216,0.2);
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 13px;
            color: rgba(255,255,255,0.6);
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .info-box strong {
            color: #00b4d8;
        }

        .wa-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            transition: opacity 0.2s;
        }

        .wa-btn:hover {
            opacity: 0.9;
            color: #ffffff;
            text-decoration: none;
        }

        .footer {
            margin-top: 36px;
            font-size: 11px;
            color: rgba(255,255,255,0.2);
        }

        .footer span {
            color: rgba(0,180,216,0.5);
        }

        /* ── CUSTOM SCROLLBAR ─────────────────────────────────────── */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #1a2d7c, #00b4d8);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #00b4d8, #1a2d7c);
        }

        ::-webkit-scrollbar-corner {
            background: transparent;
        }
    </style>
</head>
<body>
    <canvas id="particles-canvas"></canvas>
    <div class="container" style="position:relative; z-index:1;">

        <!-- Logo -->
        <div class="logo-wrapper">
            <img src="<?= base_url('assets/backend/img/movas-robot.png') ?>" alt="MOVAS">
        </div>

        <!-- Brand -->
        <div class="brand-name">MOVAS</div>
        <div class="brand-sub">Mobile Vehicle Assistant</div>

        <div class="divider"></div>

        <!-- Description -->
        <p class="description">
            Pantau kondisi kendaraan Anda kapan saja dan di mana saja.
            Dapatkan rekomendasi perawatan, riwayat service, dan konsultasi
            langsung dengan AI asisten kendaraan kami.
        </p>

        <!-- Features -->
        <div class="features">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div class="feature-text">
                    <strong>Monitoring Kondisi Kendaraan</strong>
                    Pantau kondisi semua komponen kendaraan Anda secara real-time
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="feature-text">
                    <strong>Riwayat Service Lengkap</strong>
                    Akses seluruh riwayat service kendaraan dalam genggaman
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="feature-text">
                    <strong>AI Asisten Kendaraan</strong>
                    Konsultasi kondisi kendaraan dengan AI 24 jam
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="info-box">
            <strong><i class="fas fa-info-circle"></i> Cara Mengakses MOVAS</strong><br>
            Link MOVAS Anda dikirimkan langsung oleh tim
            <strong>Mobile Auto Care</strong> setelah melakukan service.
            Hubungi kami untuk mendapatkan akses.
        </div>

        <!-- WA Button -->
        <a href="https://wa.me/6282324001500" target="_blank" class="wa-btn">
            <i class="fab fa-whatsapp" style="font-size:18px;"></i>
            Hubungi Mobile Auto Care
        </a>

        <!-- ── Tombol Booking Service ─────────────────────────── -->
        <div style="margin-top:12px;">
            <a href="<?= site_url('booking') ?>" 
            style="display:inline-flex; align-items:center; gap:8px;
                    background:linear-gradient(135deg, #1a2d7c, #00b4d8);
                    color:#ffffff; text-decoration:none;
                    padding:12px 24px; border-radius:12px;
                    font-size:13px; font-weight:600;
                    box-shadow:0 4px 20px rgba(0,180,216,0.3);
                    transition:all 0.2s; position:relative; overflow:hidden;"
            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(0,180,216,0.4)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,180,216,0.3)'">
                <i class="fas fa-calendar-check" style="font-size:16px;"></i>
                Booking Service Sekarang
                <i class="fas fa-arrow-right" style="font-size:11px; opacity:0.7;"></i>
            </a>
            <div style="margin-top:8px; font-size:11px; color:rgba(255,255,255,0.3);">
                Tidak perlu akun MOVAS
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Powered by <span>Mobile Auto Care</span> &copy; <?= date('Y') ?>
        </div>

    </div>
<script>
    const canvas = document.getElementById('particles-canvas');
    const ctx = canvas.getContext('2d');

    let width = canvas.width = window.innerWidth;
    let height = canvas.height = window.innerHeight;

    window.addEventListener('resize', () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
        initParticles();
    });

    class Particle {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.radius = Math.random() * 2 + 0.8;
            this.vx = (Math.random() - 0.5) * 0.4;
            this.vy = (Math.random() - 0.5) * 0.4;
            this.alpha = Math.random() * 0.5 + 0.2;
            this.alphaSpeed = Math.random() * 0.005 + 0.002;
        }

        update() {
            this.x += this.vx;
            this.y += this.vy;
            if (this.x < 0 || this.x > width) this.vx *= -1;
            if (this.y < 0 || this.y > height) this.vy *= -1;
            this.alpha += this.alphaSpeed;
            if (this.alpha <= 0.1 || this.alpha >= 0.7) this.alphaSpeed *= -1;
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(147, 197, 253, ${this.alpha})`;
            ctx.shadowBlur = 8;
            ctx.shadowColor = 'rgba(59, 130, 246, 0.5)';
            ctx.fill();
        }
    }

    let particles = [];

    function initParticles() {
        const particleCount = Math.floor((width * height) / 40000);
        particles = [];
        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }
    }

    function connectParticles() {
        for (let a = 0; a < particles.length; a++) {
            for (let b = a + 1; b < particles.length; b++) {
                const dx = particles[a].x - particles[b].x;
                const dy = particles[a].y - particles[b].y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                if (distance < 110) {
                    const opacity = (1 - distance / 110) * 0.15;
                    ctx.beginPath();
                    ctx.moveTo(particles[a].x, particles[a].y);
                    ctx.lineTo(particles[b].x, particles[b].y);
                    ctx.strokeStyle = `rgba(147, 197, 253, ${opacity})`;
                    ctx.lineWidth = 0.6;
                    ctx.stroke();
                }
            }
        }
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);
        particles.forEach(p => { p.update(); p.draw(); });
        connectParticles();
        requestAnimationFrame(animate);
    }

    initParticles();
    animate();
</script>
</body>
</html>