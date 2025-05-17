<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Kullanıcı';

// Dosya yükleme işlemi
$uploadMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $allowedTypes = ['application/pdf', 'image/png', 'image/jpeg'];
    $file = $_FILES['file'];

    if ($file['error'] === 0 && in_array($file['type'], $allowedTypes)) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filename = time() . '_' . basename($file['name']);
        $filePath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Dosya bilgilerini veritabanına kaydet
            $stmt = $pdo->prepare("INSERT INTO files (user_id, filename, original_name, file_type,file_size, uploaded_at) VALUES (?, ?, ?,?, ?, NOW())");
            $stmt->execute([$userId, $filename, $file['name'], $file['type'], $file['size']]);
            $uploadMessage = '<div class="alert alert-success">Dosya başarıyla yüklendi!</div>';
        } else {
            $uploadMessage = '<div class="alert alert-danger">Dosya yüklenirken hata oluştu.</div>';
        }
    } else {
        $uploadMessage = '<div class="alert alert-warning">Sadece PDF, PNG veya JPG dosyalarına izin verilir.</div>';
    }
}

// Dosya silme
if (isset($_GET['delete'])) {
    $fileId = intval($_GET['delete']);
    $stmt = $pdo->prepare("SELECT filename FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$fileId, $userId]);
    $file = $stmt->fetch();

    if ($file) {
        $filePath = __DIR__ . '/uploads/' . $file['filename'];
        if (file_exists($filePath)) unlink($filePath);

        $pdo->prepare("DELETE FROM files WHERE id = ?")->execute([$fileId]);
        header("Location: dashboard.php");
        exit;
    }
}

// Kullanıcının yüklediği dosyalar
$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$userId]);
$files = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kontrol Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f2f6fc;
        }
        .header {
            background: linear-gradient(to right, #007bff, #00c6ff);
            padding: 2rem;
            color: white;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .upload-box {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        .table-container {
            margin-top: 2rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header text-center">
        <h2>Hoş geldiniz, <?= htmlspecialchars($username) ?>!</h2>
        <p>Dosya yükleyin, yönetin ve silin.</p>
        <a href="logout.php" class="btn btn-outline-light mt-2">Çıkış Yap</a>
    </div>

    <div class="upload-box mt-4">
        <h5>📤 Dosya Yükle</h5>
        <?= $uploadMessage ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <input type="file" name="file" accept=".pdf,.png,.jpg,.jpeg" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Yükle</button>
        </form>
    </div>

    <div class="table-container">
        <h5 class="mt-5">📁 Yüklenen Dosyalar</h5>
        <?php if (count($files) > 0): ?>
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>Dosya Adı</th>
                    <th>Tür</th>
                    <th>Tarih</th>
                    <th>İndir</th>
                    <th>Sil</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($files as $file): ?>
                <tr>
                    <td><?= htmlspecialchars($file['original_name']) ?></td>
                    <td><?= htmlspecialchars($file['file_type']) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($file['uploaded_at'])) ?></td>
                    <td><a class="btn btn-sm btn-success" href="uploads/<?= urlencode($file['filename']) ?>" target="_blank">Görüntüle</a></td>
                    <td><a class="btn btn-sm btn-danger" href="?delete=<?= $file['id'] ?>" onclick="return confirm('Bu dosyayı silmek istediğinize emin misiniz?')">Sil</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="alert alert-info">Henüz hiç dosya yüklemediniz.</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>