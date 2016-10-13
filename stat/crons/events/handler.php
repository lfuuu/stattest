<?php

use app\classes\ActaulizerCallChatUsage;
use app\classes\ActaulizerVoipNumbers;
use app\classes\api\ApiVpbx;
use app\classes\behaviors\uu\AccountTariffBiller;
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
    /** @var \EventQueue $event */
    foreach (EventQueue::getPlanedEvents() + EventQueue::getPlanedErrorEvents() as $event) {

        try {
            echo "\n" . date("r") . ": event: " . $event->event . ", " . $event->param;

            // для того, чтобы при фатале на конкретном событии они при следующем запуске не мешало другим событиям
            $event->setError();
            $event->iteration--;

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

            $isCoreServer = (isset(\Yii::$app->params['CORE_SERVER']) && \Yii::$app->params['CORE_SERVER']);
            $isVpbxServer = ApiVpbx::getVpbxHost() && ApiVpbx::getApiUrl();

            switch ($event->event) {
                case Event::USAGE_VOIP__INSERT:
                case Event::USAGE_VOIP__UPDATE:
                case Event::USAGE_VOIP__DELETE: {
                    //ats2Numbers::check();
                    break;
                }

                case Event::ADD_PAYMENT: {
                    EventHandler::updateBalance($param[1]);
                    (new AddPaymentNotificationProcessor($param[1], $param[0]))->makeSingleClientNotification();

                    break;
                }

                case Event::UPDATE_BALANCE: {
                    EventHandler::updateBalance($param);
                    break;
                }

                case Event::MIDNIGHT: {

                    /* проверка необходимости включать или выключать услуги */
                    Event::go(Event::CHECK__USAGES);

                    /* каждый 2-ой рабочий день, помечаем, что все счета показываем в LK */
                    if (WorkDays::isWorkDayFromMonthStart(time(), 2)) {
                        Event::go(Event::MIDNIGHT__LK_BILLS4ALL);
                    }

                    /* за 4 дня предупреждаем о списании абонентки аваносовым клиентам */
                    if (WorkDays::isWorkDayFromMonthEnd(time(), 4)) {
                        Event::go(Event::MIDNIGHT__MONTHLY_FEE_MSG);
                    }

                    /* очистка предоплаченных счетов */
                    Event::go(Event::MIDNIGHT__CLEAN_PRE_PAYED_BILLS);

                    /* очистка очереди событий */
                    Event::go(Event::MIDNIGHT__CLEAN_EVENT_QUEUE);

                    break;
                }

                case Event::CHECK__USAGES: {
                    /* проверка необходимости включить или выключить услугу UsageVoip */
                    Event::go(Event::CHECK__VOIP_OLD_NUMBERS);

                    /* проверка необходимости включить или выключить услугу в новой схеме */
                    Event::go(Event::CHECK__VOIP_NUMBERS);

                    /* проверка необходимости включить или выключить услугу UsageVirtPbx */
                    Event::go(Event::CHECK__VIRTPBX3);

                    /* проверка необходимости включить или выключить улугу UsageCallChat */
                    Event::go(Event::CHECK__CALL_CHAT);

                    break;
                }

                /* проверка необходимости включить или выключить услугу UsageVoip */
                case Event::CHECK__VOIP_OLD_NUMBERS: {
                    voipNumbers::check();
                    echo "...voipNumbers::check()";
                    break;
                }

                /* проверка необходимости включить или выключить услугу UsageVirtPbx */
                case Event::CHECK__VIRTPBX3: {
                    $usageId = isset($param[0]) ? $param[0] : (isset($param['usage_id']) ? $param['usage_id'] : 0);
                    VirtPbx3::check($usageId);
                    echo "...VirtPbx3::check()";
                    break;
                }

                /* проверка необходимости включить или выключить услугу UsageCallChat */
                // TODO: перенести в новый демон
                case Event::CHECK__CALL_CHAT: {
                    ActaulizerCallChatUsage::me()->actualizeUsages();
                    echo "...ActaulizerCallChatUsage::actualizeUsages()";
                    break;
                }

                /* каждый 2-ой рабочий день, помечаем, что все счета показываем в LK */
                case Event::MIDNIGHT__LK_BILLS4ALL: {
                    NewBill::setLkShowForAll();
                    break;
                }

                /* за 4 дня предупреждаем о списании абонентки аваносовым клиентам */
                case Event::MIDNIGHT__MONTHLY_FEE_MSG: {
                    //$execStr = "cd ".PATH_TO_ROOT."crons/stat/; php -c /etc/ before_billing.php >> /var/log/nispd/cron_before_billing.php";
                    //echo " exec: ".$execStr;
                    //exec($execStr);
                    break;
                }

                /* очистка предоплаченных счетов */
                case Event::MIDNIGHT__CLEAN_PRE_PAYED_BILLS: {
                    Bill::cleanOldPrePayedBills();
                    echo "... clear prebilled bills";
                    break;
                }

                /* очистка очереди событий */
                case Event::MIDNIGHT__CLEAN_EVENT_QUEUE: {
                    EventQueue::clean();
                    echo "...EventQueue::clean()";
                    break;
                }

                case Event::LK_SETTINGS_TO_MAILER: {
                    \app\models\LkNoticeSetting::sendToMailer($param['client_account_id']);
                    break;
                }

                case Event::UU_TARIFICATE: {
                    $clientAccount = \app\models\ClientAccount::findOne(['id' => $param['client_account_id']]);
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

                case AccountTariffBiller::EVENT_RECALC:
                    // Билинговать UU-клиента
                    AccountTariffBiller::recalc($param);
                    break;

                case Event::ADD_ACCOUNT:
                    $isCoreServer && SyncCore::addAccount($param, true);
                    break;

                case Event::CLIENT_SET_STATUS:
                    $isCoreServer && SyncCore::addAccount($param, false);
                    break;

                case Event::USAGE_VIRTPBX__INSERT:
                case Event::USAGE_VIRTPBX__UPDATE:
                case Event::USAGE_VIRTPBX__DELETE:
                case Event::UU_ACCOUNT_TARIFF_VPBX:
                    $usageId = isset($param[0]) ? $param[0] : (isset($param['usage_id']) ? $param['usage_id'] : 0);
                    $isCoreServer && VirtPbx3::check($usageId);
                    break;

                case Event::SYNC__VIRTPBX3: {
                    $usageId = isset($param[0]) ? $param[0] : (isset($param['usage_id']) ? $param['usage_id'] : 0);
                    $isCoreServer && VirtPbx3::sync($usageId);
                    break;
                }

                case Event::UU_ACCOUNT_TARIFF_VOIP:
                    $isCoreServer && \app\models\Number::dao()->actualizeStatusByE164($param['number']);
                    $isCoreServer && ActaulizerVoipNumbers::me()->actualizeByNumber($param['number']);
                    break;

                case Event::ACTUALIZE_NUMBER:
                    \app\models\Number::dao()->actualizeStatusByE164($param['number']);
                    $isCoreServer && ActaulizerVoipNumbers::me()->actualizeByNumber($param['number']);
                    break;

                case Event::ACTUALIZE_CLIENT:
                    $isCoreServer && ActaulizerVoipNumbers::me()->actualizeByClientId($param['client_id']);
                    break;

                case Event::UPDATE_PRODUCTS:
                    $isCoreServer && SyncCore::checkProductState('phone', $param['account_id']);
                    break;

                case Event::CHECK__VOIP_NUMBERS:
                    $isCoreServer && ActaulizerVoipNumbers::me()->actualizeAll();
                    break;

                case Event::ATS3__SYNC:
                    $isCoreServer && ActaulizerVoipNumbers::me()->sync($param["number"]);
                    $isCoreServer && SyncCore::checkProductState('phone', $param['client_id']);
                    break;

                case Event::CALL_CHAT__ADD:
                case Event::CALL_CHAT__UPDATE:
                case Event::CALL_CHAT__DEL:
                    // события услуги звонок_чат
                    $isFeedbackServer = (isset(\Yii::$app->params['FEEDBACK_SERVER']) && \Yii::$app->params['FEEDBACK_SERVER']);
                    $isFeedbackServer && ActaulizerCallChatUsage::me()->actualizeUsage($param['usage_id']);
                    break;

                case Event::ACCOUNT_BLOCKED: {
                    $isVpbxServer && ApiVpbx::lockAccount($param['account_id']);
                    break;
                }

                case Event::ACCOUNT_UNBLOCKED: {
                    $isVpbxServer && ApiVpbx::unlockAccount($param['account_id']);
                    break;
                }
            }

            $event->setOk();

        } catch (Exception $e) {
            echo "\n--------------\n";
            echo "[" . $event->event . "] Code: " . $e->getCode() . ": " . $e->getMessage() . " in " . $e->getFile() . " +" . $e->getLine();
            $event->setError($e);
        }
    }
}
