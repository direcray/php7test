<?php
    include "config.inc.php";
    include "db.inc.php";
    use LFH\SQL;
?>
<!doctype html>
<html lang="zh-TW">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>新交客戶數統計</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <!--<script type="text/javascript" src="https://cdn.datatables.net/r/bs-3.3.5/jqc-1.11.3,dt-1.10.8/datatables.min.js"></script>-->
    <script type="text/javascript">
    $(document).ready(function() {
        $('#table1').DataTable( {
            "paging": false
        } );
        //$('#table1').removeClass( 'display' )
        //.addClass('table table-striped table-bordered');
    } );
    </script>
    <link type="text/css" rel="stylesheet"  href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" />
    <!--<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/bs-3.3.5/jq-2.1.4,dt-1.10.8/datatables.min.css"/>-->
    <style>
    #container1 { margin: 50px; font-size: 0.9em; }
    #table1 { width: 1000px; }
    </style>
</head>
<body>
<?php
    $mth = date('Ym');
    $prev_mth = $mth - 1;
    if (substr($prev_mth, 4, 2) == "00") $prev_mth -= 88; //一月的前一個月是去年的十二月 
    $this_month = date('m');
    for ($i = 1; $i <= 12; $i ++) {
        $mth_ends[$i] = sprintf("2017-%02d-%02d", $i, date("t", mktime(0, 0, 0, $i, 1, 2017))); //取得每個月的最後一天 
        if ($i == $this_month) $mth_ends[$i] = $mth_ends[$i] = sprintf("2017-%02d-%02d", $i, date('d')); //只有這個月取今天
    }
    $month_percentage[1] = 0.3;
    $month_percentage[2] = 0.3;
    $month_percentage[3] = 0.3;
    $month_percentage[4] = 0.3;
    $month_percentage[5] = 0.3;
    $month_percentage[6] = 0.3;
    $month_percentage[7] = 0.3;
    $month_percentage[8] = 0.3;
    $month_percentage[9] = 0.2;
    $month_percentage[10] = 0.1;
    $month_percentage[11] = 0;
    $month_percentage[12] = 0;
    
    $sql1 = new SQL();
    $sql2 = new SQL();
    $sql1->str = "select * from `SRV` where `MTH`='$mth' and `CPT`='NBM'";
    $sql1->query();
    if ($sql1->num_rows < 1) {
	$sql1->str = "select * from `SRV` where `MTH`='$prev_mth' and `CPT`='NBM'";
	$sql1->query();
    }
        
?>
    <div id="container1">
    <table id="table1" class="display compact">
    <thead>
        <tr>
            <th></th>
            <th>員工編號</th>
            <th>姓名</th>
<?php
    for ($i = 1; $i <= 12; $i ++) {
?>
            <th><?=$i?>月</th>
<?php
    }
?>
            <th>總計</th>
        </tr>
    </thead>
    <tbody>
<?php
    $i = 0;
    $total_gp = 0;
    $total_tgp = 0;
    while ($sql1->fetch_object()) {
        $tmp_gp = 0;
        $tmp_tgp = 0;
        $tmp_discount = 0;
        $tmp_deduction = 0;
        //for ($j = 1; $j <= date('m'); $j ++) {
        $annual_ncd = 0;
        for ($j = 1; $j <= $this_month; $j ++) {
            $sql2->str = "select `NCD`, `GP`, `MONTH_DISCOUNT`, `SPON_DEDUCTION`, `TGP` from `NBM_DAILY_HISTORY` where `NBM_DATE`='". $mth_ends[$j]. "' and `EMDN`='". $sql1->row->EMDN. "'";
            $sql2->query();
            $sql2->fetch_object();
            $month_ncd[$j] = $sql2->row->NCD;
            $total_ncd[$j] += $sql2->row->NCD;
            $annual_ncd += $sql2->row->NCD;
        }
        $i ++;
?>
        <tr>
            <td><?=$i?></td>
            <td><?=$sql1->row->EMDN?></td>
            <td><?=$sql1->row->NAME?></td>
<?php
        for ($k = 1; $k <= 12; $k ++) {
?>
            <td align="right"><?=number_format($month_ncd[$k])?></td>
<?php
        }
?>
            <td align="right"><?=number_format($annual_ncd)?></td>
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
<?php
    for ($i = 1; $i <= 12; $i ++) {
?>
            <th align="right"><?=$total_ncd[$i]?></th>
<?php
    }
?>
            <th></th>
    </tfoot>
    </table>
    <br>
    </div>
</body>
</html>
