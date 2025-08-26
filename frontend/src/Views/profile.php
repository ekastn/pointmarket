<?php
// Unified profile view for all roles
$user = $user ?? ['name' => 'Guest', 'email' => 'N/A', 'username' => 'N/A', 'role' => 'guest', 'avatar' => null, 'bio' => null];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-user-circle me-2"></i>Profil Saya</h1>
  </div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <img src="<?php echo htmlspecialchars($user['avatar'] ?? '/assets/img/default-avatar.png'); ?>" class="rounded-circle mb-3" alt="Avatar" style="width: 150px; height: 150px; object-fit: cover;">
                <h5 class="card-title mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                <p class="text-muted mb-0"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
                <p class="text-muted"><small>@<?php echo htmlspecialchars($user['username']); ?></small></p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Edit Profil</h5>
            </div>
            <div class="card-body">
                <form action="/profile" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="avatar" class="form-label">URL Avatar</label>
                        <input type="text" class="form-control" id="avatar" name="avatar" value="<?php echo htmlspecialchars($user['avatar'] ?? ''); ?>">
                        <div class="form-text">Tautan ke foto profil Anda (mis. Gravatar atau layanan gambar).</div>
                    </div>
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Ubah Kata Sandi</h5>
            </div>
            <div class="card-body">
                <form action="/profile/password" method="POST" onsubmit="return validatePasswordForm()">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Kata Sandi Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Kata Sandi Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" required>
                        <div class="form-text">Gunakan minimal 8 karakter dengan huruf dan angka.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-key me-2"></i>Ubah Kata Sandi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function validatePasswordForm() {
    const np = document.getElementById('new_password').value;
    const cp = document.getElementById('confirm_password').value;
    if (np !== cp) {
        alert('Kata sandi baru dan konfirmasi tidak cocok.');
        return false;
    }
    return true;
}
</script>
