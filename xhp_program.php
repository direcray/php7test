<?php
    include "config.inc.php";
    include "db.inc.php";
    use LFH\SQL;
?>
<!doctype html>
<html lang="zh-TW">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>XHP 222 銷售獎金</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        $('#table1').DataTable( {
            "paging": false
        } );
    } );
    </script>
    <link type="text/css" rel="stylesheet"  href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" />
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
            <th> 7月獎金</th>
            <th> 8月獎金</th>
            <th> 9月獎金</th>
            <th>10月獎金</th>
            <th>11月獎金</th>
            <th>12月獎金</th>
            <th>總計獎金</th>
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
        for ($j = 1; $j <= $this_month; $j ++) {
            $sql2->str = "select `GP`, `MONTH_DISCOUNT`, `SPON_DEDUCTION`, `TGP` from `NBM_DAILY_HISTORY` where `NBM_DATE`='". $mth_ends[$j]. "' and `EMDN`='". $sql1->row->EMDN. "'";
            $sql2->query();
            $sql2->fetch_object();
            $tmp_gp += $sql2->row->GP;
            $tmp_tgp += $sql2->row->TGP;
            $tmp_discount += $sql2->row->MONTH_DISCOUNT;
            $tmp_deduction += $sql2->row->SPON_DEDUCTION;
            $month_gp[$j] = $sql2->row->GP - $sql2->row->MONTH_DISCOUNT - $sql2->row->SPON_DEDUCTION;
        }
        for (; $j <= 12; $j ++) {
            $tmp_tgp += $sql2->row->TGP;
        }
        
        $accumulated_gp = 0;
        $has_exceed = 0;
        for ($j = 1; $j <= $this_month; $j ++) {
            $accumulated_gp += $month_gp[$j];
            if ($accumulated_gp > $tmp_tgp) {
                if (!$has_exceed) { // 處理超過的那個月
                    $exceed_month = $j;
                    $has_exceed = 1;
                    $month_gpi[$j] = $accumulated_gp - $tmp_tgp;
                } else {
                    $month_gpi[$j] = $month_gp[$j];
                }
            } else {
                $month_gpi[$j] = 0;
            }
        }
        $tmp_real_gp = $tmp_gp - $tmp_discount - $tmp_deduction;
        $tmp_ratio = ($tmp_tgp == 0) ? 0 : round($tmp_real_gp * 100 / $tmp_tgp, 2);
        $total_tgp += $tmp_tgp;
        $total_gp += $tmp_real_gp;
        $tmp_gpi =  ($tmp_real_gp > $tmp_tgp) ? $tmp_real_gp - $tmp_tgp : 0;
        $tmp_bonus = $tmp_gpi * $month_percentage[$exceed_month];
        $i ++;
?>
        <tr>
            <td><?=$i?></td>
            <td><?=$sql1->row->EMDN?></td>
            <td><?=$sql1->row->NAME?></td>
            <td align="right"><?=number_format($month_gpi[7])?></td>
            <td align="right"><?=number_format($month_gpi[8])?></td>
            <td align="right"><?=number_format($month_gpi[9])?></td>
            <td align="right"><?=number_format($month_gpi[10])?></td>
            <td align="right"><?=number_format($month_gpi[11])?></td>
            <td align="right"><?=number_format($month_gpi[12])?></td>
            <td align="right"><?=number_format($tmp_bonus)?></td>
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
            <th align="right"><?=number_format(0)?></th>
            <th align="right"><?=number_format(0)?></th>
            <th align="right"><?=number_format(0)?></th>
            <th align="right"><?=number_format(0)?></th>
            <th align="right"><?=number_format(0)?></th>
            <th align="right"><?=number_format(0)?></th>
            <th></th>
    </tfoot>
    </table>
    </div>
</body>
</html>