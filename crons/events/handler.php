<?php

define("PATH_TO_ROOT", "../../");
include PATH_TO_ROOT."conf.php";
include INCLUDE_PATH."runChecker.php";

echo "\n".date("r").":";


if (runChecker::isRun())
    exit();


    $sleepTime = 3;
    $workTime = 120;

runChecker::run();

$counter = 2;

do{
    do_events();
    sleep($sleepTime);
    echo ".";
}while($counter++ < round($workTime/$sleepTime));

runChecker::stop();
echo "\nstop-".date("r").":";




function do_events()
{
    foreach(EventQueue::getUnhandledEvents() as $event)
    {
        echo "\n".date("r").": event: ".$event->event.", ".$event->param;

        $param = $event->param; 

        if (strpos($param, "a:") === 0)
        {
            $param = unserialize($param);
        }else if (strpos($param, "|") !== false) {
            $param = explode("|", $param);
        }

        try{

            if (defined("CORE_SERVER") && CORE_SERVER)
            {
                switch($event->event)
                {
                    case 'add_super_client': SyncCore::AddSuperClient($param); break;

                    case 'add_account':       SyncCore::AddAccount($param, true);  break;
                    case 'client_set_status': SyncCore::AddAccount($param, false); break;

                    //case 'contact_add_email': SyncCore::AddEmail($param);break;
                    case 'password_changed': SyncCore::updateAdminPassword($param);break;
                    case 'admin_changed': SyncCore::adminChanged($param); break;

                    case 'usage_virtpbx__insert':
                    case 'usage_virtpbx__update':
                    case 'usage_virtpbx__delete': SyncCore::checkProductState('vpbx', $param/*id, client*/); 
                                                  virtPbx::check();
                                                  break; 

                    case 'virtpbx_tarif_changed': SyncVirtPbx::changeTarif($param["client_id"], $param["usage_id"]); break;

                    case 'usage_voip__insert':
                    case 'usage_voip__update':
                    case 'usage_voip__delete':  SyncCore::checkProductState('phone', $param/*id, client*/); break;
                }
            }

            switch($event->event)
            {
                case 'company_changed':     EventHandler::companyChanged($param); break;

                case 'usage_voip__insert':
                case 'usage_voip__update':
                case 'usage_voip__delete':  ats2Numbers::check(); break;

                case 'cyberplat_payment':
                case 'yandex_payment':      $clientId = $param["client_id"]; 
                                            EventHandler::updateBalance($clientId); 
                                            break;

                case 'update_balance': EventHandler::updateBalance($param); break;
                case 'add_payment': LkNotificationContact::createBalanceNotifacation($param[1], $param[0]); break;

                case 'midnight': voipNumbers::check(); /* проверка необходимости включить или выключить услугу */
                                 ats2Numbers::check();
                                 virtPbx::check();
                                 if(date("d") == 11) { //каждого 11-го числа помечаем, что все счета показываем в LK
                                     NewBill::setLkShowForAll();
                                 }
                                 break;

                case 'autocreate_accounts': ats2Numbers::autocreateAccounts($param[0], (bool)$param[1], true); break;
            }
        } catch (Exception $e)
        {
            echo "\n--------------\n";
            echo "[".$event->event."] Code: ".$e->getCode().": ".$e->GetMessage();
            $event->setStoped();
        }
        $event->setHandled();
    }
}
