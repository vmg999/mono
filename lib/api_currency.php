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
        $this->api = SETTINGS['URLS']['api'];
        $this->pub_get = SETTINGS['URLS']['getcurrency'];
    }

    public function get_currency()
    {
        if(@$_SESSION['get_currency_time']==null or (time()-$_SESSION['get_currency_time'])>60) {
        $this->currency = json_decode(file_get_contents($this->api . $this->pub_get));
            $_SESSION['get_currency_time'] = time();
            $_SESSION['currency']=$this->currency;
        }else{
            $this->currency=$_SESSION['currency'];
        }

        // Добавление в объект расшифровки кодов валют
        $t_size = count($this->currency);
        $z = new get_iso4217_list();

        for ($ii = 0; $ii < $t_size; $ii++) {
            $codeA = $this->currency[$ii]->currencyCodeA;
            $codeB = $this->currency[$ii]->currencyCodeB;
            $c_iA = $z->get_cur_by_code($codeA);
            $c_iB = $z->get_cur_by_code($codeB);
            $this->currency[$ii]->currencyAname = (string)$c_iA['CurrencyAbbr'];
            $this->currency[$ii]->currencyBname = (string)$c_iB['CurrencyAbbr'];
            $this->currency[$ii]->currencyAfullName = (string)$c_iA['CurrencyName'];
            $this->currency[$ii]->currencyBfullName = (string)$c_iB['CurrencyName'];
            $this->currency[$ii]->countryAname = (string)$c_iA['CountryName'];
            $this->currency[$ii]->countryBname = (string)$c_iB['CountryName'];
        }
        return $this->currency;
    }
}


