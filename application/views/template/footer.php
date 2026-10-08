</div>
<!-- End of Main Content -->

<!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>© <?= date('Y') ?> Mobile Auto Care. All rights reserved.</span>
        </div>
    </div>
</footer>
<!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="<?= base_url('auth/logout') ?>">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- ===== MAC LOADER WITH REFRESH FALLBACK ===== -->

<div id="mac-loader-overlay">
  <div class="mac-particles" id="mac-particles"></div>
  <div class="mac-loader-content">

    <div class="mac-ring-outer">
      <div class="mac-ring-inner">
        <div class="mac-logo-circle">
          <img src="<?= base_url() ?>assets/backend/img/mobileautocare-white.png"
               alt="MAC" class="mac-logo-img" />
        </div>
      </div>
    </div>

    <div class="mac-brand-block">
      <div class="mac-brand-name">Mobile Auto Care</div>
      <div class="mac-brand-sub">Inventory Management System</div>
    </div>

    <div class="mac-loading-text" id="mac-loading-text">Memuat sistem...</div>

    <div class="mac-progress-wrap">
      <div class="mac-progress-track"></div>
    </div>

    <div class="mac-dots">
      <span class="mac-dot"></span>
      <span class="mac-dot"></span>
      <span class="mac-dot"></span>
    </div>

    <!-- Tombol Refresh / Batal (Hidden secara default, muncul jika loading lama) -->
    <div class="mac-fallback-action" id="mac-fallback-action">
      <button type="button" class="mac-btn-refresh" onclick="window.location.reload();">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M23 4v6h-6"></path>
          <path d="M1 20v-6h6"></path>
          <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
        </svg>
        Segarkan Halaman
      </button>
      <!-- <button type="button" class="mac-btn-close" onclick="macLoader.hide();">
        Tutup
      </button> -->
    </div>

  </div>
</div>

<style>
  #mac-loader-overlay {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    background: linear-gradient(135deg, #0a1628 0%, #0d1f3c 40%, #0f2347 70%, #0a1628 100%);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 99999;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
    overflow: hidden;
  }
  #mac-loader-overlay.mac-active {
    display: flex;
    opacity: 1;
  }

  /* Partikel */
  .mac-particles {
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
  }
  .mac-particle {
    position: absolute;
    border-radius: 50%;
    animation: macFloatUp linear infinite;
  }

  /* Konten utama */
  .mac-loader-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    z-index: 2;
  }

  /* Ring luar */
  .mac-ring-outer {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    border: 1px solid rgba(99, 145, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    animation: macRotateSlow 6s linear infinite;
  }
  .mac-ring-outer::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 50%;
    border: 2.5px solid transparent;
    border-top-color: #4d7cfe;
    border-right-color: rgba(77, 124, 254, 0.4);
    animation: macRotateFast 1.4s linear infinite;
  }
  .mac-ring-outer::after {
    content: '';
    position: absolute;
    inset: 6px;
    border-radius: 50%;
    border: 1px solid rgba(99, 145, 255, 0.08);
  }

  /* Ring dalam */
  .mac-ring-inner {
    width: 94px;
    height: 94px;
    border-radius: 50%;
    border: 1px solid rgba(99, 145, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    animation: macRotateSlow 4s linear infinite reverse;
  }
  .mac-ring-inner::before {
    content: '';
    position: absolute;
    inset: -1px;
    border-radius: 50%;
    border: 1.5px solid transparent;
    border-bottom-color: rgba(99, 145, 255, 0.6);
    border-left-color: rgba(99, 145, 255, 0.2);
    animation: macRotateFast 2s linear infinite reverse;
  }

  /* Lingkaran logo */
  .mac-logo-circle {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    background: linear-gradient(145deg, #0d1f3c, #162a50);
    border: 1.5px solid rgba(77, 124, 254, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: macPulseLogo 2.2s ease-in-out infinite;
  }
  .mac-logo-img {
    width: 62px;
    height: auto;
    object-fit: contain;
    filter: brightness(1.1);
  }

  /* Brand */
  .mac-brand-block {
    margin-top: 28px;
  }
  .mac-brand-name {
    font-family: 'Segoe UI', Roboto, sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 4px;
    text-transform: uppercase;
  }
  .mac-brand-sub {
    font-family: 'Segoe UI', Roboto, sans-serif;
    font-size: 10px;
    color: rgba(255, 255, 255, 0.3);
    letter-spacing: 2px;
    margin-top: 5px;
    text-transform: uppercase;
  }

  /* Teks loading */
  .mac-loading-text {
    font-family: 'Segoe UI', Roboto, sans-serif;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.35);
    margin-top: 22px;
    letter-spacing: 1px;
    min-height: 16px;
    transition: opacity 0.35s ease;
  }

  /* Progress bar */
  .mac-progress-wrap {
    width: 180px;
    height: 2px;
    background: rgba(255, 255, 255, 0.07);
    border-radius: 10px;
    margin-top: 12px;
    overflow: hidden;
  }
  .mac-progress-track {
    height: 100%;
    width: 40%;
    background: linear-gradient(90deg, #4d7cfe, #6391ff);
    border-radius: 10px;
    animation: macMoveBar 1.5s ease-in-out infinite;
  }

  /* Dots */
  .mac-dots {
    margin-top: 14px;
    display: flex;
    gap: 5px;
    align-items: center;
  }
  .mac-dot {
    display: inline-block;
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: rgba(77, 124, 254, 0.8);
    animation: macDotBounce 1.2s ease-in-out infinite;
  }
  .mac-dot:nth-child(2) { animation-delay: 0.2s; }
  .mac-dot:nth-child(3) { animation-delay: 0.4s; }

  /* Fallback Action Buttons */
  .mac-fallback-action {
    margin-top: 24px;
    display: none; /* Disembunyikan, hanya muncul jika loading lama */
    gap: 10px;
    align-items: center;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.4s ease;
  }
  .mac-fallback-action.show-btn {
    display: flex;
    opacity: 1;
    transform: translateY(0);
  }

  .mac-btn-refresh {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: rgba(77, 124, 254, 0.15);
    border: 1px solid rgba(77, 124, 254, 0.4);
    border-radius: 20px;
    color: #ffffff;
    font-family: 'Segoe UI', Roboto, sans-serif;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .mac-btn-refresh:hover {
    background: #4d7cfe;
    box-shadow: 0 4px 12px rgba(77, 124, 254, 0.3);
  }

  .mac-btn-close {
    padding: 8px 14px;
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    color: rgba(255, 255, 255, 0.5);
    font-family: 'Segoe UI', Roboto, sans-serif;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .mac-btn-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
  }

  /* Keyframes */
  @keyframes macRotateSlow { to { transform: rotate(360deg); } }
  @keyframes macRotateFast { to { transform: rotate(360deg); } }
  @keyframes macPulseLogo {
    0%, 100% { box-shadow: 0 0 0 0 rgba(77, 124, 254, 0); }
    50%       { box-shadow: 0 0 20px 5px rgba(77, 124, 254, 0.15); }
  }
  @keyframes macMoveBar {
    0%   { margin-left: -40%; }
    100% { margin-left: 100%; }
  }
  @keyframes macDotBounce {
    0%, 100% { transform: translateY(0); opacity: 0.35; }
    50%       { transform: translateY(-5px); opacity: 1; }
  }
  @keyframes macFloatUp {
    0%   { transform: translateY(0) translateX(0); opacity: 0; }
    10%  { opacity: 0.8; }
    90%  { opacity: 0.1; }
    100% { transform: translateY(-100vh) translateX(var(--dx, 0px)); opacity: 0; }
  }
</style>

</body>