<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Mobile Vehicle Assistant - <?= $customer['customer_name'] ?></title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Sweet Allert -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.css">

    <!-- Fancy Box -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancyapps-ui/5.0.36/fancybox/fancybox.css">

    <!-- Layout CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/backend/css/layout.css') ?>">
</head>
<body>

<!-- ── MOVAS LOADER ───────────────────────────────────────── -->
<div id="movas-loader-overlay">
  <div class="movas-loader-content">

    <div class="movas-ring-outer">
      <div class="movas-ring-inner">
        <div class="movas-logo-circle">
          <img src="<?= base_url() ?>assets/backend/img/movas-robot-no-text.png"
               alt="MOVAS" class="movas-logo-img" />
        </div>
      </div>
    </div>

    <div class="movas-brand-block">
      <div class="movas-brand-name">MOVAS</div>
      <div class="movas-brand-sub">Mobile Vehicle Assistant</div>
    </div>

    <div class="movas-loading-text" id="movas-loading-text">Memuat data...</div>

    <div class="movas-progress-wrap">
      <div class="movas-progress-track"></div>
    </div>

    <div class="movas-dots">
      <span class="movas-dot"></span>
      <span class="movas-dot"></span>
      <span class="movas-dot"></span>
    </div>

    <div class="movas-fallback-action" id="movas-fallback-action">
      <button type="button" class="movas-btn-refresh" onclick="window.location.reload();">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
          <path d="M23 4v6h-6"></path>
          <path d="M1 20v-6h6"></path>
          <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
        </svg>
        Segarkan Halaman
      </button>
    </div>

  </div>
</div>

<!-- Sidebar (desktop) -->
<aside class="movas-sidebar">
    <div class="sidebar-brand">
        <img src="<?= base_url('assets/backend/img/movas-robot-no-text.png') ?>" alt="MOVAS">
        <div>
            <div class="sidebar-brand-text">MOVAS</div>
            <div class="sidebar-brand-sub">Mobile Vehicle Assistant</div>
        </div>
    </div>

    <div class="sidebar-customer">
        <div class="sidebar-customer-name">
            <?= htmlspecialchars($customer['customer_name']) ?>
        </div>
        <div class="sidebar-customer-plan">
            <?php if ($is_pro): ?>
                <span class="plan-badge pro">★ PRO</span>
            <?php else: ?>
                <span class="plan-badge free">FREE</span>
            <?php endif; ?>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= site_url($token) ?>"
        class="sidebar-nav-item <?= $page === 'vehicle_list' ? 'active' : '' ?>">
            <i class="fas fa-car"></i> Kendaraan Saya
        </a>
        <a href="<?= site_url($token . '/kendaraan') ?>"
        class="sidebar-nav-item <?= $page === 'kendaraan_list' ? 'active' : '' ?>">
            <i class="fas fa-list"></i> Daftar Kendaraan
        </a>
        <a href="<?= site_url($token . '/profile') ?>"
        class="sidebar-nav-item <?= $page === 'profile' ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i> Profile
        </a>
    </nav>

    <!-- Toggle tema -->
    <div style="padding:12px 20px; border-top:1px solid var(--movas-border);">
        <button id="btn-toggle-theme"
                onclick="toggleTheme()"
                style="width:100%; display:flex; align-items:center; justify-content:space-between;
                    background:var(--movas-surface2); border:1px solid var(--movas-border);
                    color:var(--movas-text); padding:8px 12px; border-radius:10px;
                    cursor:pointer; font-size:12px; font-weight:500;">
            <span>
                <i id="theme-icon" class="fas fa-moon" style="margin-right:6px;"></i>
                <span id="theme-label">Tema Gelap</span>
            </span>
            <div id="theme-switch"
                style="width:32px; height:18px; border-radius:9px; background:var(--movas-accent);
                        position:relative; transition:background 0.2s;">
                <div id="theme-knob"
                    style="position:absolute; top:2px; left:2px;
                            width:14px; height:14px; border-radius:50%;
                            background:#fff; transition:transform 0.2s;"></div>
            </div>
        </button>
    </div>

    <div class="sidebar-footer">
        © <?= date('Y') ?> MOVAS by Mobile Auto Care<br>
        Versi 1.0
    </div>
</aside>

<!-- Main Content -->
<main class="movas-main">

    <!-- ── MOVAS MOBILE HEADER ──────────────────────────── -->
    <?php if ($page !== 'kendaraan_detail'): ?>
    <div class="movas-mobile-header">
        <div style="display:flex; align-items:center; gap:10px;">
            <img src="<?= base_url() ?>assets/backend/img/movas-robot-no-text.png"
                style="width:32px; height:32px; object-fit:contain;">
            <div>
                <div style="font-size:13px; font-weight:700; letter-spacing:1px;">MOVAS</div>
                <div style="font-size:9px; color:var(--movas-text3); letter-spacing:0.5px; text-transform:uppercase;">
                    Mobile Vehicle Assistant
                </div>
            </div>
        </div>
        <div>
            <?php if ($is_pro): ?>
            <span class="plan-badge pro">★ PRO</span>
            <?php else: ?>
            <span class="plan-badge free">FREE</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <!-- ── END MOVAS MOBILE HEADER ──────────────────────── -->

    <?php if ($page === 'vehicle_list'):  $this->load->view('movas/vehicle_list',    get_defined_vars()); endif; ?>
    <?php if ($page === 'kendaraan_detail'): $this->load->view('movas/kendaraan_detail', get_defined_vars()); endif; ?>
    <?php if ($page === 'kendaraan_list'): $this->load->view('movas/kendaraan_list', get_defined_vars()); endif; ?>
    <?php if ($page === 'profile'):       $this->load->view('movas/profile',         get_defined_vars()); endif; ?>
</main>

<!-- Bottom Nav (mobile) -->
<nav class="movas-bottom-nav">
    <div class="bottom-nav-inner">
        <a href="<?= site_url($token) ?>"
           class="bottom-nav-item <?= $page === 'vehicle_list' ? 'active' : '' ?>">
            <i class="fas fa-car"></i>
            <span>Kendaraan</span>
        </a>
        <a href="<?= site_url($token . '/kendaraan') ?>"
           class="bottom-nav-item <?= $page === 'kendaraan_list' ? 'active' : '' ?>">
            <i class="fas fa-list"></i>
            <span>Daftar</span>
        </a>
        <a href="<?= site_url($token . '/profile') ?>"
           class="bottom-nav-item <?= $page === 'profile' ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>
        <button class="bottom-nav-item" onclick="toggleTheme()"
                style="background:none; border:none; cursor:pointer;">
            <i class="fas fa-moon" id="theme-icon-mobile"></i>
            <span id="theme-label-mobile">Tema</span>
        </button>
    </div>
</nav>

<!-- ── MOVAS AI CHAT ─────────────────────────────────────── -->
<div id="movas-ai-fab" onclick="toggleAIChat()"
     style="position:fixed; bottom:calc(var(--bottom-nav-h) + 16px); right:16px;
            width:52px; height:52px; border-radius:50%;
            background:linear-gradient(135deg, #1a2d7c, #00b4d8);
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; z-index:500;
            box-shadow:0 4px 16px rgba(0,180,216,0.4);
            transition:transform 0.2s;"
     onmouseover="this.style.transform='scale(1.1)'"
     onmouseout="this.style.transform='scale(1)'"
     title="Tanya MOVAS AI">
    <img src="<?= base_url() ?>assets/backend/img/movas-robot-no-text.png"
         style="width:32px; height:32px; object-fit:contain;">
    <!-- Notif badge -->
    <span id="ai-badge" style="display:none; position:absolute;
          top:-2px; right:-2px; width:10px; height:10px;
          background:#f6c23e; border-radius:50%;
          border:2px solid var(--movas-bg);"></span>
</div>

<!-- Chat Window -->
<div id="movas-ai-chat-window"
     style="display:none; position:fixed;
            bottom:calc(var(--bottom-nav-h) + 80px); right:16px;
            width:320px; max-height:480px;
            background:var(--movas-surface);
            border:1px solid var(--movas-border);
            border-radius:16px; z-index:500;
            box-shadow:0 8px 32px rgba(0,0,0,0.3);
            display:none; flex-direction:column;
            overflow:hidden;">

    <!-- Header -->
    <div style="background:linear-gradient(135deg, #1a2d7c, #00b4d8);
                padding:12px 16px; display:flex;
                align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:10px;">
            <img src="<?= base_url() ?>assets/backend/img/movas-robot.png"
                 style="width:28px; height:28px; object-fit:contain;">
            <div>
                <div style="font-size:13px; font-weight:700; color:#fff;">MOVAS AI</div>
                <div style="font-size:10px; color:rgba(255,255,255,0.7);">
                    Asisten Kendaraan Anda
                </div>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <!-- Quota indicator -->
            <div id="ai-quota-label"
                 style="font-size:10px; color:rgba(255,255,255,0.7);"></div>
            <button onclick="toggleAIChat()"
                    style="background:none; border:none; color:white;
                           cursor:pointer; font-size:18px; padding:0;
                           line-height:1;">&times;</button>
        </div>
    </div>

    <!-- Messages -->
    <div id="ai-messages"
         style="flex:1; overflow-y:auto; padding:12px;
                display:flex; flex-direction:column; gap:10px;
                max-height:320px; min-height:200px;">
        <!-- Welcome message -->
        <div class="ai-bubble assistant">
            <div class="ai-bubble-content">
                Halo! Saya MOVAS AI 👋 Saya siap membantu Anda seputar kondisi dan perawatan kendaraan. Ada yang ingin ditanyakan?
            </div>
        </div>
    </div>

    <!-- Typing indicator -->
    <div id="ai-typing" style="display:none; padding:0 12px 8px;">
        <div class="ai-bubble assistant" style="margin:0;">
            <div class="ai-bubble-content" style="padding:10px 14px;">
                <span class="ai-typing-dot"></span>
                <span class="ai-typing-dot"></span>
                <span class="ai-typing-dot"></span>
            </div>
        </div>
    </div>

    <!-- Input -->
    <div style="padding:10px 12px; border-top:1px solid var(--movas-border);
                display:flex; gap:8px; align-items:flex-end;">
        <textarea id="ai-input"
                  placeholder="Ketik pertanyaan..."
                  rows="1"
                  onkeydown="handleAIKey(event)"
                  oninput="autoResizeAI(this)"
                  style="flex:1; background:var(--movas-surface2);
                         border:1px solid var(--movas-border);
                         color:var(--movas-text); border-radius:10px;
                         padding:8px 12px; font-size:12px;
                         resize:none; outline:none; max-height:80px;
                         font-family:'Inter',sans-serif;
                         transition:border-color 0.15s;"
                  onfocus="this.style.borderColor='var(--movas-cyan)'"
                  onblur="this.style.borderColor='var(--movas-border)'"></textarea>
        <button id="ai-send-btn" onclick="sendAIMessage()"
                style="width:36px; height:36px; border-radius:50%;
                       background:linear-gradient(135deg, #1a2d7c, #00b4d8);
                       border:none; cursor:pointer; flex-shrink:0;
                       display:flex; align-items:center; justify-content:center;
                       transition:opacity 0.15s;">
            <i class="fas fa-paper-plane" style="color:white; font-size:13px;"></i>
        </button>
    </div>
</div>
<!-- ── END MOVAS AI CHAT ──────────────────────────────────── -->

<!-- jQuery + Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancyapps-ui/5.0.36/fancybox/fancybox.umd.js"></script>

<script>
    var MOVAS_TOKEN = '<?= $token ?>';
    var MOVAS_IS_PRO = <?= $is_pro ? 'true' : 'false' ?>;
    var SITE_URL = '<?= site_url() ?>';

    function showLoading() { $('#movas-loading').addClass('show'); }
    function hideLoading() { $('#movas-loading').removeClass('show'); }

    // ── DISMISS UPGRADE BANNER ─────────────────────────────────
    function dismissUpgradeBanner() {
        $('.upgrade-banner').fadeOut(200);
        try {
            sessionStorage.setItem('movas_banner_dismissed', '1');
        } catch(e) {}
    }

    // Cek apakah banner sudah di-dismiss di session ini
    $(function() {
        try {
            if (sessionStorage.getItem('movas_banner_dismissed') === '1') {
                $('.upgrade-banner').hide();
            }
        } catch(e) {}
    });

    // Search kendaraan
    function filterKendaraan(val) {
        // Hapus semua spasi dari keyword — seperti LIKE %% 
        var keyword = val.toLowerCase().trim().replace(/\s+/g, '');
        var cols    = document.querySelectorAll('.vehicle-col');
        var found   = 0;

        cols.forEach(function(col) {
            // Hapus spasi dari data juga
            var text = (col.dataset.search || '').toLowerCase().replace(/\s+/g, '');

            if (!keyword || text.indexOf(keyword) > -1) {
                col.style.display = '';
                found++;
            } else {
                col.style.display = 'none';
            }
        });

        var emptyEl  = document.getElementById('search-empty');
        var clearBtn = document.getElementById('btn-clear-search');
        if (emptyEl)  emptyEl.style.display  = found === 0 ? 'block' : 'none';
        if (clearBtn) clearBtn.style.display = val ? 'block' : 'none';
    }

    function clearSearch() {
        var input = document.getElementById('search-kendaraan');
        if (input) {
            input.value = '';
            filterKendaraan('');
            input.focus();
        }
    }

    // Search Daftar Kendaraan
    function filterDaftar(val) {
        var keyword = val.toLowerCase().trim().replace(/\s+/g, '');
        var rows    = document.querySelectorAll('.daftar-row');
        var found   = 0;

        rows.forEach(function(row) {
            var text = (row.getAttribute('data-search') || '').toLowerCase().replace(/\s+/g, '');
            if (!keyword || text.indexOf(keyword) > -1) {
                row.style.display = 'flex';
                found++;
            } else {
                row.style.display = 'none';
            }
        });

        var emptyEl  = document.getElementById('daftar-empty');
        var clearBtn = document.getElementById('btn-clear-daftar');
        if (emptyEl)  emptyEl.style.display  = found === 0 ? 'block' : 'none';
        if (clearBtn) clearBtn.style.display = val ? 'block' : 'none';
    }

    function clearDaftar() {
        var input = document.getElementById('search-daftar');
        if (input) {
            input.value = '';
            filterDaftar('');
            input.focus();
        }
    }

    // Tema toggle
    function toggleTheme() {
        var current = document.body.classList.contains('light-theme') ? 'light' : 'dark';
        var next    = current === 'light' ? 'dark' : 'light';
        applyTheme(next);
        try { localStorage.setItem('movas_theme', next); } catch(e) {}
    }

    function applyTheme(theme) {
        if (theme === 'light') {
            document.body.classList.add('light-theme');
        } else {
            document.body.classList.remove('light-theme');
        }
        // Update icon & label — pakai vanilla JS karena jQuery belum tentu load
        var iconEl        = document.getElementById('theme-icon');
        var iconMobileEl  = document.getElementById('theme-icon-mobile');
        var labelEl       = document.getElementById('theme-label');
        var labelMobileEl = document.getElementById('theme-label-mobile');
        var knobEl        = document.getElementById('theme-knob');
        var switchEl      = document.getElementById('theme-switch');

        if (theme === 'light') {
            if (iconEl)        { iconEl.classList.remove('fa-moon');  iconEl.classList.add('fa-sun'); }
            if (iconMobileEl)  { iconMobileEl.classList.remove('fa-moon'); iconMobileEl.classList.add('fa-sun'); }
            if (labelEl)       labelEl.textContent       = 'Tema Terang';
            if (labelMobileEl) labelMobileEl.textContent = 'Terang';
            if (knobEl)        knobEl.style.transform    = 'translateX(14px)';
            if (switchEl)      switchEl.style.background = '#f6c23e';
        } else {
            if (iconEl)        { iconEl.classList.remove('fa-sun');  iconEl.classList.add('fa-moon'); }
            if (iconMobileEl)  { iconMobileEl.classList.remove('fa-sun'); iconMobileEl.classList.add('fa-moon'); }
            if (labelEl)       labelEl.textContent       = 'Tema Gelap';
            if (labelMobileEl) labelMobileEl.textContent = 'Gelap';
            if (knobEl)        knobEl.style.transform    = 'translateX(0)';
            if (switchEl)      switchEl.style.background = '#1a2d7c';
        }
    }

    function toggleDropdown() {
        var trigger = document.querySelector('.movas-select-trigger');
        var options = document.getElementById('dropdown-options');
        if (!trigger || !options) return;
        var isOpen = options.classList.contains('show');
        if (isOpen) {
            trigger.classList.remove('open');
            options.classList.remove('show');
        } else {
            trigger.classList.add('open');
            options.classList.add('show');
        }
    }

    function selectTanggal(el) {
        var sesiId  = el.getAttribute('data-sesi-id');
        var tgl     = el.getAttribute('data-tgl');
        var jenis   = el.getAttribute('data-jenis');
        var catatan = el.getAttribute('data-catatan');

        var tglEl = document.querySelector('.movas-select-tgl');
        var subEl = document.querySelector('.movas-select-sub');
        if (tglEl) tglEl.textContent = tgl;
        if (subEl) subEl.textContent = jenis || '';

        document.querySelectorAll('.movas-select-option').forEach(function(opt) {
            opt.classList.remove('active');
        });
        document.querySelectorAll('.movas-option-dot').forEach(function(dot) {
            dot.classList.remove('active');
        });
        el.classList.add('active');
        el.querySelector('.movas-option-dot').classList.add('active');

        document.querySelector('.movas-select-trigger').classList.remove('open');
        document.getElementById('dropdown-options').classList.remove('show');

        switchKondisi({ catatan: catatan }, sesiId);
    }

    document.addEventListener('click', function(e) {
        var wrapper = document.getElementById('tanggal-dropdown');
        if (wrapper && !wrapper.contains(e.target)) {
            var trigger = document.querySelector('.movas-select-trigger');
            var options = document.getElementById('dropdown-options');
            if (trigger) trigger.classList.remove('open');
            if (options) options.classList.remove('show');
        }
    });

    // Load preferensi saat halaman dibuka
    (function() {
        var saved = null;
        try { saved = localStorage.getItem('movas_theme'); } catch(e) {}
        applyTheme(saved === 'light' ? 'light' : 'dark');
    })();

    // ── FEEDBACK ───────────────────────────────────────────────
    function setRating(val) {
        document.getElementById('feedback-rating').value = val;
        document.querySelectorAll('.star-btn').forEach(function(star) {
            var r = parseInt(star.getAttribute('data-rating'));
            star.style.color = r <= val ? '#f6c23e' : 'var(--movas-border)';
        });
    }

    // Hover effect bintang
    document.addEventListener('DOMContentLoaded', function() {
        var stars = document.querySelectorAll('.star-btn');
        stars.forEach(function(star) {
            star.addEventListener('mouseover', function() {
                var hoverVal = parseInt(this.getAttribute('data-rating'));
                stars.forEach(function(s) {
                    var r = parseInt(s.getAttribute('data-rating'));
                    s.style.color = r <= hoverVal ? '#f6c23e' : 'var(--movas-border)';
                });
            });
            star.addEventListener('mouseout', function() {
                var currentRating = parseInt(document.getElementById('feedback-rating').value) || 0;
                stars.forEach(function(s) {
                    var r = parseInt(s.getAttribute('data-rating'));
                    s.style.color = r <= currentRating ? '#f6c23e' : 'var(--movas-border)';
                });
            });
        });
    });

    function submitFeedback() {
        var rating = parseInt(document.getElementById('feedback-rating').value);
        var pesan  = document.getElementById('feedback-pesan').value.trim();
        var btn    = document.getElementById('btn-submit-feedback');

        if (rating < 1) {
            Swal.fire({
                icon : 'warning',
                title: 'Pilih Rating',
                text : 'Pilih rating bintang terlebih dahulu.',
                confirmButtonColor: '#1a2d7c',
            });
            return;
        }

        btn.disabled    = true;
        btn.innerHTML   = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

        $.ajax({
            url: SITE_URL + 'save_feedback',
            type: 'POST',
            dataType: 'JSON',
            data: {
                token  : MOVAS_TOKEN,
                rating : rating,
                pesan  : pesan,
            },
            success: function(res) {
                btn.disabled  = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Masukan';

                if (res.status) {
                    $('#feedback-wrapper').fadeOut(200, function() {
                        $('#feedback-success').fadeIn(200);
                    });
                } else {
                    // ── GANTI alert dengan Swal ──
                    Swal.fire({
                        icon : 'info',
                        title: 'Ups!',
                        text : res.error,
                        confirmButtonColor: '#1a2d7c',
                    });
                }
            },
            error: function() {
                btn.disabled  = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Masukan';
                Swal.fire({
                    icon : 'error',
                    title: 'Terjadi Kesalahan',
                    text : 'Gagal mengirim masukan. Coba lagi.',
                    confirmButtonColor: '#1a2d7c',
                });
            }
        });
    }

    // ── MOVAS AI CHAT ──────────────────────────────────────────
    var aiChatOpen    = false;
    var aiCurrentNopol = '';
    var aiIsLoading   = false;
    var aiHistoryLoaded = false;

    function toggleAIChat() {
        aiChatOpen = !aiChatOpen;
        var win = document.getElementById('movas-ai-chat-window');

        if (aiChatOpen) {
            win.style.display = 'flex';
            document.getElementById('ai-badge').style.display = 'none';

            // Load history pertama kali saja
            if (!aiHistoryLoaded) {
                loadAIHistory();
            }

            setTimeout(function() {
                document.getElementById('ai-input').focus();
            }, 100);
        } else {
            win.style.display = 'none';
        }
    }

function loadAIHistory() {
    $.ajax({
        url     : SITE_URL + 'get_ai_history',
        type    : 'POST',
        dataType: 'JSON',
        data    : {
            token : MOVAS_TOKEN,
            nopol : aiCurrentNopol || 'umum',
        },
        success: function(res) {
            if (!res.status) return;

            aiHistoryLoaded = true;

            if (res.history && res.history.length > 0) {
                var container = document.getElementById('ai-messages');
                container.innerHTML = '';

                res.history.forEach(function(h) {
                    var div       = document.createElement('div');
                    div.className = 'ai-bubble ' + (h.role === 'user' ? 'user' : 'assistant');

                    var formatted = h.message
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/\n/g, '<br>');

                    // ── Langsung tampil tanpa animasi ──
                    div.innerHTML = '<div class="ai-bubble-content">' + formatted + '</div>';
                    container.appendChild(div);
                });

                container.scrollTop = container.scrollHeight;
            }

            // Update quota
            var label = document.getElementById('ai-quota-label');
            if (label) {
                if (res.is_pro) {
                    label.textContent = '★ PRO — Unlimited';
                } else {
                    label.textContent = res.sisa >= 0 ? 'Sisa: ' + res.sisa + '/5' : '';
                }
            }

            // Enable/disable input
            if (!res.is_pro && res.sisa === 0) {
                document.getElementById('ai-input').disabled    = true;
                document.getElementById('ai-send-btn').disabled = true;
                document.getElementById('ai-input').placeholder = 'Kuota hari ini habis.';
            } else {
                document.getElementById('ai-input').disabled    = false;
                document.getElementById('ai-send-btn').disabled = false;
                document.getElementById('ai-input').placeholder = 'Ketik pertanyaan...';
            }
        }
    });
}

    function updateAIQuotaLabel() {
        var label = document.getElementById('ai-quota-label');
        if (!label) return;
        <?php if ($is_pro): ?>
        label.textContent = '★ PRO — Unlimited';
        <?php else: ?>
        label.textContent = 'FREE';
        // Quota akan diupdate dari response AI
        <?php endif; ?>
    }

    function handleAIKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendAIMessage();
        }
    }

    function autoResizeAI(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 80) + 'px';
    }

    function appendAIBubble(role, message) {
        var container = document.getElementById('ai-messages');
        var div       = document.createElement('div');
        div.className = 'ai-bubble ' + role;

        // Format markdown sederhana
        var formatted = message
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');

        if (role === 'assistant') {
            // Animasi typewriter untuk AI
            div.innerHTML = '<div class="ai-bubble-content" id="ai-typing-' + Date.now() + '"></div>';
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;

            var bubbleEl = div.querySelector('.ai-bubble-content');
            var plainText = message
                .replace(/\*\*(.*?)\*\*/g, '$1')
                .replace(/\*(.*?)\*/g, '$1');

            var i        = 0;
            var speed    = 8; // ms per karakter — makin kecil makin cepat
            var interval = setInterval(function() {
                if (i < plainText.length) {
                    // Re-render dengan formatting setiap update
                    var current = plainText.substring(0, i + 1);
                    bubbleEl.innerHTML = current
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/\n/g, '<br>');
                    i++;
                    container.scrollTop = container.scrollHeight;
                } else {
                    clearInterval(interval);
                    // Render final dengan formatting lengkap
                    bubbleEl.innerHTML = formatted;
                    container.scrollTop = container.scrollHeight;
                }
            }, speed);

        } else {
            // User bubble — langsung tampil tanpa animasi
            div.innerHTML = '<div class="ai-bubble-content">' + formatted + '</div>';
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        }
    }

    function sendAIMessage() {
        if (aiIsLoading) return;

        var input   = document.getElementById('ai-input');
        var message = input.value.trim();
        if (!message) return;

        appendAIBubble('user', message);
        input.value        = '';
        input.style.height = 'auto';

        aiIsLoading = true;
        document.getElementById('ai-typing').style.display = 'block';
        document.getElementById('ai-send-btn').style.opacity = '0.5';
        document.getElementById('ai-messages').scrollTop =
            document.getElementById('ai-messages').scrollHeight;

        $.ajax({
            url     : SITE_URL + 'ai_chat',
            type    : 'POST',
            dataType: 'JSON',
            data    : {
                token  : MOVAS_TOKEN,
                nopol  : aiCurrentNopol || 'umum',
                message: message,
            },
            success: function(res) {
                aiIsLoading = false;
                document.getElementById('ai-typing').style.display = 'none';
                document.getElementById('ai-send-btn').style.opacity = '1';

                if (res.status) {
                    appendAIBubble('assistant', res.reply);

                    // Update quota
                    var label = document.getElementById('ai-quota-label');
                    if (label) {
                        if (res.is_pro) {
                            label.textContent = '★ PRO — Unlimited';
                        } else if (res.sisa === 0) {
                            label.textContent = 'Kuota habis';
                            // Disable input
                            document.getElementById('ai-input').disabled    = true;
                            document.getElementById('ai-send-btn').disabled = true;
                            document.getElementById('ai-input').placeholder = 'Kuota hari ini habis.';
                        } else {
                            label.textContent = 'Sisa: ' + res.sisa + '/5';
                        }
                    }

                } else {
                    appendAIBubble('assistant', '⚠️ ' + res.error);

                    // Jika quota habis — disable input
                    if (res.error.indexOf('Kuota') > -1 || res.error.indexOf('kuota') > -1) {
                        document.getElementById('ai-input').disabled    = true;
                        document.getElementById('ai-send-btn').disabled = true;
                        document.getElementById('ai-input').placeholder = 'Kuota hari ini habis.';
                        var label = document.getElementById('ai-quota-label');
                        if (label) label.textContent = 'Kuota habis';
                    }
                }
            },
            error: function() {
                aiIsLoading = false;
                document.getElementById('ai-typing').style.display = 'none';
                document.getElementById('ai-send-btn').style.opacity = '1';
                appendAIBubble('assistant', '⚠️ Terjadi kesalahan. Coba lagi.');
            }
        });
    }

    // Set nopol konteks saat di halaman detail kendaraan
    <?php if (isset($nopol)): ?>
    aiCurrentNopol = '<?= addslashes($nopol) ?>';
    <?php endif; ?>
</script>

<?php $this->load->view('movas/scripts'); ?>

<script>
(function() {
    var overlay  = document.getElementById('movas-loader-overlay');
    var txtEl    = document.getElementById('movas-loading-text');
    var txtIdx   = 0;
    var txtTimer = null;

    overlay.style.display = 'flex';
    overlay.classList.remove('movas-loader-hide');

    var loadingTexts = [
        'Hampir selesai...',
    ];

    function hideLoader() {
        clearInterval(txtTimer);
        overlay.classList.add('movas-loader-hide');
        setTimeout(function() {
            overlay.style.display = 'none';
        }, 300);
    }

    // Rotasi teks
    txtEl.textContent = loadingTexts[0];
    txtTimer = setInterval(function() {
        txtEl.style.opacity = '0';
        setTimeout(function() {
            txtIdx = (txtIdx + 1) % loadingTexts.length;
            txtEl.textContent = loadingTexts[txtIdx];
            txtEl.style.opacity = '1';
        }, 350);
    }, 1800);

    // Sembunyikan saat halaman selesai load
    window.addEventListener('load', function() {
        hideLoader();
    });

    window.addEventListener('pageshow', function(e) {
    // e.persisted = true artinya halaman dari cache (back/forward)
        if (e.persisted) {
            hideLoader();
        }
    });

    // Tampilkan saat pindah halaman
    window.addEventListener('beforeunload', function() {
        overlay.style.display = 'flex';
        overlay.classList.remove('movas-loader-hide');
    });

    window.movasLoader = {
        show: function() {
            overlay.style.display = 'flex';
            overlay.classList.remove('movas-loader-hide');
        },
        hide: hideLoader
    };
})();
</script>
</body>
</html>