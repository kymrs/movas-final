<div id="content-wrapper" class="d-flex flex-column">
  <div id="content">
    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
      <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
      </button>
      <!-- Date & Time -->
      <div class="navbar-datetime d-none d-md-flex align-items-center">
          <div class="datetime-icon">
              <i class="fas fa-calendar-alt"></i>
          </div>

          <div class="datetime-content">
              <div id="currentDate" class="current-date"></div>
              <div id="currentTime" class="current-time"></div>
          </div>
      </div>
      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown no-arrow">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php
              $cabang_id = $this->session->userdata('cabang_id');

              $nama_cabang = $this->db
                  ->select('nama_cabang')
                  ->where('id', $cabang_id)
                  ->get('mac_cabang')
                  ->row('nama_cabang');

              $nama_cabang = ucwords(strtolower(trim(str_replace('MAC', '', $nama_cabang))));
            ?>
            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><span style="font-weight: bold;"><?= $this->session->userdata('fullname') ?></span> <span style="margin: 0 4px;position: relative; top: -1px">|</span> <?= $nama_cabang ?></span>
            <img style="border: 1px solid #242d4a74" class="img-profile rounded-circle" src="<?= ($this->session->userdata('image') != '' ? base_url('assets/backend/img/user/') . $this->session->userdata('image') : base_url('assets/backend/img/') . 'default.jpg'); ?>" class="img-circle elevation-2" alt="User Image">
          </a>
          <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#changePasswordModal">
              <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
              Ubah Password
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
              <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
              Logout
            </a>
            <!-- <a class="dropdown-item" target="_blank" href="<?= base_url() ?>assets/backend/document/tata_cara_approval.pdf">
              <i class="fas fa-file-pdf fa-sm fa-fw mr-2 text-gray-400"></i>
              Tatacara
            </a> -->
          </div>
        </li>
      </ul>
    </nav>
    
    <!-- Modal Change Password -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="changePasswordModalLabel">Ubah Password</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form id="formChangePassword">
            <div class="modal-body">
              <div id="changePasswordAlert"></div>
              <!-- Hidden username field for accessibility and password manager support -->
              <input type="hidden" name="username" value="<?= $this->session->userdata('username') ?>">
              <div class="form-group">
                <label for="current_password">Password Saat Ini</label>
                <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" required>
                <small class="form-text text-muted">Masukkan password saat ini</small>
              </div>
              <div class="form-group">
                <label for="new_password">Password Baru</label>
                <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="new-password" required>
                <small class="form-text text-muted">Password minimal 3 karakter</small>
              </div>
              <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
                <small class="form-text text-muted">Ulangi password baru</small>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
              <button type="button" class="btn btn-primary" id="btnChangePassword">Ubah Password</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
    function getTimezoneInfo() {
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        const timezoneMap = {
            'Asia/Jakarta': 'WIB',
            'Asia/Pontianak': 'WIB',
            'Asia/Makassar': 'WITA',
            'Asia/Bangkok': 'ICT',
            'Asia/Kuala_Lumpur': 'MYT',
            'Asia/Singapore': 'SGT',
            'Asia/Tokyo': 'JST',
            'Asia/Seoul': 'KST',
            'Asia/Manila': 'PHT',
            'Australia/Perth': 'AWST',
            'Australia/Darwin': 'ACST',
            'Australia/Brisbane': 'AEST'
        };

        return timezoneMap[timezone] || getUTCOffset();
    }


    function getUTCOffset() {

        const offset = -new Date().getTimezoneOffset() / 60;

        if (offset === 7) {
            return 'WIB';
        }

        if (offset === 8) {
            return 'WITA';
        }

        if (offset === 9) {
            return 'WIT';
        }

        return 'UTC' + (
            offset >= 0
                ? '+' + offset
                : offset
        );
    }


    function updateDateTime() {

        const now = new Date();

        const days = [
            'Minggu',
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu'
        ];

        const months = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];

        const day = days[now.getDay()];
        const date = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        const timezone = getTimezoneInfo();

        document.getElementById('currentDate').textContent =
            `${day}, ${date} ${month} ${year}`;

        document.getElementById('currentTime').innerHTML =
            `${hours}:${minutes}:${seconds} <span class="timezone">${timezone}</span>`;
    }


    updateDateTime();

    setInterval(updateDateTime, 1000);

    // Tunggu hingga DOM dan jQuery siap
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Change password script initializing');
        
        var btnChangePassword = document.getElementById('btnChangePassword');
        
        if (btnChangePassword) {
            btnChangePassword.addEventListener('click', function(e) {
                e.preventDefault();
                
                var current_password = document.getElementById('current_password').value;
                var new_password = document.getElementById('new_password').value;
                var confirm_password = document.getElementById('confirm_password').value;
                
                console.log('Button clicked');
                console.log('Current password filled:', !!current_password);
                console.log('New password filled:', !!new_password);
                console.log('Confirm password filled:', !!confirm_password);
                
                // Validasi field tidak boleh kosong
                if (!current_password || !new_password || !confirm_password) {
                    showAlert('danger', 'Semua field harus diisi!');
                    return false;
                }
                
                // Validasi password baru minimal 6 karakter
                if (new_password.length < 3) {
                    showAlert('danger', 'Password baru minimal harus 3 karakter!');
                    return false;
                }
                
                // Validasi konfirmasi password
                if (new_password !== confirm_password) {
                    showAlert('danger', 'Password baru dan konfirmasi password tidak cocok!');
                    return false;
                }
                
                // Disable button saat loading
                btnChangePassword.disabled = true;
                btnChangePassword.textContent = 'Memproses...';
                
                console.log('Sending AJAX request...');
                
                // Simple AJAX request menggunakan fetch API
                var ajaxUrl = '<?= base_url("Auth/change_password") ?>';
                console.log('URL:', ajaxUrl);
                
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        current_password: current_password,
                        new_password: new_password,
                        confirm_password: confirm_password
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Response received:', data);
                    
                    if (data.success === true) {
                        showAlert('success', data.message);
                        document.getElementById('formChangePassword').reset();
                        
                        setTimeout(function() {
                            if (typeof jQuery !== 'undefined') {
                                jQuery('#changePasswordModal').modal('hide');
                            }
                            document.getElementById('changePasswordAlert').innerHTML = '';
                        }, 2000);
                    } else {
                        showAlert('danger', data.message);
                        btnChangePassword.disabled = false;
                        btnChangePassword.textContent = 'Ubah Password';
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    showAlert('danger', 'Error: ' + error.message);
                    btnChangePassword.disabled = false;
                    btnChangePassword.textContent = 'Ubah Password';
                });
                
                return false;
            });
        } else {
            console.warn('Button #btnChangePassword tidak ditemukan');
        }
        
        function showAlert(type, message) {
            var alertDiv = document.getElementById('changePasswordAlert');
            var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            
            alertDiv.innerHTML = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '</div>';
            
            console.log('Alert shown [' + type + ']:', message);
        }
    });
    </script>
