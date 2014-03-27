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
            $param = unserialize($param);

        try{

            if (defined("CORE_API_URL"))
            {
                switch($event->event)
                {
                    case 'add_super_client': SyncCore::AddSuperClient($param); break;

                    case 'add_account': 
                    case 'client_set_status': SyncCore::AddAccount($param); break;

                    //case 'contact_add_email': SyncCore::AddEmail($param);break;
                    case 'password_changed': SyncCore::updateAdminPassword($param);break;
                    case 'admin_changed': SyncCore::adminChanged($param); break;

                    case 'usage_vpbx__insert':
                    case 'usage_vpbx__update':
                    case 'usage_vpbx__delete':  SyncCore::checkProductState('vpbx', $param); break;

                    case 'usage_voip__insert':
                    case 'usage_voip__update':
                    case 'usage_voip__delete':  SyncCore::checkProductState('phone', $param); break;
                }
            }

            switch($event->event)
            {
                case 'company_changed':     EventHandler::companyChanged($param); break;

                case 'cyberplat_payment':   $clientId = $param["client_id"]; 
                                            EventHandler::updateBalance($clientId); break;
                case 'usage_voip__insert':
                case 'usage_voip__update':
                case 'usage_voip__delete':  ats2Numbers::check(); break;

                case 'midnight': voipNumbers::check(); /* проверка необходимости включить или выключить услугу */
                                 ats2Numbers::check();
                                 virtPbx::check();
                                 break;

                case 'autocreate_accounts': ats2Numbers::autocreateAccounts($param); break;
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
