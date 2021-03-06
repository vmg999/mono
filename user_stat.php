<?php
require_once 'config.php';
require_once 'lib/db.php';
require_once 'lib/api_user_stat.php';

/**
 * Class user_stat
 * Получение транзакций
 */
class user_stat
{
    private $db;
    private $api_user_stat;
    private $db_table_template;
    private $default_table;
    private $account_id;
    private $cards_order;

    public $table;
    public $account;
    public $user_info;
    public $saved_transactions;
    public $statistics_by_transactnions;

    public function __construct()
    {
        $this->db = new db();
        $this->api_user_stat = new api_user_stat();

        $this->db_table_template = SETTINGS['db_table_template'];
        $this->default_table = SETTINGS['default_table'];
        $this->cards_order = CARDS_ORDER;

        $this->set_account();
    }

    /**
     * Получение информации о пользователе
     */
    public function get_user_info()
    {
        $this->user_info = $this->api_user_stat->get_pers_info();

        // Сортировка карт в заданном в настройках порядке
        $tmp_accounts = array();
        foreach ($this->cards_order as $card) {
            foreach ($this->user_info->accounts as $account) {
                $last4dig = (int)substr($account->maskedPan[0], -4);
                if ($card == $last4dig) {
                    $tmp_accounts[$card] = $account;
                }
            }
        }
        $this->user_info->accounts = $tmp_accounts;

        return $this->user_info;
    }

    /**
     * Выбор аккаунта
     */
    public function set_account($account = "black")
    {
        if (!$this->user_info) {
            $this->get_user_info();
        }

        foreach ($this->user_info->accounts as $a) {
            if ($a->type == $account) {
                $this->account = $account;
                $this->account_id = $a->id;
                $this->table = "$this->db_table_template" . "$account";
                break;
            } else {
                $this->table = $this->default_table;
            }
        }
    }

    /**
     * Получение сохраненных транзакций
     * @param $table
     * @param string $last
     * @return mixed
     */
    public function get_saved_transactions($last = 'all')
    {
        $this->saved_transactions = $this->db->get_transactions($this->table, $last);
        return $this->saved_transactions;
    }

    /**
     * Сравнение сохраненных в БД и полученных по API транзакций и вычисление новых
     */
    public function get_new_transactions()
    {
        $new_transactions = array();
        $db_data = $this->db->get_transactions($this->table);
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
        $new_transactions = $this->get_new_transactions();
        return $this->db->save_transactions($this->table, $new_transactions);
    }

    /**
     * Вычисление различной статистики
     */
    public function get_statistics_by_transactnions()
    {
        if (!$this->saved_transactions) {
            $this->get_saved_transactions();
        }

        $quantity = count($this->saved_transactions);
        $transactions = $this->saved_transactions;
        $commission = 0;
        $total_cashback = 0;
        $cashback_out = 0;
        $minus = 0;
        $plus = 0;
        $mn_bal = array();
        $percents = 0;


        for ($i = 0; $i < $quantity; $i++) {
            $year = (int)date("Y", ($transactions[$i]['time'] + 2 * 3600));
            $mnth = (int)date("m", ($transactions[$i]['time'] + 2 * 3600));
            if ($transactions[$i]['amount'] >= 0) {
                $plus += $transactions[$i]['amount'];
                @$mn_bal["$year"]["$mnth"]['pl'] += $transactions[$i]['amount'];
            } else {
                $minus += $transactions[$i]['amount'];
                @$mn_bal["$year"]["$mnth"]['mns'] += $transactions[$i]['amount'];
            }
            $commission += $transactions[$i]['commissionRate']; //комиссия
            $total_cashback += $transactions[$i]['cashbackAmount']; //общий кешбек

            //выведенный кешбек
            $cshb = array();
            if (preg_match("/Виведення кешбеку/", $transactions[$i]['description'])) {
                preg_match("/\d+\.\d{2}/", $transactions[$i]['description'], $cshb);
                $cashback_out += (float)$cshb[0];
            }
            //проценты--------------------------------------------------
            if (preg_match("/Начисление процентов/", $transactions[$i]['description']) or
                preg_match("/ідсотк/", $transactions[$i]['description'])) {
                $percents += $transactions[$i]['amount'];
            }

        }
        //средний закрытый месяц (без текущего)-----------------------------------
        $mnttl = 0;
        foreach ($mn_bal as $y) {
            $mnttl += count($y);
        }
        $last = @end(end($mn_bal));
        array_key_exists('pl', $last) ? $last_plus = $last['pl'] : $last_plus = 0;
        array_key_exists('mns', $last) ? $last_minus = $last['mns'] : $last_minus = 0;

        $average_mnt_plus = (int)floor(($plus - $last_plus) / ($mnttl - 1));
        @$average_mnt_minus = (int)floor(($minus - $last_minus) / ($mnttl - 1));
        //---------------------------------------------------------------------
        $cashback = $total_cashback - ((int)($cashback_out * 100));

        $this->statistics_by_transactnions = compact('quantity', 'commission', 'total_cashback',
            'cashback', 'percents', 'plus', 'minus',
            'average_mnt_plus', 'average_mnt_minus', 'mn_bal');
        return $this->statistics_by_transactnions;
    }

    /**
     * Подсчет суммы транзакций по описанию
     * @param string $description
     * @return float|int
     */
    public function get_sum_by_descriptionn(string $description = '.+')
    {
        if (!$this->saved_transactions) {
            $this->get_saved_transactions();
        }
        $sum = 0;
        $quantity = count($this->saved_transactions);
        $transactions = $this->saved_transactions;
        $pattern = "/" . $description . "/";

        for ($i = 0; $i < $quantity; $i++) {
            if (preg_match($pattern, $transactions[$i]['description'])) {
                $sum += $transactions[$i]['amount'];
            }
        }
        return $sum / 100;

    }

}