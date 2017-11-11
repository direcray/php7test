<?php
    include "config.inc.php";
    include "db.inc.php";
    use LFH\SQL;
?>
<!doctype html>
<html lang="zh-TW">
<head>
    <title>Test</title>
</head>
<body>
<?php
    $sql1 = new SQL();
    $sql1->str = "select `NAME` from `EMD`";
    $sql1->query();
    while ($sql1->fetch_object()) {
        echo $sql1->row->NAME;
        echo "<br>";
    }
?>
</body>
</html>
