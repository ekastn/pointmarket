<!DOCTYPE html>
<html lang="en">
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

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <?php
        // Display flash messages as toasts
        $messages = $_SESSION['messages'] ?? [];
        if (!empty($messages)) {
            foreach ($messages as $type => $message) {
                $toastId = 'toast_' . uniqid();
                $headerClass = $type === 'success' ? 'bg-primary text-white' : 'bg-danger text-white';
                $headerText = $type === 'success' ? 'Success' : 'Error';
                ?>
                <div id="<?= $toastId ?>" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                    <div class="toast-body d-flex align-items-center <?= $headerClass ?>">
                        <strong class="me-auto"><?= htmlspecialchars($message) ?></strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
                <?php
            }
            // Unset the messages so they don't show again on refresh
            unset($_SESSION['messages']);
        }
        ?>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="pt-3">
                <?php include __DIR__.'/../components/sidebar.php'; ?>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="pt-3">
                    <?php echo $content; // This is where the view content will be injected?>
                </div>
            </main>
        </div>
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
    <script>
        // Initialize and show all toasts on page load
        document.addEventListener('DOMContentLoaded', function () {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
            var toastList = toastElList.map(function (toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
                return toast;
            });
        });
    </script>
</body>
</html>
