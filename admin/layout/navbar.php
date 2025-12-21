<?php
// layout/navbar.php

// Cek session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil data user
$userName  = $_SESSION['user_name'] ?? 'Guest';
$userEmail = $_SESSION['user_email'] ?? 'guest@example.com';
?>

<nav class="app-header navbar navbar-expand bg-body shadow-sm" style="min-height: 57px;"> 
    <div class="container-fluid"> 
        
        <ul class="navbar-nav"> 
            <li class="nav-item"> 
                <a class="nav-link d-flex align-items-center justify-content-center p-2" data-lte-toggle="sidebar" href="#" role="button"> 
                    <i class="bi bi-list fs-4" style="line-height: 1;"></i> 
                </a> 
            </li> 
        </ul> 

        <ul class="navbar-nav ms-auto"> 
            <li class="nav-item dropdown user-menu"> 
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown"> 
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px;">
                        <i class="bi bi-person-fill fs-5"></i>
                    </div>
                    <span class="d-none d-md-inline fw-bold small"><?= htmlspecialchars($userName) ?></span>
                </a> 
                
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow border-0 mt-2"> 
                    <li class="user-header text-bg-primary"> 
                        <div class="d-flex flex-column align-items-center justify-content-center h-100">
                             <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center mb-2 shadow" style="width: 60px; height: 60px;">
                                <i class="bi bi-person-fill fs-1"></i>
                            </div>
                            <p class="fs-5 fw-bold mb-0"><?= htmlspecialchars($userName) ?></p>
                            <small class="opacity-75"><?= htmlspecialchars($userEmail) ?></small>
                        </div>
                    </li> 
                    
                    <li class="user-footer bg-body-secondary d-flex justify-content-between p-3"> 
                        <a href="../profile/" class="btn btn-outline-secondary btn-sm">Profile</a> 
                        <a href="../auth/logout.php" class="btn btn-danger btn-sm">Sign out</a> 
                    </li> 
                </ul> 
            </li> 
        </ul> 
    </div> 
</nav>