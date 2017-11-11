<?php
    include "config.inc.php";
    include "db.inc.php";
    use LFH\SQL;
?>
<!doctype html>
<html lang="zh-TW">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>時間軸獎勵（早鳥獎勵）統計</title>
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
            <th>目標毛利</th>
            <th>達成毛利</th>
            <th>達成比例</th>
            <th>增加毛利 8月</th>
            <th>增加毛利 9月</th>
            <th>增加毛利10月</th>
            <th>增加毛利11月</th>
            <th>增加毛利12月</th>
            <th>達標獎金</th>
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
            /*
            if ($sql1->row->EMDN == "6170") {
                echo "month: $j : ". $mth_ends[$j];
                echo " 1. ". $sql2->row->GP;
                echo ", 2. ". $sql2->row->MONTH_DISCOUNT;
                echo ", 3. ". $sql2->row->SPON_DEDUCTION;
                echo ", 4. ". $sql2->row->TGP;
                echo "<br>\n";
            }
            */
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
            <td align="right"><?=number_format($tmp_tgp)?></td>
            <td align="right"><?=number_format($tmp_real_gp)?></td>
            <td align="right"><?=$tmp_ratio?>%</td>
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
            <th align="right"><?=number_format($total_tgp)?></th>
            <th align="right"><?=number_format($total_gp)?></th>
            <th align="right"><?=round($total_gp * 100 / $total_tgp, 2)?>%</th>
            <th align="right"><?=number_format(0)?></th>
            <th align="right"><?=number_format(0)?></th>
            <th align="right"><?=number_format(0)?></th>
            <th align="right"><?=number_format(0)?></th>
            <th align="right"><?=number_format(0)?></th>
            <th></th>
    </tfoot>
    </table>
    <br>
    <h4>時間軸獎勵（早鳥獎勵)</h4>
    <ul>
        <li>超越去年毛利（GPL）即可領取獎金。總體達成再加倍。</li>
        <li>早鳥優惠：鼓勵提早超越去年毛利額（GPL）</li>
        <ul>
            <li>超額(GPL)越多，領越多。</li>
            <ul>
                <li>所有超過去年毛利的金額(GPI)，都可以領一定比例。</li>
            </ul>
            <li>越早超額(GPL)領越多。</li>
            <ul>
                <li>10月往前，每提早一個月，多領10%，最高上限30%。</li>
            </ul>
            <li>NBM總計超越去年毛利15%，達標(GPT)的業務獎金加倍。</li>
        </ul>
        <li>簡而言之，超過去年毛利的部分開始抽啪，每早一個月多抽10%。全體NBM達去年115%，獎金再加倍。</li>
    </ul>
    </div>
</body>
</html>
