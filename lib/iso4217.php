<?php
require_once 'config.php';

/**
 * Class get_iso4217_list
 * Расшифровка кодов курсов
 */
class get_iso4217_list
{
    public $iso_url;
    public $file = "lib/iso4217table.xml";

    public function __construct()
    {
        global $iso4217;
        $this->iso_url = $iso4217;
    }

    public function get_iso_table()
    {
        $table = file_get_contents($this->iso_url);
        file_put_contents($this->file, $table);

    }

    public function get_cur_by_code($code)
    {
        $cur = simplexml_load_file($this->file);
        $tsize = count($cur->CcyTbl->CcyNtry);

        for ($i = 0; $i <= $tsize; $i++) {
            $nbr = @(int)$cur->CcyTbl->CcyNtry[$i]->CcyNbr;
            if ($nbr == $code) {
                $currency_i['CountryName'] = $cur->CcyTbl->CcyNtry[$i]->CtryNm;
                $currency_i['CurrencyName'] = $cur->CcyTbl->CcyNtry[$i]->CcyNm;
                $currency_i['CurrencyAbbr'] = $cur->CcyTbl->CcyNtry[$i]->Ccy;
                break;
            } else {
                $currency_i['CountryName'] = 'unknown';
                $currency_i['CurrencyName'] = $nbr . ' - unknown';
                $currency_i['CurrencyAbbr'] = 'unknown';
            }
        }
        return $currency_i;
    }

}