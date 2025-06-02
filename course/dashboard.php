<?php 
include 'config.php';

// التحقق من تسجيل الدخول
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// جلب معلومات المستخدم
$user_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// جلب الدورات المسجلة
$courses_stmt = $conn->prepare("
    SELECT c.title AS name, c.description 
    FROM course_bookings cb
    JOIN courses c ON cb.course_id = c.id 
    WHERE cb.user_id = ? AND cb.status != 'cancelled'
");
$courses_stmt->bind_param("i", $_SESSION['user_id']);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();

$userCourses = [];
while ($row = $courses_result->fetch_assoc()) {
    $userCourses[] = $row;
}
$courses_stmt->close();

// الدورات المتاحة (تم إضافتها مباشرة في الكود بدلاً من قاعدة البيانات)
$availableCourses = [
    [
        'id' => 1,
        'name' => 'دورة تطوير الويب',
        'description' => 'تعلم أساسيات تطوير الويب باستخدام HTML, CSS, JavaScript'
    ],
    [
        'id' => 2,
        'name' => 'دورة برمجة PHP',
        'description' => 'احترف برمجة PHP وإنشاء تطبيقات الويب الديناميكية'
    ],
    [
        'id' => 3,
        'name' => 'دورة قواعد البيانات',
        'description' => 'تعلم تصميم وإدارة قواعد البيانات باستخدام MySQL'
    ],
    [
        'id' => 4,
        'name' => 'دورة الأمن السيبراني',
        'description' => 'أساسيات حماية تطبيقات الويب من الاختراقات'
    ],
    [
        'id' => 5,
        'name' => 'دورة تطوير تطبيقات الجوال',
        'description' => 'تعلم بناء تطبيقات الجوال باستخدام React Native'
    ]
];

// فلترة الدورات المتاحة لإزالة تلك المسجلة مسبقاً
$availableCourses = array_filter($availableCourses, function($course) use ($userCourses) {
    foreach ($userCourses as $registered) {
        if ($registered['name'] === $course['name']) {
            return false;
        }
    }
    return true;
});

include 'includes/header.php'; 
?>

<main>
    <h2>مرحبًا <?php echo htmlspecialchars($user['name']); ?></h2>
    
    <section class="user-courses">
        <h3>الدورات المسجلة</h3>
        <?php if(!empty($userCourses)): ?>
            <div class="course-list">
                <?php foreach($userCourses as $course): ?>
                    <div class="course">
                        <h4><?php echo htmlspecialchars($course['name']); ?></h4>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>ليس لديك أي دورات مسجلة بعد. <a href="index.php">استعرض الدورات</a></p>
        <?php endif; ?>
    </section>
    
    <section class="available-courses">
        <h3>الدورات المتاحة للحجز</h3>
        <div class="course-list">
            <?php if(!empty($availableCourses)): ?>
                <?php foreach($availableCourses as $course): ?>
                    <div class="course">
                        <h4><?php echo htmlspecialchars($course['name']); ?></h4>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                        <form action="book_course.php" method="POST">
                            <input type="hidden" name="course_id" value="<?php echo (int)$course['id']; ?>">
                            <button type="submit" class="btn">حجز الدورة</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>لا توجد دورات متاحة للحجز حالياً.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // إنشاء عنصر لعرض الرسائل
    const messageDiv = document.createElement('div');
    messageDiv.style.position = 'fixed';
    messageDiv.style.top = '20px';
    messageDiv.style.right = '20px';
    messageDiv.style.padding = '15px';
    messageDiv.style.borderRadius = '5px';
    messageDiv.style.color = 'white';
    messageDiv.style.display = 'none';
    messageDiv.style.zIndex = '1000';
    document.body.appendChild(messageDiv);
    
    // التعامل مع جميع نماذج الحجز
    document.querySelectorAll('form[action="book_course.php"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            
            // تعطيل الزر أثناء المعالجة
            button.disabled = true;
            button.textContent = 'جاري الحجز...';
            
            fetch('book_course.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // عرض الرسالة
                messageDiv.textContent = data.message;
                messageDiv.style.display = 'block';
                messageDiv.style.backgroundColor = data.status === 'success' ? '#4CAF50' : '#F44336';
                
                // إخفاء الرسالة بعد 3 ثواني
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 3000);
                
                // إذا نجح الحجز، تحديث الصفحة
                if(data.status === 'success') {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                messageDiv.textContent = 'حدث خطأ في الاتصال بالخادم';
                messageDiv.style.display = 'block';
                messageDiv.style.backgroundColor = '#F44336';
                
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 3000);
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = originalText;
            });
        });
    });
});
</script>