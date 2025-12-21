<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="../admin/dashboard/" class="brand-link">
            <span class="brand-text fw-light">Admin Panel</span>
        </a>
    </div>
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                
                <li class="nav-header">UTAMA</li>
                <li class="nav-item">
                    <a href="../dashboard/" class="nav-link <?= $active=='dashboard'?'active':'' ?>">
                        <i class="nav-icon bi bi-speedometer"></i> <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../products/" class="nav-link <?= $active=='products'?'active':'' ?>">
                        <i class="nav-icon bi bi-box-seam"></i> <p>Produk</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../coupons/" class="nav-link <?= $active=='coupons'?'active':'' ?>">
                        <i class="nav-icon bi bi-ticket-perforated"></i> <p>Kupon Global</p>
                    </a>
                </li>

                <li class="nav-header">KEUANGAN</li>
                <li class="nav-item">
                    <a href="../orders/" class="nav-link <?= $active=='orders'?'active':'' ?>">
                        <i class="nav-icon bi bi-cart-check"></i> <p>Data Pesanan</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../withdrawals/" class="nav-link <?= $active=='withdrawals'?'active':'' ?>">
                        <i class="nav-icon bi bi-cash-coin"></i> <p>Request Payout</p>
                    </a>
                </li>

                <li class="nav-header">PENGGUNA</li>
                <li class="nav-item">
                    <a href="../users/" class="nav-link <?= $active=='users'?'active':'' ?>">
                        <i class="nav-icon bi bi-people"></i> <p>Data User</p>
                    </a>
                </li>
                
                <li class="nav-item mt-3">
                    <a href="../auth/logout.php" class="nav-link text-danger">
                        <i class="nav-icon bi bi-box-arrow-right"></i> <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>