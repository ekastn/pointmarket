<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'POINTMARKET'; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/public/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__.'/../components/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <?php include __DIR__.'/../components/sidebar.php'; ?>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="pt-3">
                    <?php echo $content; // This is where the view content will be injected?>
                </div>
            </main>
        </div>
    </div>

    <div class="toast-container position-static">
        <?php if (!empty($message)): ?>
            <?php foreach ($message as $type => $message): ?>
                <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE_URL = "<?php echo API_BASE_URL; ?>";
        const JWT_TOKEN = "<?php echo $_SESSION['jwt_token'] ?? ''; ?>";
    </script>
    <script src="/public/assets/js/dashboard.js"></script>
    <script src="/public/assets/js/admin-courses.js"></script>
    <script src="/public/assets/js/student-courses.js"></script>
    <script src="/public/assets/js/admin-missions.js"></script>
    <script src="/public/assets/js/student-missions.js"></script>
    <script src="/public/assets/js/admin-badges.js"></script>
</body>
</html>
