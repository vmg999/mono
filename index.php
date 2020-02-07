<?php
require_once 'user_stat.php';
$ustat = new user_stat();

if ($_POST != null) {
    if ($_POST['Getnew'] === 'Update') {
        $res = $ustat->db_save_new_transaction();
        //header("location:index.php");
    }
}

$transaction = $ustat->db_get();
$size = count($transaction);

var_dump($ustat->person_info);
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
<div class="upd">
    <div id="balance">
        <p><b>Текущий баланс:
                <span class="blnc">
                    <?php echo($transaction[$size - 1]['balance'] / 100); ?>
                </span>
            </b></p>
    </div>

    <div class="status">
        <p><b>
                <?php if (isset($res)) {
                    echo $res;
                } ?>
            </b>
        </p>
    </div>

    <div class="btn">
        <form action="index.php" method="post">
            <button name="Getnew" value="Update">Получить новые транзакции</button>
        </form>

    </div>
</div>

<div class="transact">
    <h2>Транзакции по карте</h2>
    <table class="table-tr">
        <div class="tblhead">
            <thead>
            <th>Номер</th>
            <th>Дата</th>
            <th>Время</th>
            <th>Описание</th>
            <th>Сумма</th>
            <th>mcc</th>
            <th>Кэшбэк</th>
            <th>Комиссия</th>
            <th>Баланс</th>
            </thead>
        </div>
        <?php
        for ($i = $size - 1; $i >= 0; $i--) {
            echo "<tr><td>" . $transaction[$i]['auto_id'] . "</td>";
            echo "<td>" . date("d.m.Y", ($transaction[$i]['time'] + 2 * 3600)) . "</td>";
            echo "<td>" . date("H:i:s", ($transaction[$i]['time'] + 2 * 3600)) . "</td>";
            echo "<td>" . $transaction[$i]['description'] . "</td>";
            if ($transaction[$i]['amount'] >= 0) {
                echo "<td align='right' style='color: green'>";
            } else {
                echo "<td align='right' style='color: red'>";
            }
            echo ($transaction[$i]['amount'] / 100) . "</td>";
            echo "<td>" . ($transaction[$i]['mcc']) . "</td>";
            echo "<td>" . ($transaction[$i]['cashbackAmount'] / 100) . "</td>";
            echo "<td>" . (($transaction[$i]['commissionRate']) / 100) . "</td>";
            echo "<td><b>" . ($transaction[$i]['balance'] / 100) . "</b></td></tr>";
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
        $comm = 0;
        $cashb = 0;
        $minus = 0;
        $plus = 0;
        $mn_bal = array();

        $cshb_out = 0; //------------------kostyl

        for ($i = 0; $i < $size; $i++) {
            $year = (int)date("Y", ($transaction[$i]['time'] + 2 * 3600));
            $mnth = (int)date("m", ($transaction[$i]['time'] + 2 * 3600));
            if ($transaction[$i]['amount'] >= 0) {
                $plus += $transaction[$i]['amount'];
                @$mn_bal["$year"]["$mnth"]['pl'] += $transaction[$i]['amount'];
            } else {
                $minus += $transaction[$i]['amount'];
                @$mn_bal["$year"]["$mnth"]['mns'] += $transaction[$i]['amount'];
            }
            $comm += $transaction[$i]['commissionRate'];
            $cashb += $transaction[$i]['cashbackAmount'];

            //ttl cshb out--------------------
            $cshb = array();
            if (preg_match("/Виведення кешбеку/", $transaction[$i]['description'])) {
                preg_match("/\d+\.\d{2}/", $transaction[$i]['description'], $cshb);
                $cshb_out += (float)$cshb[0];
            }
            //ttl csh--------------------

        }

        echo "<tr><td></td><td>Всего: </td><td>" . ($plus / 100) . "</td><td>" . ($minus / 100) . "</td></tr>";
        foreach ($mn_bal as $yr => $mn) {
            foreach ($mn as $mnth => $mnbal) {
                echo "<tr><td>" . $yr . "</td><td>" . $mnth . "</td><td>" . (@$mnbal['pl'] / 100) . "</td><td>" . (@$mnbal['mns'] / 100) . "</td></tr>";
            }
        }
        ?>
    </table>
    <?php
    echo "Комиссия = " . ($comm / 100) . "<br>";
    echo "Кэшбэк = " . ($cashb / 100 - $cshb_out) . " (Всего " . ($cashb / 100) . ")<br>";
    ?>
</div>
</body>
</html>