<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POINTMARKET - Masuk</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-body">
    <div class="container-fluid h-100">
        <div class="row h-100">
            <!-- Left Side - Branding -->
            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-primary">
                <div class="text-center text-white">
                    <i class="fas fa-graduation-cap fa-5x mb-4"></i>
                    <h1 class="display-4 fw-bold mb-3">POINTMARKET</h1>
                    <p class="lead mb-4">Platform Pembelajaran Adaptif dengan AI</p>
                    <div class="feature-list text-start">
                        <div class="mb-3">
                            <i class="fas fa-brain me-3"></i>
                            <span>Pembelajaran dengan Natural Language Processing</span>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-robot me-3"></i>
                            <span>Rekomendasi Konten dengan AI</span>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-chart-line me-3"></i>
                            <span>Analitik Pembelajaran Real-time</span>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-users me-3"></i>
                            <span>Kolaborasi Multi-role</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="login-form-container w-100" style="max-width: 400px;">
                    <div class="text-center mb-4 d-lg-none">
                        <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                        <h2 class="fw-bold">POINTMARKET</h2>
                    </div>
                    
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <h3 class="card-title text-center mb-4 fw-bold">Masuk ke Akun</h3>
                            
                            <?php
                            require_once 'includes/config.php';
                            
                            $messages = getMessages();
                            if (!empty($messages['error'])): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo htmlspecialchars($messages['error']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($messages['success'])): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo htmlspecialchars($messages['success']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="auth/process_login.php">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Pilih Role</option>
                                        <option value="siswa">Siswa</option>
                                        <option value="guru">Guru</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        Masuk
                                    </button>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <div class="text-center">
                                <h6 class="text-muted mb-3">Demo Login Credentials:</h6>
                                <div class="row text-sm">
                                    <div class="col-12 mb-2">
                                        <strong>Siswa:</strong> andi/password | budi/password
                                    </div>
                                    <div class="col-12 mb-2">
                                        <strong>Guru:</strong> sarah/password | ahmad/password
                                    </div>
                                    <div class="col-12 mb-2">
                                        <strong>Admin:</strong> admin/password
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            © 2025 POINTMARKET. Dibuat untuk Penelitian Fundamental.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
