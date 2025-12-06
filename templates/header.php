<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'CVerity AI - Evaluasi CV Berbasis ATS' ?></title>
    <link rel="icon" type="image/png" href="logo.png">
    <script>
        // Base URL untuk API calls
        window.BASE_URL = '<?= rtrim(dirname($_SERVER['PHP_SELF']), '/\\') ?>/';
        if (window.BASE_URL === '//') window.BASE_URL = '/';
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6C5CE7',
                        'primary-dark': '#4B2DFF',
                        'primary-light': '#9B79FF',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="index.php" class="flex items-center space-x-2">
                    <img src="logo.png" alt="CVerity AI Logo" class="w-10 h-10 object-contain">
                    <span class="text-2xl font-bold text-gray-800">CVerity <span class="text-primary">AI</span></span>
                </a>
                <nav class="flex items-center space-x-6">
                    <a href="index.php" class="text-gray-600 hover:text-primary transition">Dashboard</a>
                    <a href="settings.php" class="text-gray-600 hover:text-primary transition">Settings</a>
                </nav>
            </div>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8">
