<?php
// اسم ملف قاعدة البيانات
$databaseFile = 'AllFileDB/'.$_COOKIE["name-file-db"].'.db';

// تأكد من وجود الملف
if (file_exists($databaseFile)) {
    // تحديد رؤوس الاستجابة للتنزيل
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($databaseFile) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($databaseFile));
    readfile($databaseFile);
    exit;
} else {
    // رسالة الخطأ إذا لم يتم العثور على ملف قاعدة البيانات
    echo 'لم يتم العثور على ملف قاعدة البيانات.';
}
?>
