<?php
// Pastikan path ini sesuai dengan struktur folder Anda
$baseUrl = '../'; // Sesuaikan jika perlu
?>
        <footer class="app-footer small"> 
            <div class="float-end d-none d-sm-inline">
                <a href="../info/terms.php" class="text-decoration-none text-muted me-2">Syarat & Ketentuan</a>
                <span class="text-muted opacity-50">|</span>
                <a href="../info/refund.php" class="text-decoration-none text-muted mx-2">Refund Policy</a>
                <span class="text-muted opacity-50">|</span>
                <a href="../info/faq.php" class="text-decoration-none text-muted ms-2">FAQ / Bantuan</a>
            </div>
            
            <strong>Copyright &copy; <?= date('Y') ?> <a href="#" class="text-decoration-none fw-bold">MyTahfidz Affiliate</a>.</strong> 
            <span class="text-muted d-none d-md-inline ms-1">All rights reserved.</span>
        </footer> 
        </div> 
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>

    <script>
        // A. Inisialisasi Scrollbar Sidebar
        const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
        const Default = {
            scrollbarTheme: "os-theme-light",
            scrollbarAutoHide: "leave",
            scrollbarClickScroll: true,
        };
        document.addEventListener("DOMContentLoaded", function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (sidebarWrapper && typeof OverlayScrollbarsGlobal !== "undefined") {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });

        // B. Inisialisasi Grafik (Hanya render jika elemen chart ada di halaman ini)
        if (document.querySelector("#revenue-chart")) {
            const sales_chart_options = {
                series: [{
                    name: "Komisi",
                    data: [100000, 400000, 350000, 500000, 490000, 600000, 700000, 910000, 1250000]
                }],
                chart: {
                    height: 300,
                    type: 'area',
                    fontFamily: 'inherit', // Biar font ngikut body
                    toolbar: { show: false }
                },
                colors: ['#0d6efd'], // Warna Biru Bootstrap Primary
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep'],
                    labels: { style: { colors: '#adb5bd' } }
                },
                grid: {
                    borderColor: '#f4f6f9',
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    }
                }
            };
            const sales_chart = new ApexCharts(document.querySelector("#revenue-chart"), sales_chart_options);
            sales_chart.render();
        }
    </script>
</body>
</html>