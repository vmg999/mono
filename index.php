<?php
require_once 'user_stat.php';
require_once 'lib/api_currency.php';
session_start();
date_default_timezone_set('Europe/Kiev');

//курсы
$cur = new get_cur;
$currency = $cur->get_currency();
$curval=new get_iso4217_list();
//

$ustat = new user_stat();
$ustat->get_user_info();

if ($_GET != null) {
    $ustat->set_account($_GET['account']);
    $db_answer = $ustat->db_save_new_transaction();
}

$transactions = $ustat->get_saved_transactions();
$size = count($transactions);

$ustat->get_statistics_by_transactnions();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mono</title>
    <link rel="icon" href="img/favicon-mono.ico">
    <link rel="stylesheet" href="css/main.css">
</head>

<body>
<a href="index.php"><h1>Monobank INFO</h1></a>
<div class="currency">
    <table class="currency_tbl">
        <?php
        for ($i = 0; $i < 4; $i++) {
            echo "<tr>";
            echo "<td>" . date("d.m", $currency[$i]->date) . " </td>";
            echo "<td>" . date("H:i", $currency[$i]->date) . " </td>";
            echo "<td>  " . $currency[$i]->currencyAname . " / ";
            echo $currency[$i]->currencyBname . "  </td>";
            echo "<td>" . $currency[$i]->rateBuy . " / ".$currency[$i]->rateSell . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>
<div id="hh">
    <div id="balance">

        <?php
        foreach ($ustat->user_info->accounts as $account) {
            echo "<p><b>Баланс <abbr title='".$account->maskedPan[0]."'>".ucfirst($account->type)."</abbr>: ";
            echo "<span class='blnc'>".($account->balance / 100)." </span></b><span class='smalltxt'>";
            $curname=$curval->get_cur_by_code($account->currencyCode);
            echo $curname['CurrencyAbbr'];
            if($account->currencyCode != 840) {
                echo ' (' . round(($account->balance / 100) / $currency[0]->rateSell, 2) . ' $)';
            }
            echo "</span></p>";
        }
        ?>


    </div>

    <div class="status">
        <p><b>
                <?php if (isset($db_answer)) {
                    echo (time()-$_SESSION['get_pers_info_time'])."s  ";
                    echo $db_answer;
                } ?>
            </b>
        </p>
    </div>


</div>
<br><br><br>
<div class="transact">
    <div class="upd">
        <div class="upd"><h2>Транзакции по карте: </h2></div>
        <div class="upd">
            <a href="/?account=black"><button name="account">Black</button></a>
        </div>
        <div class="upd">
            <a href="/?account=white"><button name="account">White</button></a>
        </div>

    </div>
    <table class="table-tr">
        <div class="tblhead">
            <thead>
            <th>Номер</th>
            <th>Дата</th>
            <th>Время</th>
            <th>Описание</th>
            <th>Сумма</th>
            <!--<th>mcc</th>-->
            <th>Кэшбэк</th>
            <!--<th>Комиссия</th>-->
            <th>Баланс</th>
            </thead>
        </div>
        <?php
        for ($i = $size - 1; $i >= ($size - 10); $i--) {
            @extract($transactions[$i]);
            echo "<tr><td>" . $auto_id . "</td>";
            echo "<td>" . date("d.m.Y", ($time)) . "</td>";
            echo "<td>" . date("H:i:s", ($time)) . "</td>";
            echo "<td>" . $description . "</td>";
            if ($amount >= 0) {
                echo "<td align='right' style='color: green'>";
            } else {
                echo "<td align='right' style='color: red'>";
            }
            echo ($amount / 100) . "</td>";
            //echo "<td>" . $mcc . "</td>";
            echo "<td>" . ($cashbackAmount / 100) . "</td>";
            //echo "<td>" . ($commissionRate / 100) . "</td>";
            echo "<td><b>" . ($balance / 100) . "</b></td></tr>";
        }
        ?>

    </table>
</div>

<div class="stat">
    <h2>Stat</h2>
    <table class="table-tr">

        <th>Год</th>
        <th>Месяц</th>
        <th>Приход</th>
        <th>Расход</th>
        <?php
        extract($ustat->statistics_by_transactnions);

        echo "<tr><td></td><td>Всего: </td><td>" . ($plus / 100) . "</td><td>" . ($minus / 100) . "</td></tr>";
        echo "<tr><td></td><td>Средний</td><td>" . floor($average_mnt_plus / 100) . "</td><td>" . floor($average_mnt_minus / 100) . "</td></tr>";
        foreach ($mn_bal as $yr => $mn) {
            foreach ($mn as $mnth => $mnbal) {
                echo "<tr><td>" . $yr . "</td><td>" . $mnth . "</td><td>" . (@$mnbal['pl'] / 100) . "</td><td>" . (@$mnbal['mns'] / 100) . "</td></tr>";
            }
        }
        ?>
    </table>

</div>

<div class="stat">
    <h2>Stat</h2>
    <table class="table-tr">
        <?php
        echo "<tr><td>Транзакций</td><td>" . $quantity . "</td></tr>";
        echo "<tr><td>Комиссия</td><td>" . ($commission / 100) . "</td></tr>";
        echo "<tr><td>Кэшбек</td><td>" . ($cashback / 100) . "</td></tr>";
        echo "<tr><td>Весь Кэшбек</td><td>" . ($total_cashback / 100) . "</td></tr>";
        echo "<tr><td>Проценты</td><td>" . ($percents / 100) . "</td></tr>";
        ?>
    </table>
</div>
</body>
</html>