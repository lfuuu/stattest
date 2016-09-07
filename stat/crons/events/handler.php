<?php

use app\classes\ActaulizerVoipNumbers;
use app\classes\ActaulizerCallChatUsage;
use app\classes\behaviors\uu\SyncAccountTariffLight;
use app\classes\Event;
use app\classes\notification\processors\AddPaymentNotificationProcessor;

define("NO_WEB", 1);
define("PATH_TO_ROOT", "../../");
include PATH_TO_ROOT . "conf_yii.php";
include INCLUDE_PATH . "runChecker.php";

echo "\n" . date("r") . ":";


if (runChecker::isRun()) {
    exit();
}


$sleepTime = 3;
$workTime = 120;

runChecker::run();

$counter = 2;

EventQueue::table()->conn->query("SET @@session.time_zone = '+00:00'");

do {
    do_events();
    sleep($sleepTime);
    echo ".";
} while ($counter++ < round($workTime / $sleepTime));

runChecker::stop();
echo "\nstop-" . date("r") . ":";


function do_events()
{
    foreach (EventQueue::getPlanedEvents() + EventQueue::getPlanedErrorEvents() as $event) {
        $isError = false;
        echo "\n" . date("r") . ": event: " . $event->event . ", " . $event->param;

        $param = $event->param;

        if (strpos($param, "a:") === 0) {
            $param = unserialize($param);
        } else {
            if (strpos($param, "|") !== false) {
                $param = explode("|", $param);
            } else {
                if (strpos($param, "{\"") === 0) {
                    $param = json_decode($param, true);
                }
            }
        }

        Yii::info('Handle event: ' . $event->event . ' ' . json_encode($param,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        try {
            switch ($event->event) {
                case 'client_set_status':
                case 'usage_voip__insert':
                case 'usage_voip__update':
                case 'usage_voip__delete': {
                    //ats2Numbers::check();
                    break;
                }

                case 'actualize_number':
                    \app\models\Number::dao()->actualizeStatusByE164($param['number']);
                    break;

                case 'add_payment': {
                    EventHandler::updateBalance($param[1]);
                    (new AddPaymentNotificationProcessor($param[1], $param[0]))->makeSingleClientNotification();

                    break;
                }

                case 'update_balance': {
                    EventHandler::updateBalance($param);
                    break;
                }

                case 'midnight': {

                    /* проверка необходимости включать или выключать услуги */
                    Event::go('check__usages');

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

                case 'check__usages': {
                    /* проверка необходимости включить или выключить услугу UsageVoip */
                    Event::go('check__voip_old_numbers');

                    /* проверка необходимости включить или выключить услугу в новой схеме */
                    Event::go('check__voip_numbers');

                    /* проверка необходимости включить или выключить услугу UsageVirtPbx */
                    Event::go('check__virtpbx3');

                    /* проверка необходимости включить или выключить улугу UsageCallChat */
                    Event::go('check__call_chat');

                    break;
                }

                /* проверка необходимости включить или выключить услугу UsageVoip */
                case 'check__voip_old_numbers': {
                    voipNumbers::check();
                    echo "...voipNumbers::check()";
                    break;
                }

                /* проверка необходимости включить или выключить услугу UsageVirtPbx */
                case 'check__virtpbx3': {
                    VirtPbx3::check();
                    echo "...VirtPbx3::check()";
                    break;
                }

                /* проверка необходимости включить или выключить услугу UsageCallChat */
                // TODO: перенести в новый демон
                case 'check__call_chat': {
                    ActaulizerCallChatUsage::me()->actualizeUsages();
                    echo "...ActaulizerCallChatUsage::actualizeUsages()";
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

                case 'lk_settings_to_mailer': {
                    \app\models\LkNoticeSetting::sendToMailer($param['client_account_id']);
                    break;
                }

                case 'uu_tarificate': {
                    $clientAccount = \app\models\ClientAccount::findOne(['id' => $param['account_id']]);
                    if ($clientAccount) {
                        \app\models\Bill::dao()->transferUniversalBillsToBills($clientAccount);
                    }
                    break;
                }

                case SyncAccountTariffLight::EVENT_ADD_TO_ACCOUNT_TARIFF_LIGHT:
                    // Добавить данные в AccountTariffLight
                    SyncAccountTariffLight::addToAccountTariffLight($param);
                    break;

                case SyncAccountTariffLight::EVENT_DELETE_FROM_ACCOUNT_TARIFF_LIGHT:
                    // Удалить данные из AccountTariffLight. Теоретически этого быть не должно, но...
                    SyncAccountTariffLight::deleteFromAccountTariffLight($param);
                    break;
            }

            if (isset(\Yii::$app->params['CORE_SERVER']) && \Yii::$app->params['CORE_SERVER']) {
                switch ($event->event) {
                    case 'add_account':
                        SyncCore::addAccount($param, true);
                        break;

                    case 'client_set_status':
                        SyncCore::addAccount($param, false);
                        break;

                    case 'usage_virtpbx__insert':
                    case 'usage_virtpbx__update':
                    case 'usage_virtpbx__delete':
                        VirtPbx3::check();//$param[0]);
                        break;

                    case 'actualize_number':
                        ActaulizerVoipNumbers::me()->actualizeByNumber($param['number']);
                        break;

                    case 'actualize_client':
                        ActaulizerVoipNumbers::me()->actualizeByClientId($param['client_id']);
                        break;

                    case 'update_products':
                        SyncCore::checkProductState('phone', $param['account_id']);
                        break;

                    case 'check__voip_numbers':
                        ActaulizerVoipNumbers::me()->actualizeAll();
                        break;

                    case 'ats3__sync':
                        ActaulizerVoipNumbers::me()->sync($param["number"]);
                        SyncCore::checkProductState('phone', $param['client_id']);
                        break;
                }

                //события услуги звонок_чат
                if (isset(\Yii::$app->params['FEEDBACK_SERVER']) && \Yii::$app->params['FEEDBACK_SERVER']) {
                    switch ($event->event) {
                        case 'call_chat__add':
                        case 'call_chat__update':
                        case 'call_chat__del':
                            ActaulizerCallChatUsage::me()->actualizeUsage($param['usage_id']);
                            break;
                    }
                }
            }

        } catch (Exception $e) {
            echo "\n--------------\n";
            echo "[" . $event->event . "] Code: " . $e->getCode() . ": " . $e->GetMessage() . " in " . $e->getFile() . " +" . $e->getLine();
            $event->setError($e);
            $isError = true;
        }
        if (!$isError) {
            $event->setOk();
        }
    }
}
