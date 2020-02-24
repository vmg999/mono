<?php

class db
{
    private $db_host;
    private $db_user;
    private $db_psw;
    private $db_name;
    private $db_table;
    private $database;

    public $res;

    public function __construct()
    {
        global $db_host;
        global $db_user;
        global $db_psw;
        global $db_name;
        global $db_table;

        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_psw = $db_psw;
        $this->db_name = $db_name;
        $this->db_table = $db_table;

        $this->database = new mysqli($this->db_host, $this->db_user, $this->db_psw, $this->db_name);
        $this->database->set_charset("utf8");
    }

    public function __destruct()
    {
        $this->database->close();
    }

    /**
     * Получение транзакций из БД
     */
    public function get_transactions($last = 'all')
    {
        if ((int)$last) {
            $query = "SELECT * FROM $this->db_table ORDER BY auto_id DESC LIMIT $last";
        } else {
            $query = "SELECT * FROM $this->db_table";
        }
        $this->res = $this->database->query($query);
        return $this->res->fetch_all(1);
    }

    /**
     * Запись транзакций в БД
     */
    public function save_transactions($new_transactions)
    {
        if ($new_transactions != 0) {
            $nt_size = count($new_transactions);
            for ($i = 0; $i < $nt_size; $i++) {

                $id = $new_transactions[$i]->id;
                $time = $new_transactions[$i]->time;
                $description = $new_transactions[$i]->description;
                $mcc = $new_transactions[$i]->mcc;
                $amount = $new_transactions[$i]->amount;
                $operationAmount = $new_transactions[$i]->operationAmount;
                $currencyCode = $new_transactions[$i]->currencyCode;
                $commissionRate = $new_transactions[$i]->commissionRate;
                $cashbackAmount = $new_transactions[$i]->cashbackAmount;
                $balance = $new_transactions[$i]->balance;
                if ($new_transactions[$i]->hold == 1) {
                    $hold = $new_transactions[$i]->hold;
                } else {
                    $hold = 0;
                }

                $query = "INSERT INTO $this->db_table (id, time, description, mcc, amount, 
                          operationAmount, currencyCode, commissionRate, cashbackAmount, balance,
                           hold) VALUES('$id', $time,'$description',$mcc, $amount, $operationAmount,
                            $currencyCode, $commissionRate, $cashbackAmount, $balance, $hold)";

                if ($this->database->query($query)) {
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
