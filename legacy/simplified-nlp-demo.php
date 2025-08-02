<?php
require_once 'includes/config.php';
// Skip login requirement for debugging
// requireLogin();

$user = isset($_SESSION['username']) ? ['name' => $_SESSION['username']] : ['name' => 'Debug User'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simplified NLP Demo - POINTMARKET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <main class="col-12 px-4">
                <div class="container">
                    
                    <!-- Header -->
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-brain me-2"></i>
                            Demo NLP Analysis (Simplified)
                        </h1>
                    </div>

                    <!-- Test Content -->
                    <div class="alert alert-info">
                        <h5>Test Content</h5>
                        <p>If you can see this, the page is rendering correctly.</p>
                    </div>
                    
                    <!-- Demo Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-edit me-2"></i>Test Form</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label for="test-text" class="form-label">Write something:</label>
                                    <textarea id="test-text" class="form-control" rows="3"></textarea>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="alert('Test button works!')">
                                    Test Button
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
