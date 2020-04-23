<?php //скопировать с именем config.php
define("SETTINGS", array(
    'token' => "",
    'db_host' => 'localhost',
    'db_user' => '',
    'db_psw' => '',
    'db_name' => '',
    'db_table_template' => 'monobank_transactions_',
    'default_table' => 'monobank_transactions_black',
    'available_period' => 2682000,
    'URLS' => [
        'iso4217' => "https://www.currency-iso.org/dam/downloads/lists/list_one.xml",
        'api' => "https://api.monobank.ua",
        'pers' => "/personal/client-info",
        'state' => "/personal/statement/",
        'getcurrency' => "/bank/currency"
    ]

));

define('CARDS_ORDER', array(0 => '', 1 => '', 2 => '')); //last 4 digits