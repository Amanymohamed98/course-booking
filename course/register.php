<?php 
include 'config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // التحقق من عدم وجود حساب بنفس البريد باستخدام MySQLi
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if($stmt->num_rows > 0) {
        $error = "هذا البريد الإلكتروني مسجل بالفعل";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();
        
        $_SESSION['user_id'] = $stmt->insert_id;
        header("Location: dashboard.php");
        exit();
    }
}

include 'includes/header.php'; 
?>

<main class="auth-form">
    <h2>إنشاء حساب جديد</h2>
    <?php if(isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="name">الاسم الكامل:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">كلمة المرور:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">إنشاء الحساب</button>
    </form>
    <p>لديك حساب بالفعل؟ <a href="login.php">سجل الدخول</a></p>
</main>

<?php include 'includes/footer.php'; ?>