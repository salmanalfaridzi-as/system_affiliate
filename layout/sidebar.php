<?php
// 1. Logic PHP untuk Menentukan Menu Aktif
// Pastikan variabel $active sudah didefinisikan di halaman utama sebelum include sidebar
$active = $active ?? 'dashboard';

// Array halaman yang termasuk dalam grup Affiliate
$affiliate_pages = [
    'affiliate_link',
    'affiliate_coupon',
    'affiliate_order',
    'affiliate_commission',
    'affiliate_help' // Menambahkan Marketing Kit agar menu tidak tertutup
];

// Cek apakah sedang di halaman affiliate
$is_affiliate_active = in_array($active, $affiliate_pages);
?>

<aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
    <a href="../dashboard/" class="brand-link">
        <i class="bi bi-exclude brand-image opacity-75 shadow rounded ms-1" style="font-size: 1.2rem;"></i>
        
        <span class="brand-text fw-bold ms-2">Affiliate</span>
    </a>
</div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                
                <li class="nav-header">UTAMA</li>
                
                <li class="nav-item">
                    <a href="../dashboard/" class="nav-link <?= $active == 'dashboard' ? 'active' : ''; ?>">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">PROMOSI</li>

                <li class="nav-item <?= $is_affiliate_active ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $is_affiliate_active ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-share-fill"></i>
                        <p>
                            Affiliate
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview ms-4">
                        <li class="nav-item">
                            <a href="../affiliate/commission.php" class="nav-link <?= $active == 'affiliate_commission' ? 'active' : ''; ?>">
                                
                                <p>Komisi Saya</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../affiliate/link.php" class="nav-link <?= $active == 'affiliate_link' ? 'active' : ''; ?>">
                                
                                <p>Generate Link</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../affiliate/help.php" class="nav-link <?= $active == 'affiliate_help' ? 'active' : ''; ?>">
                                
                                <p>Marketing Kit</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../affiliate/coupon.php" class="nav-link <?= $active == 'affiliate_coupon' ? 'active' : ''; ?>">
                                
                                <p>Kupon Diskon</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../affiliate/order.php" class="nav-link <?= $active == 'affiliate_order' ? 'active' : ''; ?>">
                                
                                <p>Riwayat Order</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="../leaderboard/" class="nav-link <?= $active == 'leaderboard' ? 'active' : ''; ?>">
                        <i class="nav-icon bi bi-trophy-fill"></i>
                        <p>Klasemen Juara</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../order/" class="nav-link <?= $active == 'order' ? 'active' : ''; ?>">
                        <i class="nav-icon bi bi-bag-check-fill"></i>
                        <p>Riwayat Belanja</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../subscription/" class="nav-link <?= $active == 'subscription' ? 'active' : ''; ?>">
                        <i class="nav-icon bi bi-calendar-check-fill"></i>
                        <p>Langganan</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../access/" class="nav-link <?= $active == 'access' ? 'active' : ''; ?>">
                        <i class="nav-icon bi bi-unlock-fill"></i>
                        <p>Akses Produk</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../license/" class="nav-link <?= $active == 'license' ? 'active' : ''; ?>">
                        <i class="nav-icon bi bi-upc-scan"></i>
                        <p>Lisensi & Key</p>
                    </a>
                </li>

                <li class="nav-header">AKUN</li>
                
                <li class="nav-item">
                    <a href="../profile/" class="nav-link <?= $active == 'profile' ? 'active' : ''; ?>">
                        <i class="nav-icon bi bi-person-badge"></i>
                        <p>Profil Saya</p>
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a href="../auth/logout.php" class="nav-link text-danger">
                        <i class="nav-icon bi bi-box-arrow-left"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>