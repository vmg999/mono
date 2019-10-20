<?php
    require_once 'user_stat.php';
    $ustat=new user_stat();

    if($_POST!=null){
        if ($_POST['Getnew']==='Update'){
            $res=$ustat->db_save_new_transaction();
            //header("location:index.php");
        }
    }

    $transaction=$ustat->db_get();
    $size=count($transaction);
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
            <p ><b>Текущий баланс:
                <span class="blnc">
                    <?php echo ($transaction[$size-1]['balance']/100);?>
                </span>
             </b></p>
        </div>

        <div class="status">
            <p><b>
                <?php if(isset($res)){echo $res;}?>
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

                <th>Номер</th>
                <th>Дата</th>
                <th>Время</th>
                <th>Описание</th>
                <th>MCC</th>
                <th>Сумма</th>
            <th>Комиссия</th>
            <th>Баланс</th>

            <?php
                for ($i=$size-1;$i>=0;$i--){
                    echo "<tr><td>".$transaction[$i]['auto_id']."</td>";
                    echo "<td>".date("d.m.Y",($transaction[$i]['time']+10800))."</td>";
                    echo "<td>".date("H:i:s",($transaction[$i]['time']+10800))."</td>";
                    echo "<td>".$transaction[$i]['description']."</td>";
                    echo "<td>".$transaction[$i]['mcc']."</td>";
                    if ($transaction[$i]['amount']>=0) {
                        echo "<td align='right' style='color: green'>";
                    }
                    else{
                        echo "<td align='right' style='color: red'>";
                    }
                    echo ($transaction[$i]['amount'] / 100) . "</td>";
                    echo "<td>".(($transaction[$i]['commissionRate'])/100)."</td>";
                    echo "<td><b>".($transaction[$i]['balance']/100)."</b></td></tr>";

                    }
            ?>

        </table>
    </div>

    <div class="stat">
        <h2>Stat</h2>
        <table class="table-tr">

            <th>Месяц</th><th>Приход</th><th>Расход</th>
            <?php
            $comm=0;
            $minus=0;
            $plus=0;
            $mn_bal=['1'=>['pl'=>0, 'mns'=>0],
                '2'=>['pl'=>0, 'mns'=>0],
                '3'=>['pl'=>0, 'mns'=>0],
                '4'=>['pl'=>0, 'mns'=>0],
                '5'=>['pl'=>0, 'mns'=>0],
                '6'=>['pl'=>0, 'mns'=>0],
                '7'=>['pl'=>0, 'mns'=>0],
                '8'=>['pl'=>0, 'mns'=>0],
                '9'=>['pl'=>0, 'mns'=>0],
                '10'=>['pl'=>0, 'mns'=>0],
                '11'=>['pl'=>0, 'mns'=>0],
                '12'=>['pl'=>0, 'mns'=>0]];

            for ($i=0;$i<$size;$i++){
                $mnth=(int)date("m",$transaction[$i]['time']);
                if($transaction[$i]['amount']>=0){
                    $plus+=$transaction[$i]['amount'];
                    $mn_bal["$mnth"]['pl']+=$transaction[$i]['amount'];
                }
                else{
                    $minus+=$transaction[$i]['amount'];
                    $mn_bal["$mnth"]['mns']+=$transaction[$i]['amount'];
                }
               $comm+=$transaction[$i]['commissionRate'];

            }

            echo "<tr><td>Всего: </td><td>".($plus/100)."</td><td>".($minus/100)."</td></tr>";
            foreach ($mn_bal as $mn=>$mnb){
                if($mnb['pl']!=0 or $mnb['mns']!=0){
                    echo "<tr><td>".$mn."</td><td>".($mnb['pl']/100)."</td><td>".($mnb['mns']/100)."</td></tr>";
                }
            }


            ?>
        </table>
        <?php
        echo "Комиссия = ".($comm/100);
        ?>
    </div>
</body>
</html>