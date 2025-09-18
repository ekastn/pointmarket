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

        <!-- Right Side - Register Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center">
            <div class="login-form-container w-100" style="max-width: 420px;">
                <div class="text-center mb-4 d-lg-none">
                    <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                    <h2 class="fw-bold">POINTMARKET</h2>
                </div>

                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <h3 class="card-title text-center mb-4 fw-bold">Daftar Akun Siswa</h3>

                        <?php $messages = $messages ?? []; ?>
                        <?php if (!empty($messages['error'])): ?>
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

                        <form method="POST" action="/register">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-id-card"></i>
                                    </span>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Andi Setiawan" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="andi@example.com" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="andi" required>
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
                                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirm">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Daftar & Masuk
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <small class="text-muted">Sudah punya akun? <a href="/login">Masuk di sini</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const toggle = (inputId, btnId) => {
            const input = document.getElementById(inputId);
            const btn = document.getElementById(btnId);
            btn.addEventListener('click', function () {
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        };
        toggle('password', 'togglePassword');
        toggle('confirm_password', 'toggleConfirm');

        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</div>

