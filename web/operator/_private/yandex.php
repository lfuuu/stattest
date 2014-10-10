<?php

/*
4268 0337 0354 5624
*/

define("PATH_TO_ROOT", "../../../stat/");
include PATH_TO_ROOT."conf.php";


$_p = array(
        "shopSumBankPaycash" => "1003",
        "requestDatetime" => "2014-05-20T12%3A56%3A52.902%2B04%3A00",
        "merchant_order_id" => "112233_200514125107_00000_15339",
        "customerNumber" => "9130",
        "sumCurrency" => "000",
        "cdd_pan_mask" => "426803%7C5624",
        "shopSumAmount" => "99.39",
        "shopSumCurrencyPaycash" => "10643",
        "ErrorTemplate" => "ym2xmlerror",
        "orderSumAmount" => "103.00",
        "shn" => "mcn.ru,",
        "shopId" => "15339",
        "action" => "checkOrder",
        "shopArticleId" => "108157",
        "orderSumCurrencyPaycash" => "10643",
        "skr_sum" => "103.00",
        "orderSumBankPaycash" => "1003",
        "external_id" => "deposit",
        "invoiceId" => "2000000161791",
        "paymentType" => "AC",
        "cdd_rrn" => "719227890793",
        "orderCreatedDatetime" => "2014-05-20T12%3A56%3A52.646%2B04%3A00",
        "paymentPayerCode" => "4100322062290",
        "rebillingOn" => "false",
        "depositNumber" => "be92b677e9d2a2c9b907ce7903239dbfb20a4624",
        "BuyButton" => "Submit",
        "yandexPaymentId" => "25700929722",
        "skr_env" => "desktop",
        "SuccessTemplate" => "ym2xmlsuccess",
        "cps_region_id" => "213",
        "md5" => "D3C07962C646DBEB569D5A5EF937FE6A",
        "cps-source" => "default",
        "requestid" => "3536363932305f62306261396531313237336532316531663563323764396665383763653337396238343563653963",
        "cdd_auth_code" => "885370",
        "scid" => "51443",
        );

ob_start();
print_r($_POST);

$fp = fopen(LOG_DIR."ecash_yandexmoney.log", "a+");

fwrite($fp, "\n------------------".date("r")."------------\n".ob_get_clean());


ob_start();
$y = new YandexProcessor();
$y->proccessRequest();
fwrite($fp, "\n---------- answer: ----------\n".ob_get_flush());



