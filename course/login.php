<?php 
include 'config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة";
    }
}

include 'includes/header.php'; 
?>

<main class="auth-form">
    <h2>تسجيل الدخول</h2>
    <?php if(isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">كلمة المرور:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">تسجيل الدخول</button>
    </form>
    <p>ليس لديك حساب؟ <a href="register.php">سجل الآن</a></p>
</main>

<?php include 'includes/footer.php'; ?>