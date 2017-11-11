<?php
    include "config.inc.php";
    include "db.inc.php";
    use LFH\SQL;
?>
<!doctype html>
<html lang="zh-TW">
<head>
    <title>開洪 I 單統計</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <!--<script type="text/javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>-->
    <script type="text/javascript" src="https://cdn.datatables.net/r/bs-3.3.5/jqc-1.11.3,dt-1.10.8/datatables.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        $('#table1').DataTable();
        $('#table1').removeClass( 'display' )
        .addClass('table table-striped table-bordered');
    } );
    </script>
    <!--<link type="text/css" rel="stylesheet"  href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css" />-->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/bs-3.3.5/jq-2.1.4,dt-1.10.8/datatables.min.css"/>
    <style>
    #container1 { margin: 50px; font-size: 0.9em; }
    </style>
</head>
<body>
<?php
    $sql1 = new SQL();
    $sql2 = new SQL();
    $sql3 = new SQL();
    $sql1->str = "select * from `DS` where `NOTE` like 'I.%'";
    $sql1->query();
    $total_qty = 0;
?>
    <div id="container1">
    <table id="table1" class="display">
    <thead>
        <tr>
            <th></th>
            <th>日期</th>
            <th>單號</th>
            <th>公司名稱</th>
            <th>備註</th>
            <th>產品名稱</th>
            <th>數量</th>
        </tr>
    </thead>
    <tbody>
<?php
    $i = 0;
    while ($sql1->fetch_object()) {
        $sql2->str = "select `NPN`, `ITEM`, `QTY` from `SAL` where `DLINO`='{$sql1->row->DLINO}'";
        $sql2->query();
        while ($sql2->fetch_object()) {
            $i ++;
            $total_qty += $sql2->row->QTY;
?>
        <tr>
            <td><?=$i?></td>
            <td><?=$sql1->row->DATE?></td>
            <td><?=$sql1->row->DLINO?></td>
            <td><?=mb_substr($sql1->row->COMPANY, 0, 4)?></td>
            <td><?=$sql1->row->NOTE?></td>
            <td><?=$sql2->row->ITEM?></td>
            <td align="right"><?=$sql2->row->QTY?></td>
        </tr>
<?php
        }
    }
?>
    </tbody>
    <tfoot>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th>總計</th>
            <th align="right"><?=$total_qty?></th>
    </tfoot>
    </table>
    </div>
</body>
</html>
