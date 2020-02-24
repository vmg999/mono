<?php
require_once 'config.php';
require_once 'lib/db.php';
require_once 'lib/api_user_stat.php';


/**
 * Class user_stat
 * Получение статистики транзакций
 */
class user_stat
{
    private $db;
    private $api_user_stat;
    public $user_info;
    public $saved_transactions;


    //account kostyl

    public function __construct()
    {
        $this->db=new db();
        $this->api_user_stat=new api_user_stat();


    }

    /**
     * Получение информации о пользователе
     */
    public function get_user_info(){
        $this->user_info=$this->api_user_stat->get_pers_info();
        return $this->user_info;
    }

    /**
     * Получение сохраненных транзакций
     */
    public function get_saved_transactions($last='all'){
        $this->saved_transactions=$this->db->get_transactions($last);
        return $this->saved_transactions;
    }

    /**
     * Сравнение сохраненных в БД и полученных по API транзакций и вычисление новых
     */
    public function is_there_new_transactions()
    {
        $new_transactions = array();
        $db_data = $this->db->get_transactions();
        $user_bank_stat = $this->api_user_stat->get_user_statement($this->account_id);
        if ($user_bank_stat) {
            $array_size = count($user_bank_stat);
        } else {
            $array_size = 0;
        }

        for ($i = $array_size - 1; $i >= 0; $i--) {
            $xxx = 0;
            foreach ($db_data as $db_datum) {
                if ($user_bank_stat[$i]->id == $db_datum['id']) {
                    $xxx++;
                }
            }
            if ($xxx == 0) {
                $new_transactions[] = $user_bank_stat[$i];
            }

        }
        if ($new_transactions != null) {
            return $new_transactions;
        } else {
            return 0;
        }
    }

    /**
     * Запись новых транзакций в БД
     */
    public function db_save_new_transaction()
    {
        $new_transactions = $this->is_there_new_transactions();
        $resq=$this->db->save_transactions($new_transactions);
        return $resq;
    }

    /**
     * Вычисление различной статистики
     */
    public function get_transactions_statistics(){
        //

    }
}

