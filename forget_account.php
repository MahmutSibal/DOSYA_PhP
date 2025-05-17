<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['reset_attempts'])) {
    $_SESSION['reset_attempts'] = 0;
}

$step = 'verify'; // İlk adım: doğrulama
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND email = ?");
        $stmt->execute([$username, $email]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['reset_user_id'] = $user['id'];
            $step = 'reset'; // Doğrulama başarılı, şifre sıfırlama adımına geç
            $_SESSION['reset_attempts'] = 0; // Sayaç sıfırlanır
        } else {
            $error = "Kullanıcı adı veya e-posta yanlış!";
            $_SESSION['reset_attempts'] += 1;

            if ($_SESSION['reset_attempts'] >= 3) {
                header("Location: index.php");
                exit;
            }
        }
    }

    if (isset($_POST['reset_password'])) {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword === $confirmPassword && isset($_SESSION['reset_user_id'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $_SESSION['reset_user_id']]);

            unset($_SESSION['reset_user_id']);
            $_SESSION['reset_attempts'] = 0;
            $success = "Şifre başarıyla güncellendi. Giriş yapabilirsiniz.";
            $step = 'done';
        } else {
            $error = "Şifreler eşleşmiyor!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifre Sıfırlama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
        }
        .link {
            text-decoration: none;
            color: #000;
        }
    </style>
</head>
<body>
<div class="card col-md-5">
    <h3 class="text-center mb-4">Şifre Sıfırlama</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <div class="text-center">
            <a href="index.php" class="btn btn-success mt-2">Giriş Sayfasına Dön</a>
        </div>
    <?php elseif ($step === 'verify'): ?>
        <form method="POST">
            <input type="hidden" name="verify" value="1">
            <div class="mb-3">
                <label class="form-label">Kullanıcı Adı</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">E-posta</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Devam Et</button>
        </form>
    <?php elseif ($step === 'reset'): ?>
        <form method="POST">
            <input type="hidden" name="reset_password" value="1">
            <div class="mb-3">
                <label class="form-label">Yeni Şifre</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Yeni Şifre (Tekrar)</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Şifreyi Güncelle</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
