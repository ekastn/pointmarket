<?php
// Data for this view will be passed from the ProfileController
$user = $user ?? ['name' => 'Guest', 'email' => 'N/A', 'username' => 'N/A', 'role' => 'admin', 'avatar' => null];
$messages = $messages ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-user-circle me-2"></i>My Profile</h1>
</div>

<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $type => $message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($type); ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

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
                <h5 class="mb-0">Edit Profile Information</h5>
            </div>
            <div class="card-body">
                <form action="/profile" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Avatar URL</label>
                        <input type="text" class="form-control" id="avatar" name="avatar" value="<?php echo htmlspecialchars($user['avatar'] ?? ''); ?>">
                        <div class="form-text">Link to your profile picture (e.g., from Gravatar or an image hosting service).</div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
