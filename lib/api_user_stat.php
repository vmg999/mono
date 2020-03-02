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
        global $token;
        global $api;
        global $pers;
        global $state;
        global $available_period;

        $this->tok = $token;
        $this->opts = array('http' => array('method' => "GET", 'header' => "X-Token:$this->tok\r\n" . "Cookie: foo=bar\r\n"));
        $this->context = stream_context_create($this->opts);
        $this->api = $api;
        $this->pers = $pers;
        $this->state = $state;
        $this->available_period = $available_period;
    }

    /**
     * Получение информации о юзере (id аккаунтов)
     */

    public function get_pers_info()
    {
        $this->person_info = json_decode(file_get_contents($this->api . $this->pers, false, $this->context));
        return $this->person_info;
    }

    /**
     * Получение всех транзакций за available_period
     */
    public function get_user_statement($account_id)
    {
        $this->user_statement = json_decode(file_get_contents($this->api . $this->state . $account_id . "/" . (time() - $this->available_period) . "/" . (time()), false, $this->context));
        return $this->user_statement;
    }
}
