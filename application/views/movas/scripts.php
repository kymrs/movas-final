<script>
// Fancy Box
window.addEventListener('load', function() {
    setTimeout(function() {
        Fancybox.bind('.glightbox', {
            groupAll: false,
            closeOnOutsideClick: true,
            caption: function(fancybox, slide) {
                return slide.triggerEl
                    ? (slide.triggerEl.getAttribute('data-caption') || '')
                    : '';
            },
        });
    }, 300);
});

$(function() {

    // ── TOGGLE ACCORDION KONDISI ───────────────────────────────
    function toggleAccordion(header) {
        var body    = $(header).next('.accordion-body');
        var chevron = $(header).find('.fa-chevron-down');
        var isOpen  = body.hasClass('show');

        body.toggleClass('show');
        chevron.css('transform', isOpen ? 'rotate(0deg)' : 'rotate(180deg)');
    }

    // Expose ke global karena dipanggil dari onclick di PHP
    window.toggleAccordion = toggleAccordion;

    // ── TOGGLE TIMELINE DETAIL ─────────────────────────────────
    function toggleTimeline(header, idx) {
        var detail  = $('#detail-' + idx);
        var chevron = $('#chevron-timeline-' + idx);
        var isOpen  = detail.hasClass('show');

        detail.toggleClass('show');
        chevron.css('transform', isOpen ? 'rotate(0deg)' : 'rotate(180deg)');
    }

    window.toggleTimeline = toggleTimeline;

    // ── SWITCH KONDISI PER CHIP TANGGAL ───────────────────────
    function switchKondisi(el, sesiId) {
        showLoading();

        // Ambil catatan dari option yang dipilih
        var selectedOption = el.options ? el.options[el.selectedIndex] : null;
        var catatan = selectedOption ? (selectedOption.getAttribute('data-catatan') || '') : '';

        $.ajax({
            url: SITE_URL + 'get_kondisi_ajax',
            type: 'POST',
            dataType: 'JSON',
            data: {
                token   : MOVAS_TOKEN,
                sesi_id : sesiId,
            },
            success: function(res) {
                hideLoading();
                if (!res.status) { alert('Gagal memuat data.'); return; }

                // Update catatan
                var cat = (res.catatan || '').trim();
                if (cat !== '' && cat !== '-') {
                    $('#catatan-text').text(cat);
                    $('#catatan-wrapper').show();
                } else {
                    $('#catatan-wrapper').hide();
                }

                // Update kondisi
                renderKondisi(res.kondisi, res.sesi);

                // ── Update gallery foto ──────────────────────────────
                if (typeof updateFotoGallery === 'function') {
                    updateFotoGallery(res.foto_sesi || []);
                }
            },
            error: function() {
                hideLoading();
                alert('Terjadi kesalahan. Coba lagi.');
            }
        });
    }

    window.switchKondisi = switchKondisi;

    // Catatan
    if (res.catatan && res.catatan.trim() !== '' && res.catatan.trim() !== '-') {
        $('#catatan-text').text(res.catatan);
        $('#catatan-wrapper').show();
    } else {
        $('#catatan-wrapper').hide();
    }

    // ── RENDER KONDISI (dari AJAX) ─────────────────────────────
    function renderKondisi(kondisi, sesi) {
        var kritis    = ['Perlu Ganti','Rusak','Bocor','Retak','Robek','Aus','Habis',
                         'Mati','Getas','Hilang','Tidak Berfungsi','Perlu Overhaul'];
        var perhatian = ['Kotor','Tipis','Lemah','Kurang Dingin','Kurang',
                         'Kendur','Berat','Perlu Servis','Perlu Tune Up'];

        function kondisiBadge(val) {
            if (!val) return '<span style="color:var(--movas-text3)">—</span>';
            if (kritis.indexOf(val)    > -1) return '<span class="kondisi-badge critical">' + val + '</span>';
            if (perhatian.indexOf(val) > -1) return '<span class="kondisi-badge attention">' + val + '</span>';
            return '<span class="kondisi-badge safe">' + val + '</span>';
        }

        var skipKeys = ['id','sesi_id','created_at','updated_at'];

        var labelMap = {
            tali_kipas:'Tali Kipas', oli_mesin:'Oli Mesin', filter_oli:'Filter Oli',
            slang_radiator:'Slang Radiator', air_radiator:'Air Radiator', tune_up:'Tune Up',
            busi:'Busi', baterai_mesin:'Baterai', filter_udara:'Filter Udara', filter_bbm:'Filter BBM',
            oli_transmisi:'Oli Transmisi', oli_differential:'Oli Differential',
            kabel_kopling:'Kabel Kopling', master_kopling:'Master Kopling',
            sistem_rem:'Sistem Rem', master_rem:'Master Rem', brake_pad:'Brake Pad',
            disc_brake:'Disc Brake', brake_shoe:'Brake Shoe', tromol:'Tromol',
            power_steering:'Power Steering', boot_drive_shaft:'Boot Drive Shaft',
            ball_joint:'Ball Joint', suspensi_depan:'Suspensi Depan',
            suspensi_belakang:'Suspensi Belakang', motor_extra_fan:'Motor Extra Fan',
            lampu_sein_depan_kanan:'Sein Depan Kanan', lampu_sein_depan_kiri:'Sein Depan Kiri',
            lampu_sein_belakang_kanan:'Sein Belakang Kanan', lampu_sein_belakang_kiri:'Sein Belakang Kiri',
            lampu_kecil_depan_kanan:'Kecil Depan Kanan', lampu_kecil_depan_kiri:'Kecil Depan Kiri',
            lampu_kecil_belakang_kanan:'Kecil Belakang Kanan', lampu_kecil_belakang_kiri:'Kecil Belakang Kiri',
            lampu_besar_pendek_kanan:'Besar Pendek Kanan', lampu_besar_pendek_kiri:'Besar Pendek Kiri',
            lampu_besar_jauh_kanan:'Besar Jauh Kanan', lampu_besar_jauh_kiri:'Besar Jauh Kiri',
            lampu_kabut_kanan:'Kabut Kanan', lampu_kabut_kiri:'Kabut Kiri',
            lampu_rem_kanan:'Rem Kanan', lampu_rem_kiri:'Rem Kiri',
            lampu_spoiler:'Spoiler', lampu_plat_no_pol:'Plat No Pol', lampu_mundur:'Mundur',
            sensor_parkir:'Sensor Parkir', lampu_plafon_depan:'Plafon Depan',
            lampu_plafon_tengah:'Plafon Tengah', lampu_panel_speedometer:'Panel Speedometer',
            lampu_hazard:'Hazard', fungsi_panel_wiper:'Panel Wiper', klakson:'Klakson',
            fungsi_radio_tape:'Radio/Tape', cd_player:'CD Player', fungsi_speaker:'Speaker',
            antena:'Antena', central_lock_depan_kanan:'CL Depan Kanan',
            central_lock_depan_kiri:'CL Depan Kiri', central_lock_belakang_kanan:'CL Belakang Kanan',
            central_lock_belakang_kiri:'CL Belakang Kiri', power_window_depan_kanan:'PW Depan Kanan',
            power_window_depan_kiri:'PW Depan Kiri', power_window_belakang_kanan:'PW Belakang Kanan',
            power_window_belakang_kiri:'PW Belakang Kiri', electric_mirror:'Electric Mirror',
            jam:'Jam', pemantik:'Pemantik', ac_depan:'AC Depan', ac_tengah:'AC Tengah',
            filter_cabin:'Filter Cabin', asbak:'Asbak', karpet:'Karpet',
            accu_tegangan:'Tegangan Accu', accu_merk:'Merk Accu', accu_kondisi:'Kondisi Accu',
            kursi_depan_kanan:'Kursi Depan Kanan', kursi_depan_kiri:'Kursi Depan Kiri',
            kursi_tengah_kanan:'Kursi Tengah Kanan', kursi_tengah_kiri:'Kursi Tengah Kiri',
            kursi_belakang_kanan:'Kursi Belakang Kanan', kursi_belakang_kiri:'Kursi Belakang Kiri',
            penahan_matahari:'Penahan Matahari', wiper_blade_depan:'Wiper Depan',
            wiper_blade_belakang:'Wiper Belakang', spion_luar_kanan:'Spion Luar Kanan',
            spion_luar_kiri:'Spion Luar Kiri', spion_dalam:'Spion Dalam', mirror_lamp:'Mirror Lamp',
            mudguard_depan_kanan:'Mudguard Depan Kanan', mudguard_depan_kiri:'Mudguard Depan Kiri',
            mudguard_belakang_kanan:'Mudguard Belakang Kanan', mudguard_belakang_kiri:'Mudguard Belakang Kiri',
            foot_step:'Foot Step', liner_fender_kanan:'Liner Fender Kanan',
            liner_fender_kiri:'Liner Fender Kiri', toolkit_tang:'Tang', toolkit_obeng:'Obeng',
            toolkit_kunci_ring:'Kunci Ring', toolkit_kunci_pas:'Kunci Pas',
            toolkit_kunci_busi:'Kunci Busi', toolkit_handle_towing:'Handle Towing',
            toolkit_kunci_roda:'Kunci Roda', toolkit_dongkrak:'Dongkrak',
            segitiga_pengaman:'Segitiga Pengaman', kotak_p3k:'Kotak P3K',
            kunci_kontak:'Kunci Kontak', remot:'Remot', stnk:'STNK',
            buku_kir:'Buku KIR', buku_servis:'Buku Servis',
        };

        var kategoriConfig = [
            { key: 'mesin',        label: 'Mesin',              icon: 'fa-cogs'      },
            { key: 'chassis',      label: 'Chassis & Bodi',     icon: 'fa-car'       },
            { key: 'kelistrikan',  label: 'Kelistrikan',        icon: 'fa-bolt'      },
            { key: 'kelistrikan2', label: 'AC, Central Lock & Accu', icon: 'fa-snowflake'},
            { key: 'safety',       label: 'Safety Belt & Jok',  icon: 'fa-shield-alt'},
            { key: 'eksterior',    label: 'Eksterior',          icon: 'fa-car-side'  },
            { key: 'perlengkapan', label: 'Perlengkapan',       icon: 'fa-toolbox'   },
        ];

        var html = '';

        $.each(kategoriConfig, function(i, kat) {
            var data = kondisi[kat.key];
            if (!data || $.isEmptyObject(data)) return;

            var rows = '';
            $.each(data, function(col, val) {
                if (skipKeys.indexOf(col) > -1 || val === null || val === '') return;
                var lbl = labelMap[col] || col;

                // ── TAMBAHAN: khusus accu_tegangan ────────────────
                var badgeHtml;
                if (col === 'accu_tegangan') {
                    var v = parseFloat(val.toString().replace(',', '.'));
                    var color = v >= 12.6
                        ? 'var(--movas-safe)'
                        : (v >= 12.0 ? 'var(--movas-warn)' : 'var(--movas-danger)');
                    badgeHtml = '<span style="color:' + color + '; font-weight:600;">' + val + ' V</span>';
                } else {
                    badgeHtml = kondisiBadge(val);
                }
                // ── END TAMBAHAN ──────────────────────────────────

                rows += '<div class="kondisi-row">'
                    + '<div class="kondisi-label">' + lbl + '</div>'
                    + '<div>' + badgeHtml + '</div>'  // ← ganti kondisiBadge(val) dengan badgeHtml
                    + '</div>';
            });

            if (!rows) return;

            html += '<div class="kondisi-accordion">'
                + '<div class="accordion-header" onclick="toggleAccordion(this)">'
                + '<div class="accordion-header-left">'
                + '<i class="fas ' + kat.icon + '" style="color:var(--movas-cyan); width:16px;"></i>'
                + kat.label
                + '</div>'
                + '<i class="fas fa-chevron-down" style="color:var(--movas-text3); font-size:11px; transition:transform 0.2s;"></i>'
                + '</div>'
                + '<div class="accordion-body">' + rows + '</div>'
                + '</div>';
        });

        // Ban
        if (kondisi.ban && kondisi.ban.length > 0) {
            var banRows = '';
            $.each(kondisi.ban, function(i, ban) {
                var pctColor = ban.kondisi_pct >= 60
                    ? 'var(--movas-safe)'
                    : (ban.kondisi_pct >= 30 ? 'var(--movas-warn)' : 'var(--movas-danger)');

                banRows += '<div style="padding:8px 0; border-bottom:1px solid var(--movas-border);">'
                         + '<div style="font-size:12px; font-weight:600; margin-bottom:5px;">' + ban.posisi + '</div>'
                         + '<div style="display:flex; gap:16px; font-size:11px; flex-wrap:wrap;">';

                if (ban.ukuran)      banRows += '<span style="color:var(--movas-text2);">Ukuran: <strong>' + ban.ukuran + '</strong></span>';
                if (ban.merk)        banRows += '<span style="color:var(--movas-text2);">Merk: <strong>' + ban.merk + '</strong></span>';
                if (ban.kondisi_pct) banRows += '<span style="color:var(--movas-text2);">Kondisi: <strong style="color:' + pctColor + '">' + ban.kondisi_pct + '%</strong></span>';
                if (ban.status)      banRows += '<span>' + kondisiBadge(ban.status) + '</span>';

                banRows += '</div></div>';
            });

            html += '<div class="kondisi-accordion">'
                  + '<div class="accordion-header" onclick="toggleAccordion(this)">'
                  + '<div class="accordion-header-left">'
                  + '<i class="fas fa-circle-notch" style="color:var(--movas-cyan); width:16px;"></i> Ban'
                  + '</div>'
                  + '<i class="fas fa-chevron-down" style="color:var(--movas-text3); font-size:11px; transition:transform 0.2s;"></i>'
                  + '</div>'
                  + '<div class="accordion-body">' + banRows + '</div>'
                  + '</div>';
        }

        if (!html) {
            html = '<div style="text-align:center; padding:30px; color:var(--movas-text3);">'
                 + '<i class="fas fa-clipboard" style="font-size:32px; opacity:0.3; margin-bottom:10px; display:block;"></i>'
                 + 'Belum ada data kondisi untuk tanggal ini.</div>';
        }

        $('#kondisi-wrapper').fadeOut(150, function() {
            $(this).html(html).fadeIn(150);
        });
    }
});
</script>