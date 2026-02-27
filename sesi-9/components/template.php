<?php
if (!function_exists('renderTemplateStart')) {
    function renderTemplateStart($title = 'Aplikasi', $currentPage = '', $basePath = '')
    {
        if (!is_string($title) || $title === '') {
            $title = 'Aplikasi';
        }

        if (!is_string($currentPage)) {
            $currentPage = '';
        }

        if (!is_string($basePath)) {
            $basePath = '';
        }

        $navbarCurrentPage = $currentPage;
        $navbarBasePath = $basePath;
?>
        <!DOCTYPE html>
        <html lang="id">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($title); ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        </head>

        <body class="bg-light">
            <?php include __DIR__ . '/navbar.php'; ?>
            <main class="py-4">
                <div class="container">
                <?php
            }
        }

        if (!function_exists('renderTemplateEnd')) {
            function renderTemplateEnd()
            {
                ?>
                </div>
            </main>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>

        </html>
<?php
            }
        }
