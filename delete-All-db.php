<?php
$folderPath = 'AllFileDB';

// قائمة بجميع الملفات في المجلد
$files = glob($folderPath . '*');

// إذا كان عدد الملفات أكثر من 30، قم بحذفها
if (count($files) > 30) {
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}
?>
