<?php
    include "config.inc.php";
    include "db.inc.php";
    use LFH\SQL;
?>
<!doctype html>
<html lang="zh-TW">
<head>
    <title>Mobilgrease XHP 222 促銷獎金</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <!--<script type="text/javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>-->
    <script type="text/javascript" src="https://cdn.datatables.net/r/bs-3.3.5/jqc-1.11.3,dt-1.10.8/datatables.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
<?php
    for ($i = 1; $i <= 28; $i ++) {
?>
        $('#table<?=$i?>').DataTable( {
            "bPaginate": false
        } );
        $('#table<?=$i?>').removeClass( 'display' )
        .addClass('table table-striped table-bordered');
<?php
    }
?>
        $('#summary').DataTable( {
            "bPaginate": false
        } );
        $('#summary').removeClass( 'display' )
        .addClass('table table-striped table-bordered');
    } );
    </script>
    <!--<link type="text/css" rel="stylesheet"  href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css" />-->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/bs-3.3.5/jq-2.1.4,dt-1.10.8/datatables.min.css"/>
    <style>
    #container1 { margin: 50px; font-size: 0.9em; }
    #summary { width: 400px; }
    tfoot th { text-align: right; }
    </style>
</head>
<body>
    <div id="container<?=$j?>">
<?php
    $npn_array = array("12376", "11788");
    $cost_array = array("12376" => 3200, "11788" => 80);
    $sql1 = new SQL();
    $sql2 = new SQL();
    $sql3 = new SQL();

    $sql3->str = "select distinct(`EMDN`) from `SAL` where `DATE`>='2017-07-01' and `DATE`<='2017-09-30' and `NPN` in ('12376', '11788') order by `EMDN`";
    $sql3->query();
    while ($sql3->fetch_object()) {
        if ($sql3->row->EMDN > '5980') $emdn_array[] = $sql3->row->EMDN;
    }
    $i = 0;
    $all_bonus = 0;
    foreach ($emdn_array as $tmp_emdn) {
        $j ++;
        $sql1->str = "select * from `SAL` where `EMDN`='$tmp_emdn' and `DATE`>='2017-07-01' and `DATE`<='2017-09-30' and `NPN` in ('12376', '11788') order by `NPN`";
        $sql1->query();
        $total_qty = 0;
?>
    <table id="table<?=$j?>" class="display">
    <thead>
        <tr>
            <th></th>
            <th>EMDN</th>
            <th>姓名</th>
            <th>日期</th>
            <th>單號</th>
            <th>公司名稱</th>
            <th>NPN</th>
            <th>產品名稱</th>
            <th>付款單號</th>
            <th>數量</th>
            <th>單價</th>
            <th>成本</th>
            <th>差價</th>
            <th>獎金</th>
        </tr>
    </thead>
    <tbody>
<?php
        $i = 0;
        $total_bonus = 0;
        while ($sql1->fetch_object()) {
            $i ++;
            $total_qty += $sql1->row->QTY;
            $sql2->str = "select `NAME` from `EMD` where `EMDN`='". $sql1->row->EMDN. "'";
            $sql2->query();
            $sql2->fetch_object();
            $name_array[$tmp_emdn] = $sql2->row->NAME;
            $price_diff = $sql1->row->UP - $cost_array[$sql1->row->NPN];
            $bonus = $sql1->row->QTY * $price_diff;
            if ($bonus < 0) $bonus = 0;
            if ($bonus > 0 && $sql1->row->PDNO != "") $total_bonus += $bonus; 
?>
        <tr>
            <td><?=$i?></td>
            <td><?=$sql1->row->EMDN?></td>
            <td><?=$sql2->row->NAME?></td>
            <td><?=$sql1->row->DATE?></td>
            <td><?=$sql1->row->DLINO?></td>
            <td><?=mb_substr($sql1->row->COMPANY, 0, 4)?></td>
            <td><?=$sql1->row->NPN?></td>
            <td><?=$sql1->row->ITEM?></td>
            <td><?=$sql1->row->PDNO?></td>
            <td align="right"><?=$sql1->row->QTY?></td>
            <td align="right"><?=$sql1->row->UP?></td>
            <td align="right"><?=$cost_array[$sql1->row->NPN]?></td>
            <td align="right"><?=$price_diff?></td>
            <td align="right"><?=number_format($bonus)?></td>
        </tr>
<?php
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
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th>總計</th>
            <th align="right"><?=number_format($total_bonus)?></th>
    </tfoot>
    </table>
<?php
        $all_bonus += $total_bonus;
        $bonus_array[$tmp_emdn] = $total_bonus;
    }
?>
    <table id="summary">
    <thead>
        <tr>
            <th>EMDN</th>
            <th>姓名</th>
            <th>獎金</th>
        </tr>
    </thead>
    <tbody>
<?php
    foreach ($bonus_array as $emdn => $bonus) {
?>
        <tr>
            <td><?=$emdn?></td>
            <td><?=$name_array[$emdn]?></td>
            <td align="right"><?=number_format($bonus)?></td>
        </tr>
<?php
    }
?>
    </tbody>
    <tfoot>
        <tr>
            <th></th>
            <th align="right">總獎金：</th>
            <th align="right"><?=number_format($all_bonus)?></th>
        </tr>
    </tfoot>
    </table>
    </div>
</body>
</html>
