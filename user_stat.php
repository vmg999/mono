<?php
require_once 'config.php';

/**
 * Class user_stat
 * Получение статистики транзакций
 */
class user_stat
{
    private $opts;
    private $context;
    private $tok;
    private $api;
    private $pers;
    private $state;
    private $db_host;
    private $db_user;
    private $db_psw;
    private $db_name;
    private $db_table;
    private $account_id;
    public $person_info;
    public $user_statement;

    public function __construct()
    {
        global $token;
        $this->tok = $token;
        $this->opts = array('http' => array('method' => "GET", 'header' => "X-Token:$this->tok\r\n" . "Cookie: foo=bar\r\n"));
        $this->context = stream_context_create($this->opts);

        global $db_host;
        global $db_user;
        global $db_psw;
        global $db_name;
        global $db_table;
        global $api;
        global $pers;
        global $state;

        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_psw = $db_psw;
        $this->db_name = $db_name;
        $this->db_table = $db_table;
        $this->api = $api;
        $this->pers = $pers;
        $this->state = $state;
    }

    /**
     * Получение информации о юзере (id аккаунта)
     */

    public function get_pers_info()
    {
        $this->person_info = json_decode(file_get_contents($this->api . $this->pers, false, $this->context));
        return $this->person_info;
    }

    /**
     * Получение всех транзакций за месяц
     */
    public function get_user_statement()
    {
        if(!$this->account_id) {
            $this->get_pers_info();
            $this->account_id = $this->person_info->accounts[0]->id;  //id аккаунта
        }
        $this->user_statement = json_decode(file_get_contents($this->api . $this->state . $this->account_id . "/" . (time() - 2682000) . "/" . (time()), false, $this->context));
        return $this->user_statement;
    }

    /**
     * Получение транзакций из БД
     */
    public function db_get()
    {
        $db = new mysqli($this->db_host, $this->db_user, $this->db_psw, $this->db_name);
        $db->set_charset("utf8");

        $query = "SELECT * FROM $this->db_table";
        $res = $db->query($query);
        $db->close();
        return $res->fetch_all(1);

    }

    /**
     * Сравнение сохраненных в БД и полученных транзакций и вычисление новых
     */
    public function get_new_transaction()
    {
        $new_transaction = array();
        $db_data = $this->db_get();
        $us_st = $this->get_user_statement();
        if ($us_st) {
            $sz = count($us_st);
        } else {
            $sz = 0;
        }

        for ($i = $sz - 1; $i >= 0; $i--) {
            $xxx = 0;
            foreach ($db_data as $db_datum) {
                if ($us_st[$i]->id == $db_datum['id']) {
                    $xxx++;
                }
            }
            if ($xxx == 0) {
                $new_transaction[] = $us_st[$i];

            }

        }
        if ($new_transaction != null) {
            return $new_transaction;
        } else {
            return 0;
        }
    }

    /**
     * Запись новых транзакций в БД
     */
    public function db_save_new_transaction()
    {
        $db = new mysqli($this->db_host, $this->db_user, $this->db_psw, $this->db_name);
        $db->set_charset("utf8");
        $new_transaction = $this->get_new_transaction();

        if ($new_transaction != 0) {
            $ntsz = count($new_transaction);
            for ($i = 0; $i < $ntsz; $i++) {

                $id = $new_transaction[$i]->id;
                $time = $new_transaction[$i]->time;
                $description = $new_transaction[$i]->description;
                $mcc = $new_transaction[$i]->mcc;
                $amount = $new_transaction[$i]->amount;
                $operationAmount = $new_transaction[$i]->operationAmount;
                $currencyCode = $new_transaction[$i]->currencyCode;
                $commissionRate = $new_transaction[$i]->commissionRate;
                $cashbackAmount = $new_transaction[$i]->cashbackAmount;
                $balance = $new_transaction[$i]->balance;
                if ($new_transaction[$i]->hold == 1) {
                    $hold = $new_transaction[$i]->hold;
                } else {
                    $hold = 0;
                }

                $query = "INSERT INTO $this->db_table (id, time, description, mcc, amount, 
                          operationAmount, currencyCode, commissionRate, cashbackAmount, balance,
                           hold) VALUES('$id', $time,'$description',$mcc, $amount, $operationAmount,
                            $currencyCode, $commissionRate, $cashbackAmount, $balance, $hold)";

                if ($db->query($query)) {
                    $result = "New transaction added : " . date("d.m - H:i", time() + 3600 * 3);
                } else {
                    $result = "Error";
                }
            }
        } else {
            $result = "No new transaction :  " . date("d.m - H:i", time() + 3600 * 2);
        }
        return $result;

    }
}

