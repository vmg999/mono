<?php

class api_user_stat
{
    private $opts;
    private $context;
    private $tok;
    private $api;
    private $pers;
    private $state;
    private $available_period;

    public $person_info;
    public $user_statement;

    public function __construct()
    {
        $this->tok = SETTINGS['token'];
        $this->api = SETTINGS['URLS']['api'];
        $this->pers = SETTINGS['URLS']['pers'];
        $this->state = SETTINGS['URLS']['state'];
        $this->available_period = SETTINGS['available_period'];

        $this->opts = array('http' => array('method' => "GET", 'header' => "X-Token:$this->tok\r\n" . "Cookie: foo=bar\r\n"));
        $this->context = stream_context_create($this->opts);
    }

    /**
     * Получение информации о юзере (id аккаунтов)
     */

    public function get_pers_info()
    {
        if(@$_SESSION['get_pers_info_time']==null or (time()-$_SESSION['get_pers_info_time'])>60) {
            $this->person_info = json_decode(file_get_contents($this->api . $this->pers, false, $this->context));
            $_SESSION['get_pers_info_time'] = time();
            $_SESSION['pers_info']=$this->person_info;
        }else{
            $this->person_info=$_SESSION['pers_info'];
        }
        return $this->person_info;
    }

    /**
     * Получение всех транзакций за available_period
     */
    public function get_user_statement($account_id)
    {
        if(@$_SESSION[$account_id]['get_user_statement_time']==null or (time()-$_SESSION[$account_id]['get_user_statement_time'])>60) {
            $this->user_statement = json_decode(file_get_contents($this->api . $this->state . $account_id . "/" . (time() - $this->available_period) . "/" . (time()), false, $this->context));
            $_SESSION[$account_id]['get_user_statement_time'] = time();
            $_SESSION[$account_id]['user_statement']=$this->user_statement;
        }else{
            $this->user_statement=$_SESSION[$account_id]['user_statement'];
        }
        return $this->user_statement;
    }
}