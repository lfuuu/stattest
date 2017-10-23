<?php

use app\classes\ActaulizerCallChatUsage;
use app\classes\ActaulizerVoipNumbers;
use app\classes\api\ApiCore;
use app\classes\api\ApiFeedback;
use app\classes\api\ApiPhone;
use app\classes\api\ApiVpbx;
use app\classes\Event;
use app\classes\HandlerLogger;
use app\classes\partners\RewardCalculate;
use app\models\ClientAccount;
use app\models\EventQueueIndicator;
use app\modules\atol\behaviors\SendToOnlineCashRegister;
use app\modules\nnp\classes\CityLinker;
use app\modules\nnp\classes\OperatorLinker;
use app\modules\nnp\classes\RefreshPrefix;
use app\modules\nnp\classes\RegionLinker;
use app\modules\nnp\models\CountryFile;
use app\modules\uu\behaviors\AccountTariffBiller;
use app\modules\uu\behaviors\RecalcRealtimeBalance;
use app\modules\uu\behaviors\SyncAccountTariffLight;
use app\modules\uu\behaviors\SyncVmCollocation;
use app\modules\uu\models\AccountTariff;

define('NO_WEB', 1);
define('PATH_TO_ROOT', '../../');

require PATH_TO_ROOT . 'conf_yii.php';
require INCLUDE_PATH . 'runChecker.php';

echo PHP_EOL . date('r') . ':';

if (runChecker::isRun()) {
    exit();
}

$sleepTime = 3;
$workTime = 120;

runChecker::run();

$counter = 2;

EventQueue::table()->conn->query("SET @@session.time_zone = '+00:00'");

do {
    doEvents();
    sleep($sleepTime);
    echo '.';
} while ($counter++ < round($workTime / $sleepTime));

runChecker::stop();
echo PHP_EOL . 'stop-' . date('r') . ':';

/**
 * @inheritdoc
 */
function doEvents()
{
    /** @var \EventQueue $event */
    foreach ((EventQueue::getPlanedEvents() + EventQueue::getPlanedErrorEvents()) as $event) {
        HandlerLogger::me()->clear();

        $info = '';

        try {
            echo PHP_EOL . date('r') . ': event: ' . $event->event . ', ' . $event->param;

            // для того, чтобы при фатале на конкретном событии они при следующем запуске не мешало другим событиям
            $event->setError();
            $event->iteration--;

            $param = $event->param;

            if (strpos($param, 'a:') === 0) {
                $param = unserialize($param);
            } else {
                if (strpos($param, '|') !== false) {
                    $param = explode('|', $param);
                } else {
                    if (strpos($param, '{"') === 0) {
                        $param = json_decode($param, true);
                    }
                }
            }

            Yii::info(
                'Handle event: ' . $event->event . ' ' .
                json_encode($param, (JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))
            );

            $isCoreServer = (isset(\Yii::$app->params['CORE_SERVER']) && \Yii::$app->params['CORE_SERVER']);
            $isVpbxServer = ApiVpbx::me()->isAvailable();

            switch ($event->event) {
                case Event::USAGE_VOIP__INSERT:
                case Event::USAGE_VOIP__UPDATE:
                case Event::USAGE_VOIP__DELETE: {
                    // ats2Numbers::check();
                    break;
                }

                case Event::ADD_PAYMENT: {
                    EventHandler::updateBalance($param[1]);
                    // (new AddPaymentNotificationProcessor($param[1], $param[0]))->makeSingleClientNotification();
                    break;
                }

                case Event::UPDATE_BALANCE: {
                    EventHandler::updateBalance($param);
                    break;
                }

                case Event::MIDNIGHT: {

                    // проверка необходимости включать или выключать услуги
                    Event::go(Event::CHECK__USAGES);

                    /*
                        // каждый 2-ой рабочий день, помечаем, что все счета показываем в LK
                        if (WorkDays::isWorkDayFromMonthStart(time(), 2)) {
                            Event::go(Event::MIDNIGHT__LK_BILLS4ALL);
                        }
                    */

                    // за 4 дня предупреждаем о списании абонентки аваносовым клиентам
                    if (WorkDays::isWorkDayFromMonthEnd(time(), 4)) {
                        Event::go(Event::MIDNIGHT__MONTHLY_FEE_MSG);
                    }

                    // очистка предоплаченных счетов
                    Event::go(Event::MIDNIGHT__CLEAN_PRE_PAYED_BILLS);

                    // очистка очереди событий
                    Event::go(Event::MIDNIGHT__CLEAN_EVENT_QUEUE);

                    break;
                }

                case Event::CHECK__USAGES: {
                    // проверка необходимости включить или выключить услугу UsageVoip
                    Event::go(Event::CHECK__VOIP_OLD_NUMBERS);

                    // проверка необходимости включить или выключить услугу в новой схеме
                    Event::go(Event::CHECK__VOIP_NUMBERS);

                    // проверка необходимости включить или выключить услугу UsageVirtPbx
                    Event::go(Event::CHECK__VIRTPBX3);

                    // проверка необходимости включить или выключить улугу UsageCallChat
                    Event::go(Event::CHECK__CALL_CHAT);

                    break;
                }

                // проверка необходимости включить или выключить услугу UsageVoip
                case Event::CHECK__VOIP_OLD_NUMBERS: {
                    voipNumbers::check();
                    echo '...voipNumbers::check()';
                    break;
                }

                // проверка необходимости включить или выключить услугу UsageVirtPbx
                case Event::CHECK__VIRTPBX3: {
                    $usageId = isset($param[0]) ? $param[0] : (isset($param['usage_id']) ? $param['usage_id'] : 0);
                    VirtPbx3::check($usageId);
                    echo '...VirtPbx3::check()';
                    break;
                }

                // проверка необходимости включить или выключить услугу UsageCallChat
                // @todo перенести в новый демон
                case Event::CHECK__CALL_CHAT: {
                    ActaulizerCallChatUsage::me()->actualizeUsages();
                    echo '...ActaulizerCallChatUsage::actualizeUsages()';
                    break;
                }

                // каждый 2-ой рабочий день, помечаем, что все счета показываем в LK
                case Event::MIDNIGHT__LK_BILLS4ALL: {
                    NewBill::setLkShowForAll();
                    break;
                }

                // за 4 дня предупреждаем о списании абонентки аваносовым клиентам
                case Event::MIDNIGHT__MONTHLY_FEE_MSG: {
                    // $execStr = "cd ".PATH_TO_ROOT."crons/stat/; php -c /etc/ before_billing.php >> /var/log/nispd/cron_before_billing.php";
                    // echo " exec: ".$execStr;
                    // exec($execStr);
                    break;
                }

                // очистка предоплаченных счетов
                case Event::MIDNIGHT__CLEAN_PRE_PAYED_BILLS: {
                    Bill::cleanOldPrePayedBills();
                    echo '... clear prebilled bills';
                    break;
                }

                // очистка очереди событий
                case Event::MIDNIGHT__CLEAN_EVENT_QUEUE: {
                    EventQueue::clean();
                    echo '...EventQueue::clean()';
                    break;
                }

                case Event::LK_SETTINGS_TO_MAILER: {
                    /** @var \app\modules\notifier\Module $notifier */
                    $notifier = Yii::$app->getModule('notifier');
                    $notifier->actions->applySchemePersonalSubscribe($param);
                    break;
                }

                case Event::CHECK_CREATE_CORE_OWNER:
                    $isCoreServer && ApiCore::checkCreateCoreAdmin($param);
                    break;

                case Event::CORE_CREATE_OWNER:
                    $isCoreServer && ($info = ApiCore::syncCoreOwner($param));
                    break;

                case Event::ADD_ACCOUNT:
                    // Пока ничего не делаем
                    break;

                case Event::USAGE_VIRTPBX__INSERT:
                case Event::USAGE_VIRTPBX__UPDATE:
                case Event::USAGE_VIRTPBX__DELETE:
                    $usageId = isset($param[0]) ? $param[0] : (isset($param['usage_id']) ? $param['usage_id'] : 0);
                    $isCoreServer && VirtPbx3::check($usageId);
                    break;

                case Event::SYNC__VIRTPBX3: {
                    $usageId = isset($param[0]) ? $param[0] : (isset($param['usage_id']) ? $param['usage_id'] : 0);
                    $isCoreServer && VirtPbx3::sync($usageId);
                    break;
                }

                case Event::ACTUALIZE_NUMBER:
                    \app\models\Number::dao()->actualizeStatusByE164($param['number']);
                    $isCoreServer && ActaulizerVoipNumbers::me()->actualizeByNumber($param['number']);
                    break;

                case Event::ACTUALIZE_CLIENT:
                    $isCoreServer && ActaulizerVoipNumbers::me()->actualizeByClientId($param['client_id']);
                    break;

                case Event::CHECK__VOIP_NUMBERS:
                    $isCoreServer && ActaulizerVoipNumbers::me()->actualizeAll();
                    break;

                case Event::ATS3__SYNC:
                    $isCoreServer && ActaulizerVoipNumbers::me()->sync($param['number']);
                    break;

                case Event::CALL_CHAT__ADD:
                case Event::CALL_CHAT__UPDATE:
                case Event::CALL_CHAT__DEL:
                    // события услуги звонок_чат
                    $isFeedbackServer = (isset(\Yii::$app->params['FEEDBACK_SERVER']) && \Yii::$app->params['FEEDBACK_SERVER']);
                    $isFeedbackServer && ActaulizerCallChatUsage::me()->actualizeUsage($param['usage_id']);
                    break;

                case Event::ACCOUNT_BLOCKED:
                    $isVpbxServer && Event::goWithIndicator(
                        Event::VPBX_BLOCKED,
                        $param['account_id'],
                        ClientAccount::tableName(),
                        $param['account_id'],
                        EventQueueIndicator::SECTION_ACCOUNT_BLOCK
                    );
                    (new SyncVmCollocation)->disableAccount($param['account_id']); // Синхронизировать в VM manager
                    break;

                case Event::ACCOUNT_UNBLOCKED:
                    $isVpbxServer && Event::goWithIndicator(
                        Event::VPBX_UNBLOCKED,
                        $param['account_id'],
                        ClientAccount::tableName(),
                        $param['account_id'],
                        EventQueueIndicator::SECTION_ACCOUNT_BLOCK
                    );
                    (new SyncVmCollocation)->enableAccount($param['account_id']); // Синхронизировать в VM manager
                    break;

                case Event::VPBX_BLOCKED:
                    $isVpbxServer && ApiVpbx::me()->lockAccount($param); // Синхронизировать в Vpbx. Блокировка
                    break;

                case Event::VPBX_UNBLOCKED:
                    $isVpbxServer && ApiVpbx::me()->unlockAccount($param); // Синхронизировать в Vpbx. Разблокировка
                    break;

                case Event::PARTNER_REWARD: {
                    RewardCalculate::run($param['client_id'], $param['bill_id'], $param['created_at']);
                    break;
                }

                // --------------------------------------------
                // Универсальные услуги
                // --------------------------------------------
                case \app\modules\uu\Module::EVENT_ADD_LIGHT:
                    // УУ. Добавить данные в AccountTariffLight
                    SyncAccountTariffLight::addToAccountTariffLight($param);
                    break;

                case \app\modules\uu\Module::EVENT_DELETE_LIGHT:
                    // УУ. Удалить данные из AccountTariffLight. Теоретически этого быть не должно, но...
                    SyncAccountTariffLight::deleteFromAccountTariffLight($param);
                    break;

                case \app\modules\uu\Module::EVENT_RECALC_ACCOUNT:
                    // УУ. Билинговать клиента
                    AccountTariffBiller::recalc($param);
                    break;

                case \app\modules\uu\Module::EVENT_RECALC_BALANCE:
                    // УУ. Пересчитать realtime баланс
                    RecalcRealtimeBalance::recalc($param['clientAccountId']);
                    break;

                case \app\modules\uu\Module::EVENT_VM_SYNC:
                    // УУ. Услуга VM collocation
                    (new SyncVmCollocation)->syncVm($param['account_tariff_id']);
                    break;

                case \app\modules\uu\Module::EVENT_VPBX:
                    // УУ. Услуга ВАТС
                    $isCoreServer && VirtPbx3::check($param['account_tariff_id']);
                    break;

                case \app\modules\uu\Module::EVENT_VOIP_CALLS:
                    // УУ. Услуга телефонии
                    \app\models\Number::dao()->actualizeStatusByE164($param['number']);
                    $isCoreServer && ActaulizerVoipNumbers::me()->actualizeByNumber($param['number']);
                    AccountTariff::actualizeDefaultPackages($param['account_tariff_id']);
                    break;

                case \app\modules\uu\Module::EVENT_VOIP_INTERNET:
                    // УУ. Услуга пакет интернет-трафика
                    \app\modules\mtt\Module::addInternetPackage($param['account_tariff_id'], $param['internet_traffic']);
                    break;

                case \app\modules\uu\Module::EVENT_CALL_CHAT:
                    // УУ. Услуга call chat
                    ApiFeedback::createChat($param['account_id'], $param['account_tariff_id']);
                    break;

                case \app\modules\uu\Module::EVENT_RESOURCE_VOIP:
                    // УУ. Отправить измененные ресурсы телефонии на платформу
                    $isCoreServer && ApiPhone::me()->editDid(
                        $param['account_id'],
                        $param['number'],
                        $param['lines'],
                        $param['is_fmc_active'],
                        $param['is_fmc_editable'],
                        $param['is_mobile_outbound_active'],
                        $param['is_mobile_outbound_editable']
                    );
                    break;

                case \app\modules\uu\Module::EVENT_RESOURCE_VPBX:
                    // УУ. Отправить измененные ресурсы ВАТС на платформу
                    $isCoreServer && ApiVpbx::me()->update(
                        $param['account_id'],
                        $param['account_tariff_id'],
                        $regionId = null,
                        ClientAccount::VERSION_BILLER_UNIVERSAL
                    );
                    break;

                // --------------------------------------------
                // АТОЛ
                // --------------------------------------------
                case \app\modules\atol\Module::EVENT_SEND:
                    // АТОЛ. В соответствии с ФЗ−54 отправить данные в онлайн-кассу. А она сама отправит чек покупателю и в налоговую
                    $info = SendToOnlineCashRegister::send($param['paymentId']);
                    break;

                case \app\modules\atol\Module::EVENT_REFRESH:
                    // АТОЛ. Обновить статус из онлайн-кассы
                    $info = SendToOnlineCashRegister::refreshStatus($param['paymentId']);
                    break;

                // --------------------------------------------
                // ННП
                // --------------------------------------------
                case \app\modules\nnp\Module::EVENT_IMPORT:
                    // ННП. Импорт страны
                    $info = CountryFile::importById($param['fileId']);

                    // поставить в очередь для пересчета операторов, регионов и городов
                    Event::go(\app\modules\nnp\Module::EVENT_LINKER);
                    break;

                case \app\modules\nnp\Module::EVENT_LINKER:
                    // ННП. Линковка исходных к ID
                    $info .= 'Операторы: ' . OperatorLinker::me()->run() . PHP_EOL;
                    $info .= 'Регионы: ' . RegionLinker::me()->run() . PHP_EOL;
                    $info .= 'Города: ' . CityLinker::me()->run() . PHP_EOL;
                    break;

                case \app\modules\nnp\Module::EVENT_FILTER_TO_PREFIX:
                    // ННП. Конвертировать фильтры в префиксы
                    $info .= implode(PHP_EOL, RefreshPrefix::me()->filterToPrefix()) . PHP_EOL;
                    break;

                // --------------------------------------------
                // МТТ
                // --------------------------------------------
                case \app\modules\mtt\Module::EVENT_CALLBACK_GET_ACCOUNT_BALANCE:
                    // МТТ. Callback обработчик API-запроса getAccountBalance
                    \app\modules\mtt\Module::getAccountBalanceCallback($param);
                    break;

                case \app\modules\mtt\Module::EVENT_CALLBACK_GET_ACCOUNT_DATA:
                    // МТТ. Callback обработчик API-запроса getAccountData
                    \app\modules\mtt\Module::getAccountDataCallback($param);
                    break;

                case \app\modules\mtt\Module::EVENT_CALLBACK_BALANCE_ADJUSTMENT:
                    // МТТ. Callback обработчик API-запроса balanceAdjustment
                    \app\modules\mtt\Module::balanceAdjustmentCallback($param);
                    break;
            }

            $event->setOk($info);

        } catch (Exception $e) {

            $message = $e->getMessage();
            echo PHP_EOL . '--------------' . PHP_EOL;
            echo '[' . $event->event . '] Code: ' . $e->getCode() . ': ' . $message . ' in ' . $e->getFile() . ' +' . $e->getLine();

            /*
                $isContinue = $e instanceof yii\base\InvalidCallException // ошибка вызова внешней системы
                    || $e instanceof InvalidParamException // Syntax error. В ответ пришел не JSON
                    || strpos($message, 'Operation timed out') !== false // Curl error: #28 - Operation timed out after 30000 milliseconds with 0 bytes received
                    || $e->getCode() == 40001; // Deadlock found when trying to get lock
            */

            $event->setError($e);
        }
    }
}
