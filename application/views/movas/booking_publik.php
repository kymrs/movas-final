<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Booking Service — Mobile Auto Care</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/backend/css/booking_publik.css') ?>">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.css">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
</head>
<body>

    <!-- ── HEADER ────────────────────────────────────────────── -->
    <div class="pub-header" style="padding:14px 0 16px;">
        <div style="display:flex; align-items:center; justify-content:center; gap:10px;">
            <img src="<?= base_url('assets/backend/img/mobileautocare-white.png') ?>"
                alt="MAC Logo" style="height:32px; object-fit:contain;">
            <div style="text-align:left;">
                <div class="pub-header-title" style="font-size:15px;">Booking Service</div>
                <div class="pub-header-sub" style="font-size:11px;">Mobile Auto Care</div>
            </div>
        </div>
    </div>

    <div class="pub-container">

        <!-- Info box -->
        <div class="pub-info-box">
            <i class="fas fa-info-circle" style="margin-right:6px;"></i>
            Isi form berikut untuk melakukan booking service kendaraan Anda.
            Tim MAC akan menghubungi Anda untuk konfirmasi jadwal.
        </div>

        <!-- ── DATA DIRI ──────────────────────────────────────── -->
        <div class="pub-card">
            <div class="pub-card-header">
                <i class="fas fa-user"></i> Data Diri
            </div>
            <div class="pub-card-body">
                <div class="pub-form-group">
                    <label class="pub-label">Nama Lengkap <span style="color:var(--pub-danger);">*</span></label>
                    <input type="text" id="nama_publik" class="pub-input"
                           placeholder="Masukkan nama lengkap Anda">
                </div>
                <div class="pub-form-group" style="margin-bottom:0;">
                    <label class="pub-label">No. WhatsApp <span style="color:var(--pub-danger);">*</span></label>
                    <input type="tel" id="no_telp_publik" class="pub-input"
                           placeholder="Contoh: 08123456789">
                </div>
            </div>
        </div>

        <!-- ── KENDARAAN ──────────────────────────────────────── -->
        <div class="pub-card">
            <div class="pub-card-header">
                <i class="fas fa-car"></i> Kendaraan
                <span style="font-size:11px; color:var(--pub-text3); font-weight:400; margin-left:auto;">
                    Bisa lebih dari 1
                </span>
            </div>
            <div class="pub-card-body">
                <div id="pub-unit-list"></div>
                <button type="button" class="pub-add-unit-btn" onclick="pubAddUnit()">
                    <i class="fas fa-plus-circle"></i> Tambah Kendaraan
                </button>
            </div>
        </div>

        <!-- ── DETAIL BOOKING ─────────────────────────────────── -->
        <div class="pub-card">
            <div class="pub-card-header">
                <i class="fas fa-calendar-alt"></i> Detail Booking
            </div>
            <div class="pub-card-body">

                <!-- Tanggal -->
                <div class="pub-form-group">
                    <label class="pub-label">Tanggal Service <span style="color:var(--pub-danger);">*</span></label>
                    <input type="text" id="tgl_booking" class="pub-input"
                           placeholder="Pilih tanggal service..." readonly>
                    <input type="hidden" id="tgl_booking_raw">
                </div>

                <!-- Waktu -->
                <div class="pub-form-group">
                    <label class="pub-label">Pilih Waktu <span style="color:var(--pub-danger);">*</span></label>
                    <div class="pub-select-wrapper" id="pub-waktu-dropdown">
                        <input type="hidden" id="waktu_catatan">
                        <div class="pub-select-trigger" id="pub-waktu-trigger"
                            onclick="pubToggleWaktu()"
                            style="opacity:0.5; pointer-events:none;">
                            <span id="pub-waktu-text" style="color:var(--pub-text3);">
                                Pilih tanggal terlebih dahulu
                            </span>
                            <i class="fas fa-chevron-down" style="font-size:11px; color:var(--pub-text3);
                            transition:transform 0.2s;" id="pub-waktu-arrow"></i>
                        </div>
                        <div class="pub-select-options" id="pub-waktu-options"></div>
                    </div>
                </div>

                <!-- Lokasi -->
                <div class="pub-form-group">
                    <label class="pub-label">Lokasi Service <span style="color:var(--pub-danger);">*</span></label>
                    <div class="pub-lokasi-btn">
                        <button type="button" id="pub-btn-bengkel"
                                onclick="pubSetLokasi('bengkel')" class="active">
                            <i class="fas fa-wrench"></i> Ke Bengkel
                        </button>
                        <button type="button" id="pub-btn-home"
                                onclick="pubSetLokasi('home_service')">
                            <i class="fas fa-home"></i> Home Service
                        </button>
                    </div>
                    <input type="hidden" id="lokasi_service" value="bengkel">
                </div>

                <!-- Alamat (home service) -->
                <div id="pub-row-alamat" style="display:none;" class="pub-form-group">
                    <label class="pub-label">Alamat Service <span style="color:var(--pub-danger);">*</span></label>
                    <textarea id="alamat_service" class="pub-input" rows="3"
                              placeholder="Masukkan alamat lengkap..."
                              style="resize:none;"></textarea>
                </div>

                <!-- Cabang Tujuan -->
                <div class="pub-form-group" style="margin-bottom:0;">
                    <label class="pub-label">Cabang Tujuan <span style="color:var(--pub-danger);">*</span></label>
                    <div class="pub-select-wrapper" id="pub-cabang-dropdown">
                        <input type="hidden" id="cabang_nama">

                        <!-- Trigger -->
                        <div class="pub-select-trigger" onclick="pubToggleCabang()" id="pub-cabang-trigger">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:8px; height:8px; border-radius:50%;
                                            background:var(--pub-border); flex-shrink:0;"
                                    id="pub-cabang-dot"></div>
                                <div>
                                    <div id="pub-cabang-text" style="color:var(--pub-text3); font-size:13px;">
                                        — Pilih Cabang —
                                    </div>
                                    <div id="pub-cabang-alamat" style="font-size:11px; color:var(--pub-text3);"></div>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down" style="font-size:11px; color:var(--pub-text3);
                            transition:transform 0.2s;" id="pub-cabang-arrow"></i>
                        </div>

                        <!-- Options -->
                        <div class="pub-select-options" id="pub-cabang-options">
                            <?php foreach ($cabang_list as $cab): ?>
                            <div class="pub-select-option"
                                data-value="<?= htmlspecialchars($cab['nama']) ?>"
                                data-alamat="<?= htmlspecialchars($cab['alamat']) ?>"
                                data-id="<?= $cab['id'] ?>"
                                onclick="pubSelectCabang(this)">
                                <div style="display:flex; align-items:flex-start; gap:10px;">
                                    <div style="width:8px; height:8px; border-radius:50%;
                                                background:var(--pub-border); flex-shrink:0;
                                                align-self:center;"
                                        class="pub-opt-dot"></div>
                                    <div>
                                        <div style="font-weight:600; font-size:13px;">
                                            <?= htmlspecialchars($cab['nama']) ?>
                                        </div>
                                        <div style="font-size:11px; color:var(--pub-text3); margin-top:2px;">
                                            <?= htmlspecialchars($cab['alamat']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Catatan -->
        <div class="pub-card">
            <div class="pub-card-header">
                <i class="fas fa-sticky-note"></i> Catatan Tambahan
                <span style="font-size:11px; color:var(--pub-text3); font-weight:400; margin-left:auto;">Opsional</span>
            </div>
            <div class="pub-card-body">
                <textarea id="catatan" class="pub-input" rows="3"
                          placeholder="Informasi tambahan untuk tim MAC..."
                          style="resize:none; margin-bottom:0;"></textarea>
            </div>
        </div>

        <!-- Submit -->
        <button type="button" id="pub-btn-submit" class="pub-submit-btn"
                onclick="pubSubmitBooking()">
            <i class="fas fa-calendar-check"></i> Kirim Booking Service
        </button>

        <div class="pub-footer">
            © <?= date('Y') ?> Mobile Auto Care · All rights reserved<br>
            <a href="https://wa.me/6282324001500" style="color:var(--pub-cyan);">
                <i class="fab fa-whatsapp"></i> 0823 2400 1500
            </a>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/id.min.js"></script>

    <script>
    var pubUnitCount  = 0;
    var pubUnitList   = {};
    var pubLokasi     = 'bengkel';
    var SITE_URL      = '<?= site_url() ?>';

    // ── INIT ───────────────────────────────────────────────────
    window.addEventListener('load', function() {
        pubAddUnit(); // otomatis 1 unit

        flatpickr('#tgl_booking', {
            locale       : 'id',
            minDate      : 'today',
            dateFormat   : 'Y-m-d',
            altInput     : true,
            altFormat    : 'j F Y',
            disableMobile: true,
            disable      : [function(date) { return date.getDay() === 0; }],
            onReady      : function(sd, ds, instance) {
                instance.altInput.className = 'pub-input';
                instance.altInput.style.cursor = 'pointer';
            },
            onChange     : function(selectedDates) {
                if (selectedDates.length > 0) {
                    document.getElementById('tgl_booking_raw').value =
                        selectedDates[0].toISOString().slice(0,10);
                    pubUpdateSlotWaktu(selectedDates[0].getDay());
                }
            },
        });
    });

    // ── UNIT KENDARAAN ─────────────────────────────────────────
    function pubAddUnit() {
        var idx  = pubUnitCount++;
        var list = document.getElementById('pub-unit-list');

        var html = '<div id="pub-unit-' + idx + '" class="pub-unit-row">';
        html += '<span class="pub-unit-badge">KENDARAAN ' + (idx + 1) + '</span>';

        if (idx > 0) {
            html += '<button type="button" class="pub-unit-remove" onclick="pubRemoveUnit(' + idx + ')">'
                  + '<i class="fas fa-times-circle"></i></button>';
        }

        // Nopol
        html += '<div class="pub-form-group">'
              + '<label class="pub-label">Nopol <span style="color:var(--pub-danger);">*</span></label>'
              + '<input type="text" id="pub-nopol-' + idx + '" class="pub-input"'
              + ' placeholder="Contoh: B 1234 ABC"'
              + ' oninput="this.value=this.value.toUpperCase()">'
              + '</div>';

        // Tipe
        html += '<div class="pub-form-group">'
              + '<label class="pub-label">Tipe Kendaraan <span style="color:var(--pub-danger);">*</span></label>'
              + '<input type="text" id="pub-tipe-' + idx + '" class="pub-input"'
              + ' placeholder="Contoh: Toyota Avanza">'
              + '</div>';

        // Jenis
        html += '<div class="pub-form-group">'
              + '<label class="pub-label">Jenis Kendaraan <span style="color:var(--pub-danger);">*</span></label>'
              + '<div class="pub-toggle-btn">'
              + '<button type="button" id="pub-jenis-mobil-' + idx + '" class="active"'
              + ' onclick="pubSetJenis(' + idx + ', \'Mobil\')">'
              + '<i class="fas fa-car"></i> Mobil</button>'
              + '<button type="button" id="pub-jenis-motor-' + idx + '"'
              + ' onclick="pubSetJenis(' + idx + ', \'Motor\')">'
              + '<i class="fas fa-motorcycle"></i> Motor</button>'
              + '</div>'
              + '<input type="hidden" id="pub-jenis-' + idx + '" value="Mobil">'
              + '</div>';

        // KM
        html += '<div class="pub-form-group">'
              + '<label class="pub-label">KM Saat Ini <span style="color:var(--pub-danger);">*</span></label>'
              + '<input type="text" id="pub-km-' + idx + '" class="pub-input"'
              + ' placeholder="Masukkan KM saat ini"'
              + ' oninput="pubFormatKM(this, ' + idx + ')">'
              + '<input type="hidden" id="pub-km-raw-' + idx + '">'
              + '</div>';

        // Keluhan
        html += '<div class="pub-form-group" style="margin-bottom:0;">'
              + '<label class="pub-label">Keluhan <span style="color:var(--pub-danger);">*</span></label>'
              + '<textarea id="pub-keluhan-' + idx + '" class="pub-input" rows="2"'
              + ' placeholder="Ceritakan keluhan kendaraan Anda..."'
              + ' style="resize:none;"></textarea>'
              + '</div>';

        html += '</div>';
        list.insertAdjacentHTML('beforeend', html);
        pubUnitList[idx] = true;
    }

    function pubRemoveUnit(idx) {
        var el = document.getElementById('pub-unit-' + idx);
        if (el) {
            el.style.opacity    = '0';
            el.style.transform  = 'translateY(-8px)';
            el.style.transition = 'all 0.2s ease';
            setTimeout(function() { el.remove(); }, 200);
        }
        delete pubUnitList[idx];
    }

    function pubSetJenis(idx, jenis) {
        document.getElementById('pub-jenis-' + idx).value = jenis;
        var btnMobil = document.getElementById('pub-jenis-mobil-' + idx);
        var btnMotor = document.getElementById('pub-jenis-motor-' + idx);
        if (jenis === 'Mobil') {
            btnMobil.classList.add('active');
            btnMotor.classList.remove('active');
        } else {
            btnMotor.classList.add('active');
            btnMobil.classList.remove('active');
        }
    }

    function pubFormatKM(el, idx) {
        var raw = el.value.replace(/\D/g, '');
        el.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        document.getElementById('pub-km-raw-' + idx).value = raw;
    }

    // ── SLOT WAKTU ─────────────────────────────────────────────
    function pubUpdateSlotWaktu(dayOfWeek) {
        var slots = [];

        if (dayOfWeek >= 1 && dayOfWeek <= 5) {
            slots = ['08.00 - 09.00','09.00 - 10.00','10.00 - 11.00',
                    '11.00 - 12.00','13.00 - 14.00','14.00 - 15.00','15.00 - 16.00'];
        } else if (dayOfWeek === 6) {
            slots = ['08.00 - 09.00','09.00 - 10.00','10.00 - 11.00',
                    '11.00 - 12.00','13.00 - 14.00'];
        }

        // Reset
        document.getElementById('waktu_catatan').value = '';
        document.getElementById('pub-waktu-text').textContent = '— Pilih Waktu —';
        document.getElementById('pub-waktu-text').style.color = 'var(--pub-text3)';

        // Enable trigger
        var trigger = document.getElementById('pub-waktu-trigger');
        trigger.style.opacity      = '1';
        trigger.style.pointerEvents = 'auto';

        // Render options
        var html = '';
        slots.forEach(function(slot) {
            html += '<div class="pub-select-option"'
                + ' data-value="' + slot + '"'
                + ' onclick="pubSelectWaktu(this)">'
                + '<i class="fas fa-clock" style="color:var(--pub-cyan); margin-right:8px; font-size:11px;"></i>'
                + slot
                + '</div>';
        });
        document.getElementById('pub-waktu-options').innerHTML = html;
    }

    function pubToggleWaktu() {
        var options = document.getElementById('pub-waktu-options');
        var arrow   = document.getElementById('pub-waktu-arrow');
        var isOpen  = options.classList.contains('show');
        if (isOpen) {
            options.classList.remove('show');
            arrow.style.transform = 'rotate(0deg)';
        } else {
            options.classList.add('show');
            arrow.style.transform = 'rotate(180deg)';
        }
    }

    function pubSelectWaktu(el) {
        var slot = el.getAttribute('data-value');
        document.getElementById('waktu_catatan').value        = slot;
        document.getElementById('pub-waktu-text').textContent = slot;
        document.getElementById('pub-waktu-text').style.color = 'var(--pub-text)';
        document.getElementById('pub-waktu-options').classList.remove('show');
        document.getElementById('pub-waktu-arrow').style.transform = 'rotate(0deg)';

        document.querySelectorAll('#pub-waktu-options .pub-select-option').forEach(function(o) {
            o.classList.remove('active');
        });
        el.classList.add('active');
    }

    // ── Tutup dropdown waktu jika klik di luar ─────────────────
    document.addEventListener('click', function(e) {
        var waktuWrapper = document.getElementById('pub-waktu-dropdown');
        if (waktuWrapper && !waktuWrapper.contains(e.target)) {
            var options = document.getElementById('pub-waktu-options');
            var arrow   = document.getElementById('pub-waktu-arrow');
            if (options) options.classList.remove('show');
            if (arrow)   arrow.style.transform = 'rotate(0deg)';
        }

        // Cabang dropdown
        var cabangWrapper = document.getElementById('pub-cabang-dropdown');
        if (cabangWrapper && !cabangWrapper.contains(e.target)) {
            document.getElementById('pub-cabang-options').classList.remove('show');
            document.getElementById('pub-cabang-arrow').style.transform = 'rotate(0deg)';
        }
    });

    // ── LOKASI ─────────────────────────────────────────────────
    function pubSetLokasi(val) {
        pubLokasi = val;
        var btnBengkel = document.getElementById('pub-btn-bengkel');
        var btnHome    = document.getElementById('pub-btn-home');
        var rowAlamat  = document.getElementById('pub-row-alamat');

        if (val === 'bengkel') {
            btnBengkel.classList.add('active');
            btnHome.classList.remove('active');
            rowAlamat.style.display = 'none';
        } else {
            btnHome.classList.add('active');
            btnBengkel.classList.remove('active');
            rowAlamat.style.display = 'block';
        }
        document.getElementById('lokasi_service').value = val;
    }

    // ── CABANG DROPDOWN ────────────────────────────────────────
    function pubToggleCabang() {
        var options = document.getElementById('pub-cabang-options');
        var arrow   = document.getElementById('pub-cabang-arrow');
        var isOpen  = options.classList.contains('show');
        if (isOpen) {
            options.classList.remove('show');
            arrow.style.transform = 'rotate(0deg)';
        } else {
            options.classList.add('show');
            arrow.style.transform = 'rotate(180deg)';
        }
    }

    function pubSelectCabang(el) {
        var value  = el.getAttribute('data-value');
        var alamat = el.getAttribute('data-alamat');

        document.getElementById('cabang_nama').value           = value;
        document.getElementById('pub-cabang-text').textContent = value;
        document.getElementById('pub-cabang-text').style.color = 'var(--pub-text)';
        document.getElementById('pub-cabang-alamat').textContent = alamat;
        document.getElementById('pub-cabang-dot').style.background = 'var(--pub-cyan)';

        document.getElementById('pub-cabang-options').classList.remove('show');
        document.getElementById('pub-cabang-arrow').style.transform = 'rotate(0deg)';

        document.querySelectorAll('#pub-cabang-options .pub-select-option').forEach(function(o) {
            o.classList.remove('active');
            o.querySelector('.pub-opt-dot').style.background = 'var(--pub-border)';
        });
        el.classList.add('active');
        el.querySelector('.pub-opt-dot').style.background = 'var(--pub-cyan)';
    }

    document.addEventListener('click', function(e) {
        var wrapper = document.getElementById('pub-cabang-dropdown');
        if (wrapper && !wrapper.contains(e.target)) {
            document.getElementById('pub-cabang-options').classList.remove('show');
            document.getElementById('pub-cabang-arrow').style.transform = 'rotate(0deg)';
        }
    });

    // ── SUBMIT ─────────────────────────────────────────────────
    function pubSubmitBooking() {
        var nama    = document.getElementById('nama_publik').value.trim();
        var notelp  = document.getElementById('no_telp_publik').value.trim();
        var tgl     = document.getElementById('tgl_booking_raw').value;
        var waktu   = document.getElementById('waktu_catatan').value;
        var cabang  = document.getElementById('cabang_nama').value;
        var alamat  = document.getElementById('alamat_service').value.trim();

        if (!nama) {
            Swal.fire({ icon:'warning', title:'Nama wajib diisi.', confirmButtonColor:'#1a2d7c' });
            return;
        }
        if (!notelp) {
            Swal.fire({ icon:'warning', title:'No. WhatsApp wajib diisi.', confirmButtonColor:'#1a2d7c' });
            return;
        }
        if (!tgl) {
            Swal.fire({ icon:'warning', title:'Pilih tanggal service.', confirmButtonColor:'#1a2d7c' });
            return;
        }
        if (!waktu) {
            Swal.fire({ icon:'warning', title:'Pilih waktu service.', confirmButtonColor:'#1a2d7c' });
            return;
        }
        if (!cabang) {
            Swal.fire({ icon:'warning', title:'Pilih cabang tujuan.', confirmButtonColor:'#1a2d7c' });
            return;
        }
        if (pubLokasi === 'home_service' && !alamat) {
            Swal.fire({ icon:'warning', title:'Alamat service wajib diisi.', confirmButtonColor:'#1a2d7c' });
            return;
        }

        // Build kendaraan
        var kendaraan  = [];
        var unitErrors = [];

        Object.keys(pubUnitList).forEach(function(idx) {
            if (!document.getElementById('pub-unit-' + idx)) return;

            var nopol   = document.getElementById('pub-nopol-'   + idx).value.trim().toUpperCase();
            var tipe    = document.getElementById('pub-tipe-'    + idx).value.trim();
            var jenis   = document.getElementById('pub-jenis-'   + idx).value;
            var kmRaw   = document.getElementById('pub-km-raw-'  + idx).value;
            var keluhan = document.getElementById('pub-keluhan-' + idx).value.trim();

            if (!nopol)   { unitErrors.push('Nopol kendaraan ' + (kendaraan.length+1) + ' wajib diisi.'); return; }
            if (!tipe)    { unitErrors.push('Tipe kendaraan ' + nopol + ' wajib diisi.'); return; }
            if (!kmRaw)   { unitErrors.push('KM kendaraan ' + nopol + ' wajib diisi.'); return; }
            if (!keluhan) { unitErrors.push('Keluhan kendaraan ' + nopol + ' wajib diisi.'); return; }

            kendaraan.push({
                nopol          : nopol,
                tipe           : tipe,
                jenis_kendaraan: jenis,
                km             : kmRaw,
                keluhan        : keluhan,
                is_new_unit    : 1,
            });
        });

        if (kendaraan.length === 0) {
            Swal.fire({ icon:'warning', title:'Tambahkan minimal 1 kendaraan.', confirmButtonColor:'#1a2d7c' });
            return;
        }

        if (unitErrors.length > 0) {
            Swal.fire({
                icon : 'warning',
                title: 'Data Belum Lengkap',
                html : '<ul style="text-align:left;">'
                     + unitErrors.map(function(e) { return '<li>' + e + '</li>'; }).join('')
                     + '</ul>',
                confirmButtonColor: '#1a2d7c',
            });
            return;
        }

        var btn = document.getElementById('pub-btn-submit');
        btn.disabled  = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

        $.ajax({
            url     : SITE_URL + 'booking/submit',
            type    : 'POST',
            dataType: 'JSON',
            data    : {
                nama_publik    : nama,
                no_telp_publik : notelp,
                tgl_booking    : tgl,
                waktu_catatan  : waktu,
                lokasi_service : pubLokasi,
                alamat_service : alamat,
                nama_cabang    : cabang,
                catatan        : document.getElementById('catatan').value,
                kendaraan      : kendaraan,
            },
            success: function(res) {
                btn.disabled  = false;
                btn.innerHTML = '<i class="fas fa-calendar-check"></i> Kirim Booking Service';

                if (res.status) {
                    Swal.fire({
                        icon             : 'success',
                        title            : 'Booking Terkirim!',
                        html             : 'Terima kasih! Tim MAC akan segera menghubungi Anda untuk konfirmasi jadwal.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#1a2d7c',
                    }).then(function() {
                        window.location.href = 'https://mobileautocare.co.id/';
                    });
                } else {
                    Swal.fire({ icon:'error', title:'Gagal', text:res.error, confirmButtonColor:'#1a2d7c' });
                }
            },
            error: function() {
                btn.disabled  = false;
                btn.innerHTML = '<i class="fas fa-calendar-check"></i> Kirim Booking Service';
                Swal.fire({ icon:'error', title:'Terjadi Kesalahan', text:'Coba lagi beberapa saat.', confirmButtonColor:'#1a2d7c' });
            }
        });
    }
    </script>

</body>
</html>