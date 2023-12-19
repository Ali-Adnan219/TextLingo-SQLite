<?php
include("delete-All-db.php");
unlink('AllFileDB/'.$_COOKIE["name-file-db"].'.db');

?>


<!DOCTYPE html>
<html>

    <script>

 window.location.href = "index.php";


</script>

</html>