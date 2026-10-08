<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Mobile Auto Care</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #0b132b;
            background-image: radial-gradient(circle at 50% 50%, #1c2a4e 0%, #0b132b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        /* Canvas untuk partikel di background */
        #particles-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .container {
            max-width: 480px;
            width: 100%;
            text-align: center;
            position: relative;
            z-index: 10;
            padding: 20px;
        }

        .brand-logo {
            max-width: 220px;
            height: auto;
            margin-bottom: 25px;
            filter: drop-shadow(0 4px 15px rgba(0, 0, 0, 0.5));
        }

        .error-code {
            font-size: 45px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1;
            margin-bottom: 5px;
            letter-spacing: -2px;
            text-shadow: 0 0 25px rgba(59, 130, 246, 0.6);
        }

        .error-title {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 12px;
        }

        .error-desc {
            font-size: 13.5px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        /* Tombol Biru Gradasi */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            max-width: 300px;
            padding: 14px 24px;
            background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            opacity: 0.95;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.6);
        }

        .footer-copyright {
            margin-top: 40px;
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- Canvas Partikel -->
    <canvas id="particles-canvas"></canvas>

    <div class="container">
        <!-- Logo MAC di Tengah -->
        <img src="<?= base_url('assets/backend/img/movas-robot.png'); ?>" alt="Mobile Auto Care" class="brand-logo" onerror="this.style.display='none'" width="200">

        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        
        <p class="error-desc">
            Akses tidak valid. Pastikan token yang digunakan sudah benar dan masih berlaku serta URL yang Anda akses sesuai. Jika kendala masih terjadi, silakan hubungi Admin.
        </p>

        <!-- Redirect ke Website Utama -->
        <a href="https://mobileautocare.co.id/" class="btn-action">
            <span>Kembali ke Beranda</span>
            <i class="fa-solid fa-arrow-right"></i>
        </a>

        <div class="footer-copyright">
            &copy; <?= date('Y'); ?> MOVAS by Mobile Auto Care. All rights reserved.
        </div>
    </div>

    <!-- Script Animasi Partikel Smooth -->
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
                this.radius = Math.random() * 2 + 0.8; // Ukuran partikel kecil
                this.vx = (Math.random() - 0.5) * 0.4;  // Kecepatan gerak horizontal halus
                this.vy = (Math.random() - 0.5) * 0.4;  // Kecepatan gerak vertikal halus
                this.alpha = Math.random() * 0.5 + 0.2;
                this.alphaSpeed = Math.random() * 0.005 + 0.002;
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;

                // Pantulan tepi
                if (this.x < 0 || this.x > width) this.vx *= -1;
                if (this.y < 0 || this.y > height) this.vy *= -1;

                // Pulsating effect (fading in/out halus)
                this.alpha += this.alphaSpeed;
                if (this.alpha <= 0.1 || this.alpha >= 0.7) {
                    this.alphaSpeed *= -1;
                }
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
        const particleCount = Math.floor((width * height) / 40000); // Kepadatan partikel adaptif

        function initParticles() {
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
            
            particles.forEach(p => {
                p.update();
                p.draw();
            });

            connectParticles();
            requestAnimationFrame(animate);
        }

        initParticles();
        animate();
    </script>
</body>
</html>