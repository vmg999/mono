<?php
require_once 'iso4217.php';
require_once 'config.php';

/**
 * Class get_cur
 * Получение курсов валют
 */
class get_cur
{
    private $api;
    private $pub_get;
    public $currency;

    public function __construct()
    {
        global $api;
        global $getcurrency;
        $this->api = $api;
        $this->pub_get = $getcurrency;
    }

    public function get_currency()
    {
        $this->currency = json_decode(file_get_contents($this->api . $this->pub_get));

        // Добавление в объект расшифровки кодов валют
        $t_size = count($this->currency);
        $z = new get_iso4217_list();

        for ($ii = 0; $ii < $t_size; $ii++) {
            $codeA = $this->currency[$ii]->currencyCodeA;
            $codeB = $this->currency[$ii]->currencyCodeB;
            $c_iA = $z->get_cur_by_code($codeA);
            $c_iB = $z->get_cur_by_code($codeB);
            $this->currency[$ii]->currencyAname = (string)$c_iA['CurrencyName'];
            $this->currency[$ii]->currencyBname = (string)$c_iB['CurrencyName'];
            $this->currency[$ii]->countryAname = (string)$c_iA['CountryName'];
            $this->currency[$ii]->countryBname = (string)$c_iB['CountryName'];
        }
        return $this->currency;
    }
}


