<?php

use app\classes\ActaulizerVoipNumbers;
use app\classes\Event;

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

EventQueue::table()->conn->query("SET @@session.time_zone = '+00:00'");

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
                case 'client_set_status':
                case 'usage_voip__insert':
                case 'usage_voip__update':
                case 'usage_voip__delete': {
                    //ats2Numbers::check();
                    break;
                }

                case 'add_payment': {
                    EventHandler::updateBalance($param[1]);
                    LkNotificationContact::createBalanceNotifacation($param[1], $param[0]);
                    break;
                }

                case 'update_balance': {
                    EventHandler::updateBalance($param);
                    break;
                }

                case 'midnight': {
                    /* проверка необходимости включить или выключить услугу UsageVoip */
                    Event::go('midnight__voip_numbers');

                    /* проверка необходимости включить или выключить услугу UsageVirtPbx */
                    Event::go('midnight__virtpbx3');

                    /* каждый 2-ой рабочий день, помечаем, что все счета показываем в LK */
                    if (WorkDays::isWorkDayFromMonthStart(time(), 2)) {
                        Event::go('midnight__lk_bills4all');
                    }

                    /* за 4 дня предупреждаем о списании абонентки аваносовым клиентам */
                    if (WorkDays::isWorkDayFromMonthEnd(time(), 4)) {
                        Event::go('midnight__monthly_fee_msg');
                    }

                    /* очистка предоплаченных счетов */
                    Event::go('midnight__clean_pre_payed_bills');

                    /* очистка очереди событий */
                    Event::go('midnight__clean_event_queue');

                    break;
                }

                /* проверка необходимости включить или выключить услугу UsageVoip */
                case 'midnight__voip_numbers': {
                    voipNumbers::check();
                    echo "...voipNumbers::check()";
                    break;
                }

                /* проверка необходимости включить или выключить услугу UsageVirtPbx */
                case 'midnight__virtpbx3': {
                    VirtPbx3::check();
                    echo "...VirtPbx3::check()";
                    break;
                }

                /* каждый 2-ой рабочий день, помечаем, что все счета показываем в LK */
                case 'midnight__lk_bills4all': {
                    NewBill::setLkShowForAll();
                    break;
                }

                /* за 4 дня предупреждаем о списании абонентки аваносовым клиентам */
                case 'midnight__monthly_fee_msg': {
                    //$execStr = "cd ".PATH_TO_ROOT."crons/stat/; php -c /etc/ before_billing.php >> /var/log/nispd/cron_before_billing.php";
                    //echo " exec: ".$execStr;
                    //exec($execStr);
                    break;
                }

                /* очистка предоплаченных счетов */
                case 'midnight__clean_pre_payed_bills': {
                    Bill::cleanOldPrePayedBills();
                    echo "... clear prebilled bills";
                    break;
                }

                /* очистка очереди событий */
                case 'midnight__clean_event_queue': {
                    EventQueue::clean();
                    echo "...EventQueue::clean()";
                    break;
                }

            }

            if (isset(\Yii::$app->params['CORE_SERVER']) && \Yii::$app->params['CORE_SERVER'])
            {
                switch($event->event)
                {
                    case 'add_account':
                        SyncCore::addAccount($param, true);
                        break;

                    case 'client_set_status':
                        SyncCore::addAccount($param, false);
                        break;

                    case 'admin_changed':
                        if (is_array($param))
                            $param = $param["account_id"];
                        SyncCore::adminChanged($param);
                        break;

                    case 'usage_virtpbx__insert':
                    case 'usage_virtpbx__update':
                    case 'usage_virtpbx__delete':
                        VirtPbx3::check($param[0]);
                        break;

                    case 'actualize_number':
                        ActaulizerVoipNumbers::me()->actualizeByNumber($param['number']);
                        break;

                    case 'actualize_client':
                        ActaulizerVoipNumbers::me()->actualizeByClientId($param['client_id']);
                        break;

                    case 'update_phone_product':
                        SyncCore::checkProductState('phone', $param['account_id']);
                        break;

                    case 'midnight':
                        ActaulizerVoipNumbers::me()->actualizeAll();
                        break;

                    case 'ats3__sync':
                        ActaulizerVoipNumbers::me()->sync($param["number"]);
                        SyncCore::checkProductState('phone', $param['client_id']);
                        break;
                }
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
