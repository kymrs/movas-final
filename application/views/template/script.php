<!-- Bootstrap core JavaScript-->
<script src="<?= base_url() ?>assets/backend/plugins/sb2/vendor/jquery/jquery.min.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/sb2/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Core plugin JavaScript-->
<script src="<?= base_url() ?>assets/backend/plugins/sb2/vendor/jquery-easing/jquery.easing.min.js"></script>
<!-- Custom scripts for all pages-->
<script src="<?= base_url() ?>assets/backend/plugins/sb2/js/sb-admin-2.min.js"></script>
<!-- Datatables -->
<script src="<?= base_url() ?>assets/backend/plugins/sb2/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/sb2/vendor/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- Validate -->
<script src="<?= base_url() ?>assets/backend/plugins/jquery/jquery.validate.min.js"></script>
<!-- Moment -->
<script src="<?= base_url() ?>assets/backend/plugins/moment/moment.min.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/moment/moment-with-locales.min.js"></script>
<!-- Tempus Dominus -->
<script src="<?= base_url() ?>assets/backend/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Swal -->
<script src="<?= base_url() ?>assets/backend/plugins/sweetalert/sweetalert2.all.min.js"></script>
<!-- SELECT2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- ChartJS -->
<script src="<?= base_url() ?>assets/backend/plugins/chartjs/chart.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/chartjs/chartjs-plugin-datalabels.min.js"></script>
<script>
    $(document).ready(function() {
        checkNotifications();
    });

    function checkNotifications() {
        $.ajax({
            url: "<?= base_url('notifikasi/get_pending_notifications') ?>",
            type: "GET",
            dataType: "json",
            success: function(data) {
                // $('.pu-notif').text(1).css('display', 'inline-block');
                console.log(data);
                // Update elemen notifikasi dengan data baru

                $.each(data.notif_pending, function(key, value) {
                    if (value > 0) {
                        // Jika ada notifikasi baru, tampilkan jumlah notifikasi
                        $('#' + key + '-notif').text(value).css('display', 'inline-block');
                    } else {
                        // Sembunyikan notifikasi jika tidak ada
                        $('#' + key + '-notif').hide();
                    }
                });

                $.each(data.notif_menu, function(menu, value) {
                    if (value > 0) {
                        $('#' + menu + '-notif').text(value).css('display', 'inline-block');
                    } else {
                        $('#' + menu + '-notif').hide();
                    }
                });

                // // Panggil fungsi lagi setelah sukses
                // checkNotifications();
                setTimeout(checkNotifications, 300000);
            },
            error: function() {
                // Jika gagal, tunggu beberapa detik sebelum mencoba lagi
                setTimeout(checkNotifications, 120000);
            }
        });
    }
</script>