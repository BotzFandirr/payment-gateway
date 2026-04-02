<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance</title>
    <?php include 'layout/header-meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body {
            background-color: #0f172a; color: white;
            height: 100vh; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            font-family: sans-serif; text-align: center; margin: 0;
        }
        .icon { font-size: 5rem; color: #F59E0B; margin-bottom: 1rem; }
        h1 { margin-bottom: 0.5rem; }
        p { color: #94a3b8; max-width: 400px; line-height: 1.6; }
        .btn {
            margin-top: 2rem; padding: 0.8rem 2rem;
            background: #3B82F6; color: white; text-decoration: none;
            border-radius: 50px; font-weight: bold; transition: 0.3s;
        }
        .btn:hover { background: #2563EB; }
    </style>
</head>
<body>
    <i class="ri-tools-fill icon"></i>
    <h1>Sedang Perbaikan</h1>
    <p>Sistem sedang menjalani pemeliharaan rutin untuk meningkatkan performa. Kami akan segera kembali.</p>
    <a href="/" class="btn">Coba Refresh</a>
</body>
</html>
