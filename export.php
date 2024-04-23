<?php
$cookieName = "name-file-db";
if (isset($_COOKIE[$cookieName])) {
    $cookieValue = $_COOKIE[$cookieName];
}

$database = new SQLite3('AllFileDB/'.$cookieValue.'.db');

if (isset($_GET['export'])) {
    $sort_export = $_GET['export'];

    if ($sort_export == "json") {
        // استعلام لاستخراج اللغات المتاحة
        $query = "SELECT DISTINCT language FROM translations";
        $result = $database->query($query);

        // Array لتخزين الترجمات لكل اللغات
        $all_translations = array();

        // Loop لاستخراج الترجمات لكل لغة
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $language = $row['language'];

            // استعلام لاستخراج الترجمات باللغة المحددة
            $queryTranslations = "SELECT * FROM translations WHERE language = '$language'";
            $resultTranslations = $database->query($queryTranslations);

            // Array لتخزين الترجمات للغة المحددة
            $translations = array();

            // حلقة لجمع الترجمات بحسب اللغة
            while ($rowTranslation = $resultTranslations->fetchArray(SQLITE3_ASSOC)) {
                $translation_key = $rowTranslation['original_text'];
                $translation_value = $rowTranslation['translation'];

                // إضافة الترجمة إلى المصفوفة
                $translations[$translation_key] = $translation_value;
            }

            // إضافة الترجمات للغة المحددة إلى المصفوفة الكبيرة
            $all_translations[$language] = $translations;
        }

        // تحويل الترجمات إلى JSON
        $json_data = json_encode($all_translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // تحديد اسم الملف
        $file_name = 'all_translations.json';

        // Headers لإرسال الملف كـ JSON للتنزيل
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        echo $json_data;
    }

    if ($sort_export == "csv") {
        // استعلام لاستخراج اللغات المتاحة
        $query = "SELECT DISTINCT language FROM translations";
        $result = $database->query($query);

        // Loop لاستخراج الترجمات لكل لغة
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $language = $row['language'];

            // استعلام لاستخراج الترجمات باللغة المحددة
            $queryTranslations = "SELECT * FROM translations WHERE language = '$language'";
            $resultTranslations = $database->query($queryTranslations);

            // مصفوفة لتخزين البيانات المستردة
            $data = array();

            // جلب العناوين (أسماء الأعمدة)
            $column_names = array();
            if ($resultTranslations->numColumns() > 0) {
                for ($i = 0; $i < $resultTranslations->numColumns(); $i++) {
                    $column_names[] = $resultTranslations->columnName($i);
                }
                $data[] = $column_names;
            }

            // Loop لإضافة البيانات إلى المصفوفة
            while ($rowTranslation = $resultTranslations->fetchArray(SQLITE3_ASSOC)) {
                $data[] = array_values($rowTranslation);
            }

            // تحديد اسم الملف
            $file_name = 'translations_' . $language . '.csv';

            // فتح ملف CSV للكتابة
            $file = fopen($file_name, 'w');

            // كتابة البيانات إلى الملف CSV
            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            // إغلاق الملف
            fclose($file);

            // تحديد Headers لإرسال الملف كـ CSV للتنزيل
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            readfile($file_name);

            // حذف الملف بعد التنزيل
            unlink($file_name);
        }
    }
}
?>
