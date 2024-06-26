<?php



is_dir('AllFileDB') || mkdir('AllFileDB');

function generateRandomName($length = 8) {
    $futureDateTime = strtotime('+1 month');
    return $futureDateTime;
}
//حفظ كوكيز و استرجاعه
$cookieValue="";
$cookieName = "name-file-db";
if (isset($_COOKIE[$cookieName])) {
    $cookieValue = $_COOKIE[$cookieName];

   $databaseFile = 'AllFileDB/'.$cookieValue.'.db';

// تأكد من وجود الملف
if (!file_exists($databaseFile)) {
$cookieValue=generateRandomName();
setcookie($cookieName, $cookieValue, time() + (86400 * 30), "/");
    
}
    
    
} else {
    $cookieValue=generateRandomName();
    setcookie($cookieName, $cookieValue, time() + (86400 * 30), "/");
}


// إنشاء اتصال مع قاعدة بيانات SQLite3
$database = new SQLite3('AllFileDB/'.$cookieValue.'.db');

// التحقق مما إذا كانت قاعدة البيانات موجودة أم لا
if (!$database) {
    die("فشل في الاتصال بقاعدة البيانات.");
}

// إنشاء الجدول إذا لم يكن موجودًا بالفعل
$query = "CREATE TABLE IF NOT EXISTS translations (id INTEGER PRIMARY KEY AUTOINCREMENT, original_text TEXT, language TEXT, translation TEXT)";
$database->exec($query);

// إدخال ترجمة جديدة أو تحديث ترجمة موجودة
if (isset($_POST['save'])) {
    $originalText = $_POST['original_text'];
    $language = $_POST['language'];
    $translation = $_POST['translation'];

    // التحقق من وجود ترجمة موجودة بنفس النص الأصلي ونفس لغة الهدف
    $existingTranslation = $database->querySingle("SELECT translation FROM translations WHERE original_text = '$originalText' AND language = '$language'");

    if ($existingTranslation) {
        // تحديث الترجمة إذا كانت موجودة بالفعل
        $query = "UPDATE translations SET translation = '$translation' WHERE original_text = '$originalText' AND language = '$language'";
    } else {
        // إدخال ترجمة جديدة إذا لم تكن موجودة
        $query = "INSERT INTO translations (original_text, language, translation) VALUES ('$originalText', '$language', '$translation')";
    }

    $database->exec($query);
}

// حذف ترجمة موجودة
if (isset($_GET['delete'])) {
    $translationId = $_GET['delete'];

    // حذف الترجمة باستخدام معرف الترجمة
    $query = "DELETE FROM translations WHERE id = $translationId";
    $database->exec($query);
}

if (isset($_FILES['database_file'])) {
    $file = $_FILES['database_file'];

    // استخراج امتداد الملف
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

    // التحقق من أن الامتداد هو "db"
    if ($extension === 'db') {
        $old_database_path = 'AllFileDB/'.$cookieValue.'.db'; // مسار قاعدة البيانات القديمة
        $new_database_path = 'AllFileDB/'.$cookieValue.'.db'; // مسار قاعدة البيانات الجديدة

        // حذف قاعدة البيانات القديمة إذا كانت موجودة
        if (file_exists($old_database_path)) {
            unlink($old_database_path);
        }

        // حفظ قاعدة البيانات الجديدة
        move_uploaded_file($file['tmp_name'], $new_database_path);

        echo "تم رفع وحفظ قاعدة البيانات بنجاح.";
        include("Refresh.php");
        exit; 
    } else {
        echo "يرجى تحميل ملف قاعدة بيانات صحيح (امتداد الملف يجب أن يكون 'db').";
    }
}

if (isset($_GET['edit'])) {
$editTranslationId = $_GET['edit'];

$queryEditTranslation = "SELECT * FROM translations WHERE id = $editTranslationId";
$resultEditTranslation = $database->querySingle($queryEditTranslation, true);

$originalTextValue = $resultEditTranslation['original_text'];
$languageValue = $resultEditTranslation['language'];
$translationValue = $resultEditTranslation['translation'];

}

$selectedLang = $_GET['lang'] ?? 'all';
$query = ($selectedLang == 'all') ? "SELECT * FROM translations" : "SELECT * FROM translations WHERE language = '$selectedLang'";
$result = $database->query($query);





?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   

  
    <title>إدارة الترجمات</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            text-align: right;
        }

        header {
            background-color: #4caf50;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .container {
            display: flex;
            justify-content: space-between;
            margin: 20px;
        }

        section {
            width: 48%;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #4caf50;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }

        input[type="text"], input[type="file"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"], button {
            background-color: #4caf50;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        th, td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: right;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
        }

        a {
            color: #4caf50;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
        select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 10px;
        }

        option {
            background-color: #fff;
            color: #333;
        }
    </style>
</head>
<body>
    <header>
        <h1>إدارة الترجمات</h1>
    </header>

    
    <div class="container">
        <section>
    <h2>إضافة ترجمة جديدة</h2>
    <form method="post" enctype="multipart/form-data">
        <label for="database_file">قاعدة البيانات الجديدة:</label>
        <input type="file" name="database_file" required>
        <input type="submit" name="savedata" value="حفظ قاعدة البيانات">
    </form>

    <h2>استيراد ملف JSON</h2>
    <form method="post" enctype="multipart/form-data"  action="import.php">
        <input type="file" name="json_file" accept=".json" required>
        <input type="submit" name="import_json" value="JSON استيراد ملف ">
    </form>

    <h2>تصدير البيانات</h2>
    <button onclick="exportToJSON()">JSON تصدير إلى </button>
    <button onclick="exportToCSV()">CSV تصدير إلى </button>

    <h2>إدارة قاعدة البيانات</h2>
    <button onclick="copyTextPython()">نسخ كود كامل python</button>
    <button onclick="copyText()">نسخ كود كامل php</button>
    <button onclick="deleteDatabase()">حذف قاعدة البيانات</button>

    <h2>تنزيل ملف قاعدة البيانات</h2>
    <h4><a href="download.php">اضغط هنا</a></h4>
</section>


        <section>
            <h2>إضافة ترجمة جديدة</h2>
            <form method="post">
            <form method="post">
                <input type="hidden" name="edit_id" value="<?php echo $editTranslationId ?? "" ; ?>">

                <label for="original_text">النص الأصلي:</label>
                <input type="text" name="original_text" value="<?php echo $originalTextValue ?? ""; ?>" required><br><br>

                <label for="language">لغة الترجمة:</label>
                <input type="text" name="language" value="<?php echo $languageValue ?? "" ; ?>" required><br><br>

                <label for="translation">الترجمة:</label>
                <textarea name="translation" required rows="5" style="width: 100%;"><?php echo $translationValue ?? ""; ?></textarea><br><br>

                <input type="submit" name="save" value="حفظ الترجمة">
            </form>
        </div>

    </div>
 
    <!-- قائمة الترجمات المحفوظة -->
<h2>قائمة الترجمات</h2>
    <form method="get" style="text-align: center;">
        <select name="lang" onchange="this.form.submit()">
            <option value="all" <?php echo ($selectedLang == 'all' ? 'selected' : ''); ?>>كل اللغات</option>
            <?php
            // قراءة اللغات المتوفرة من قاعدة البيانات
            $queryLanguages = "SELECT DISTINCT language FROM translations";
            $resultLanguages = $database->query($queryLanguages);

            while ($rowLang = $resultLanguages->fetchArray()) {
                $lang = $rowLang['language'];
                echo "<option value='$lang' " . ($selectedLang == $lang ? 'selected' : '') . ">$lang</option>";
            }
            ?>
        </select>


    </form>


<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <th style="background-color: #f2f2f2; padding: 10px;">النص الأصلي</th>
        <th style="background-color: #f2f2f2; padding: 10px;">لغة الترجمة</th>
        <th style="background-color: #f2f2f2; padding: 10px;">الترجمة</th>
        <th style="background-color: #f2f2f2; padding: 10px;">إجراءات</th>
    </tr>
    <?php while ($row = $result->fetchArray()): ?>
        <tr>
            <td style="padding: 10px;"><?php echo $row['original_text']; ?></td>
            <td style="padding: 10px;"><?php echo $row['language']; ?></td>
            <td style="padding: 10px;"><?php echo nl2br($row['translation']); ?></td>
            <td style="padding: 10px;">
                <a href="?delete=<?php echo $row['id']; ?>" style="color: red;">حذف</a>
                <a href="?edit=<?php echo $row['id']; ?>" style="color: blue;">تعديل</a>
                    
            </td>
        </tr>
    <?php endwhile; ?>
</table>

   
    <script>
        // تأكيد حذف الترجمة
        var deleteLinks = document.querySelectorAll('a[href^="?delete="]');
        for (var i = 0; i < deleteLinks.length; i++) {
            deleteLinks[i].addEventListener('click', function(e) {
                if (!confirm('هل أنت متأكد من رغبتك في حذف هذه الترجمة؟')) {
                    e.preventDefault();
                }
            });
        }

        function copyText() {
        var text =`
function getTranslation($language, $text, $variables = []) {
    // إنشاء اتصال مع قاعدة بيانات SQLite3
    $database = new SQLite3('translations.db');

    // التحقق مما إذا كانت قاعدة البيانات موجودة أم لا
    if (!$database) {
        die("فشل في الاتصال بقاعدة البيانات.");
    }

    // استعلام للبحث عن الترجمة بناءً على اللغة المحددة والنص
    $query = "SELECT translation FROM translations WHERE language = '$language' AND original_text = '$text'";
    $result = $database->query($query);

    // التحقق مما إذا تم العثور على الترجمة باللغة المحددة أم لا
    if ($result->numColumns() > 0) {
        $row = $result->fetchArray();
        $translation = $row['translation'];

        // استبدال المتغيرات في الترجمة إذا كانت متاحة
        if (!empty($variables)) {
            $translation = vsprintf($translation, $variables);
        }

        // إغلاق اتصال قاعدة البيانات
        $database->close();

        // إرجاع الترجمة الموجودة
        return $translation;
    } else {
        // إغلاق اتصال قاعدة البيانات
        $database->close();

        // استعادة الترجمة باللغة الإنجليزية (en) إذا لم تكن متاحة الترجمة باللغة المحددة
        $translation = getTranslation('en', $text, $variables);

        // إرجاع الترجمة الموجودة باللغة الإنجليزية
        return $translation;
    }
}

// استخدام الدالة للحصول على الترجمة مع متغيرات بالنص مباشرة
$a = "ar";
$text = "مرحبًا، %s! كيف حالك اليوم؟";
$variables = ["اسم المستخدم"]; // قائمة المتغيرات
$translation = getTranslation($a, $text, $variables);

// عرض الترجمة
echo "الترجمة: " . $translation . "\n";

`;

        var textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);

        textarea.select();
        document.execCommand('copy');
        
        document.body.removeChild(textarea);
        alert("تم نسخ النص بنجاح!");
    }

        function copyTextPython() {
        var text =`
import sqlite3

def getTranslation(language, text):
    # إنشاء اتصال مع قاعدة بيانات SQLite3
    database = sqlite3.connect('translations.db')

    # التحقق مما إذا كانت قاعدة البيانات موجودة أم لا
    if not database:
        raise Exception("فشل في الاتصال بقاعدة البيانات.")

    # استعلام للبحث عن الترجمة بناءً على اللغة المحددة والنص
    query = f"SELECT translation FROM translations WHERE language = '{language}' AND original_text = '{text}'"
    result = database.execute(query)

    # التحقق مما إذا تم العثور على الترجمة باللغة المحددة أم لا
    if result:
        row = result.fetchone()
        translation = row[0]

        # إغلاق اتصال قاعدة البيانات
        database.close()

        # إرجاع الترجمة الموجودة
        return translation
    else:
        # إغلاق اتصال قاعدة البيانات
        database.close()

        # استعادة الترجمة باللغة الإنجليزية (en) إذا لم تكن متاحة الترجمة باللغة المحددة
        translation = getTranslation('en', text)

        # إرجاع الترجمة الموجودة باللغة الإنجليزية
        return translation

# استخدام الدالة للحصول على الترجمة
a = "ar"
text = "/start"
translation = getTranslation(a, text)

# عرض الترجمة
print("الترجمة:", translation)

`;

        var textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);

        textarea.select();
        document.execCommand('copy');
        
        document.body.removeChild(textarea);
        alert("تم نسخ النص بنجاح!");
    }

        function deleteDatabase() {


            
            var confirmed = confirm("هل أنت متأكد من رغبتك في حذف قاعدة البيانات بالكامل؟");
            if (confirmed) {
            
               
                 alert("تم حذف قاعدة البيانات بنجاح!");
               window.location.href = "deleteDB.php";
            }
        }


       

    function exportToJSON() {
        window.location.href = 'export.php?export=json';
    }


    function exportToCSV() {
        window.location.href = 'export.php?export=csv';
    }


        
    </script>
</body>
</html>
