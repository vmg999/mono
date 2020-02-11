<?php
require_once '../config.php';

class db
{
    private $db_host;
    private $db_user;
    private $db_psw;
    private $db_name;
    private $db_table;

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
    }
    /**
     * Получение транзакций из БД
     */
    public function get_transactions()
    {
        $db = new mysqli($this->db_host, $this->db_user, $this->db_psw, $this->db_name);
        $db->set_charset("utf8");

        $query = "SELECT * FROM $this->db_table";
        $res = $db->query($query);
        $db->close();
        return $res->fetch_all(1);

    }
}