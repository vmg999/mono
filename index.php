<?php
require_once 'user_stat.php';
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
<div>
    <div id="balance">
        <p><b>Баланс <?php echo ucfirst($ustat->user_info->accounts[0]->type); ?>:
                <span class="blnc">
                    <?php echo($ustat->user_info->accounts[0]->balance / 100); ?>
                </span>
            </b>
        </p>
        <p><b>Баланс <?php echo ucfirst($ustat->user_info->accounts[1]->type); ?>:
                <span class="blnc">
                    <?php echo($ustat->user_info->accounts[1]->balance / 100); ?>
                </span>
            </b>
        </p>
    </div>

    <div class="status">
        <p><b>
                <?php if (isset($db_answer)) {
                    echo $db_answer;
                } ?>
            </b>
        </p>
    </div>
</div>

<div class="transact">
    <div class="upd">
        <div class="upd"><h2>Транзакции по карте: </h2></div>
        <div class="upd">
            <button name="account"><a href="/?account=black">Black</a></button>
        </div>
        <div class="upd">
            <button name="account"><a href="/?account=white">White</a></button>
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
            echo "<td>" . date("d.m.Y", ($time + 2 * 3600)) . "</td>";
            echo "<td>" . date("H:i:s", ($time + 2 * 3600)) . "</td>";
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
        echo "<tr><td>Комиссия</td><td>" . ($commission / 100) . "</td></tr>";
        echo "<tr><td>Кэшбек</td><td>" . ($cashback / 100) . "</td></tr>";
        echo "<tr><td>Весь Кэшбек</td><td>" . ($total_cashback / 100) . "</td></tr>";
        echo "<tr><td>Проценты</td><td>" . ($percents / 100) . "</td></tr>";
        ?>
    </table>
</div>
</body>
</html>