<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .error-container {
            text-align: center;
        }
        .error-code {
            font-size: 10rem;
            font-weight: bold;
            color: #6c757d;
        }
        .error-message {
            font-size: 1.5rem;
            color: #343a40;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-message"><?php echo htmlspecialchars($message ?? 'Page Not Found'); ?></div>
        <a href="/dashboard" class="btn btn-primary mt-4">Go to Dashboard</a>
    </div>
</body>
</html>
