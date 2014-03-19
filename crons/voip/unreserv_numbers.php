<?php 
define('PATH_TO_ROOT','../../');
include PATH_TO_ROOT."conf.php";

try {

    $db->Query("
            DELETE FROM 
                log_tarif 
            WHERE 
                id_service IN (
                    SELECT id FROM 
                        `usage_voip` 
                    WHERE 
                        status = 'connecting' AND 
                        actual_from = '2029-01-01' AND 
                        actual_to = '2029-01-01' AND 
                        (TO_DAYS(now()) - TO_DAYS(created)) > 30
                )
            ");

    $db->Query("
            DELETE FROM 
                `usage_voip` 
            WHERE 
                status = 'connecting' AND 
                actual_from = '2029-01-01' AND 
                actual_to = '2029-01-01' AND 
                (TO_DAYS(now()) - TO_DAYS(created)) > 30
            ");

}catch(Exception $e)
{
    echo "\nError: ".$e->GetMessage();
    mail("adima123@yandex.ru", "unreserv voip numbers", $e->GetMessage());
}

?>>