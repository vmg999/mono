<?php
require_once 'lib/api_currency.php';
$cur = new get_cur;
$currency = $cur->get_currency();
$t = count($currency);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/monobank_currency.css">
    <link rel="icon" href="img/favicon-mono.ico">
    <title>Monobank currency</title>
</head>
<body>
<div class="table">
    <h1>Monobank currency</h1>
    <h2>
        <?php
        date_default_timezone_set('Europe/Kiev');
        echo date("d/m/Y - H:i:s", time());
        ?>
    </h2>
    <table>
        <tr>
            <th></th>
            <th>Дата</th>
            <th>Время</th>
            <th>Валюта А</th>
            <th></th>
            <th>Валюта В</th>
            <th>Buy</th>
            <th>Sell</th>
        </tr>
        <?php
        for ($i = 0; $i < $t; $i++) {
            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td>";
            echo "<td>" . date("d.m.Y", $currency[$i]->date) . "</td>";
            echo "<td>" . date("H:i", $currency[$i]->date) . "</td>";
            echo "<td>" . $currency[$i]->currencyAname . "</td>";
            echo "<td>---></td>";
            echo "<td>" . $currency[$i]->currencyBname . "</td>";
            if (isset($currency[$i]->rateBuy)) {
                echo "<td>" . $currency[$i]->rateBuy . "</td>";
            }
            if (isset($currency[$i]->rateSell)) {
                echo "<td>" . $currency[$i]->rateSell . "</td>";
            } elseif (isset($currency[$i]->rateCross)) {
                echo "<td>" . $currency[$i]->rateCross . "</td><td><-- Cross</td>";
            }
            echo "</tr>";
        }
        ?>
    </table>
</div>
</body>
</html>