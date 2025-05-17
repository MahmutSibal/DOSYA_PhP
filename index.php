<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Giriş başarılı
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['login_attempts'] = 0; // Başarılı girişte sayaç sıfırlanır
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "E-posta veya şifre hatalı!";
        $_SESSION['login_attempts'] += 1;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap</title>
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
        .kayit {
            color: black;
            text-decoration: none;
        }
        .kayit:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="card col-md-4">
    <h3 class="text-center mb-4">Giriş Yap</h3>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">E-posta</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Şifre</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>

        <div class="mt-3 text-center">
            <a href="register.php" class="kayit">Hesabınız yok mu? Kayıt olun</a>
        </div>

        <?php if ($_SESSION['login_attempts'] >= 3): ?>
            <div class="mt-2 text-center">
                <a href="forget_account.php" class="kayit text-danger">Şifrenizi mi unuttunuz?</a>
            </div>
        <?php endif; ?>
    </form>
</div>
</body>
</html>
