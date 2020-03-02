<?php //скопировать с именем config.php
$token="";
$db_host='localhost';
$db_user='';
$db_psw='';
$db_name='';
$db_table_template='monobank_transactions_';
$default_table='monobank_transactions_black';
$iso4217="https://www.currency-iso.org/dam/downloads/lists/list_one.xml";

$available_period=2682000;

// URL's
$api="https://api.monobank.ua";
$pers="/personal/client-info";
$state="/personal/statement/";
$getcurrency="/bank/currency";