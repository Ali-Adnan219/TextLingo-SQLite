<?php
// دالة لاستيراد بيانات من ملف JSON إلى قاعدة البيانات
function importJSONToDatabase($json_file, $database) {
    // قراءة الملف JSON كنص
    $json_data = file_get_contents($json_file);

    // تحويل البيانات من JSON إلى مصفوفة PHP
    $imported_data = json_decode($json_data, true);

    if ($imported_data) {
        foreach ($imported_data as $language => $translations) {
            foreach ($translations as $original_text => $translation) {
                // تجنب الحروف المهملة
                $original_text = $database->escapeString($original_text);
                $translation = $database->escapeString($translation);

                // استعداد الاستعلام
                $query = "INSERT INTO translations (original_text, language, translation) VALUES ('$original_text', '$language', '$translation')";

                // تنفيذ الاستعلام
                $database->exec($query);
            }
        }

        return true; // نجحت العملية
    } else {
        return false; // حدث خطأ في قراءة الملف JSON
    }
}

// مثال على استخدام الدالة
$cookieName = "name-file-db";
if (isset($_COOKIE[$cookieName])) {
    $cookieValue = $_COOKIE[$cookieName];
}

$database = new SQLite3('AllFileDB/'.$cookieValue.'.db');

if (isset($_POST['import_json'])) {
    $json_file = $_FILES['json_file']['tmp_name'];

    // استدعاء الدالة لاستيراد الملف
    $import_result = importJSONToDatabase($json_file, $database);

   
}
?>



<!DOCTYPE html>
<html>

    <script>

 window.location.href = "index.php";


</script>

</html>