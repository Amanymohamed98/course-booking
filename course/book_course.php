<?php
include 'config.php';

// التحقق من تسجيل الدخول
if(!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'يجب تسجيل الدخول أولاً'
    ]);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id'])) {
    $courseId = (int)$_POST['course_id'];
    
    // هنا بدلاً من الحجز الفعلي، نرجع رسالة نجاح فقط
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'تم طلب حجز الدورة بنجاح!)'
    ]);
    exit();
}

// في حالة طلب غير صحيح
header('Content-Type: application/json');
echo json_encode([
    'status' => 'error',
    'message' => 'طلب غير صحيح'
]);
exit();
?>