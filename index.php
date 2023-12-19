<?php


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





// استعلام عن جميع الترجمات المخزنة
$query = "SELECT * FROM translations";
$result = $database->query($query);

?>

<!DOCTYPE html>
<html>
<head>
    <title>إدارة الترجمات</title>
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            text-align: right; /* تحديد محاذاة النص إلى اليمين */
        }

        h1 {
            color: #333;
        }

        form {
            background-color: #fff;
            border-radius: 5px;
            display: inline-block;
            padding: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"] {
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 5px;
            width: 300px;
        }

        input[type="submit"] {
            background-color: #4caf50;
            border: none;
            border-radius: 3px;
            color: #fff;
            cursor: pointer;
            padding: 10px 20px;
        }

        button {

             background-color: #4caf50;
            border: none;
            border-radius: 3px;
            color: #fff;
            cursor: pointer;
            padding: 10px 20px;
        }

        table {
            background-color: #fff;
            border-collapse: collapse;
            margin-top: 20px;
            width: 100%;
        }

       th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: right; /* تحديد محاذاة النص إلى اليمين */
        }

        th {
            background-color: #f2f2f2;
        }

        a {
            color: #4caf50;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
   <h1>إدارة الترجمات</h1>

    <div style="display: flex;">
        <!-- نموذج تنزيل قاعدة البيانات -->
        <div style="width: 50%;">
            <h2>تنزيل قاعدة البيانات</h2>
            <p><a href="download.php">تنزيل ملف قاعدة البيانات</a></p>
            <pre>        
function getTranslation($language, $text) {
    // إنشاء اتصال مع قاعدة بيانات SQLite3
    $database = new SQLite3('translations.db');
.........
}

// استخدام الدالة للحصول على الترجمة
$a = "ar";
$text = "/start";
$translation = getTranslation($a, $text);

// عرض الترجمة
echo "الترجمة: " . $translation;
            </pre>
            <h2>إضافة ترجمة جديدة</h2>
            <button onclick="copyTextPython()">نسخ كود كامل python</button>
            <button onclick="copyText()">نسخ كود كامل php</button>
            <button onclick="deleteDatabase()">حذف قاعدة البيانات</button>
            <form method="post" enctype="multipart/form-data">
                <label for="database_file">قاعدة البيانات الجديدة:</label>
                <input type="file" name="database_file" required><br><br>
                <input type="submit" name="savedata" value="حفظ قاعدة البيانات">
            </form>
        </div>

         <!-- نموذج إضافة ترجمة جديدة -->
        <div style="width: 50%;">
            <h2>إضافة ترجمة جديدة</h2>
            <form method="post">
                <label for="original_text">النص الأصلي:</label>
                <input type="text" name="original_text" required><br><br>
                <label for="language">لغة الترجمة:</label>
                <input type="text" name="language" required><br><br>
                <label for="translation">الترجمة:</label>
                <textarea name="translation" required rows="5" style="width: 100%;"></textarea><br><br>
                <input type="submit" name="save" value="حفظ الترجمة">
            </form>
        </div>

    </div>
 
    <!-- قائمة الترجمات المحفوظة -->
<h2>قائمة الترجمات</h2>
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
            <td style="padding: 10px;"><?php echo $row['translation']; ?></td>
            <td style="padding: 10px;">
                <a href="?delete=<?php echo $row['id']; ?>" style="color: red;">حذف</a>
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
function getTranslation($language, $text) {
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

        // إغلاق اتصال قاعدة البيانات
        $database->close();

        // إرجاع الترجمة الموجودة
        return $translation;
    } else {
        // إغلاق اتصال قاعدة البيانات
        $database->close();

        // استعادة الترجمة باللغة الإنجليزية (en) إذا لم تكن متاحة الترجمة باللغة المحددة
        $translation = getTranslation('en', $text);

        // إرجاع الترجمة الموجودة باللغة الإنجليزية
        return $translation;
    }
}

// استخدام الدالة للحصول على الترجمة
$a = "ar";
$text = "/start";
$translation = getTranslation($a, $text);

// عرض الترجمة
echo "الترجمة: " . $translation ."\n";

//في حالة تريد اضافة متغيرات 
$n=2;
echo sprintf($translation  ,$n);
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
    </script>
</body>
</html>
