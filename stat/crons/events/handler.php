<?php

use app\classes\ActaulizerVoipNumbers;

define("NO_WEB", 1);
define("PATH_TO_ROOT", "../../");
include PATH_TO_ROOT."conf_yii.php";
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
    foreach(EventQueue::getPlanedEvents() + EventQueue::getPlanedErrorEvents() as $event)
    {
        $isError = false;
        echo "\n".date("r").": event: ".$event->event.", ".$event->param;

        $param = $event->param; 

        if (strpos($param, "a:") === 0)
        {
            $param = unserialize($param);
        }else if (strpos($param, "|") !== false) {
            $param = explode("|", $param);
        }else if (strpos($param, "{\"") === 0) {
            $param = json_decode($param, true);
        }

        Yii::info('Handle event: ' . $event->event . ' ' . json_encode($param, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        try{
            switch($event->event)
            {
                case 'company_changed':     EventHandler::companyChanged($param); break;

                case 'client_set_status':
                case 'usage_voip__insert':
                case 'usage_voip__update':
                case 'usage_voip__delete':  ats2Numbers::check();
                                            break;

                case 'add_payment':    EventHandler::updateBalance($param[1]);
                                       LkNotificationContact::createBalanceNotifacation($param[1], $param[0]); 
                                       break;
                case 'update_balance': EventHandler::updateBalance($param); break;

                case 'midnight': voipNumbers::check();echo "...voipNumbers::check()"; /* проверка необходимости включить или выключить услугу */
                                 ats2Numbers::check();echo "...ats2Numbers::check()";
//                                 virtPbx::check();echo "...virtPbx::check()";
                                 VirtPbx3::check();echo "...VirtPbx3::check()";
                                 if(WorkDays::isWorkDayFromMonthStart(time(), 2)) { //каждый 2-ой рабочий день, помечаем, что все счета показываем в LK
                                     NewBill::setLkShowForAll();
                                 }
                                 if(WorkDays::isWorkDayFromMonthEnd(time(), 4)) { //за 4 дня предупреждаем о списании абонентки аваносовым клиентам
                                     $execStr = "cd ".PATH_TO_ROOT."crons/stat/; php -c /etc/ before_billing.php >> /var/log/nispd/cron_before_billing.php";
                                     echo " exec: ".$execStr;
                                     exec($execStr);
                                 }
                                 Bill::cleanOldPrePayedBills(); echo "... clear prebilled bills";
                                 EventQueue::clean();echo "...EventQueue::clean()";
                                 break;

                case 'autocreate_accounts':
                    ats2Numbers::autocreateAccounts($param[0], (bool)$param[1], true);
                    break;
            }

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
                    case 'usage_virtpbx__delete': 
                    case 'client_set_status':
//                                                  virtPbx::check();
                                                  VirtPbx3::check();
                                                  break; 

                    case 'virtpbx_tarif_changed': SyncVirtPbx::changeTarif($param["client_id"], $param["usage_id"]); break;

                    case 'usage_voip__insert':
                    case 'usage_voip__update':
                    case 'usage_voip__delete':  SyncCore::checkProductState('phone', $param/*id, client*/); break;
                }

//                if (defined("use_ats3"))
//                {
                    switch($event->event)
                    {
                        case 'usage_voip__insert':
                        case 'usage_voip__update':
                        case 'usage_voip__delete': ActaulizerVoipNumbers::me()->actualizeByNumber($param[2]); break;

                        case 'midnight': 
                        case 'client_set_status': ActaulizerVoipNumbers::me()->actualizeAll(); break;

                        case 'ats3__sync': ActaulizerVoipNumbers::me()->sync($param["number"]); break;
                    }
  //              }
            }

        } catch (Exception $e)
        {
            echo "\n--------------\n";
            echo "[".$event->event."] Code: ".$e->getCode().": ".$e->GetMessage()." in ".$e->getFile()." +".$e->getLine();
            $event->setError($e);
            $isError = true;
        }
        if (!$isError)
        {
            $event->setOk();
        }
    }
}
