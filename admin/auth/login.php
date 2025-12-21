<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MyTahfidz Affiliate</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/adminlte/adminlte.min.css">
</head>
<body class="login-page bg-body-secondary">
    <div class="login-box">
        <div class="login-logo">
            <a href="#"><b>MyTahfidz</b> Affiliate</a>
        </div>
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body login-card-body p-4">
                <p class="login-box-msg">Masuk untuk mengelola affiliate Anda</p>
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger text-center small py-2">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <form action="process_login.php" method="post">
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="email" name="email" class="form-control" id="loginEmail" placeholder="Email" required>
                            <label for="loginEmail">Email</label>
                        </div>
                        <div class="input-group-text"><span class="bi bi-envelope"></span></div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="password" name="password" class="form-control" id="loginPassword" placeholder="Password" required>
                            <label for="loginPassword">Password</label>
                        </div>
                        <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Masuk Dashboard</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>