<?php

use app\classes\ActaulizerCallChatUsage;
use app\classes\ActaulizerVoipNumbers;
use app\classes\api\ApiCore;
use app\classes\api\ApiFeedback;
use app\classes\api\ApiPhone;
use app\classes\api\ApiVpbx;
use app\classes\behaviors\SendToOnlineCashRegister;
use app\classes\Event;
use app\classes\HttpClientLogger;
use app\classes\partners\RewardCalculate;
use app\models\ClientAccount;
use app\models\EventQueueIndicator;
use app\modules\nnp\classes\CityLinker;
use app\modules\nnp\classes\OperatorLinker;
use app\modules\nnp\classes\RegionLinker;
use app\modules\nnp\media\ImportServiceUploaded;
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
        HttpClientLogger::me()->clear();

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

                    // каждый 2-ой рабочий день, помечаем, что все счета показываем в LK
                    /* if (WorkDays::isWorkDayFromMonthStart(time(), 2)) {
                        Event::go(Event::MIDNIGHT__LK_BILLS4ALL);
                    } */

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

                case SyncAccountTariffLight::EVENT_ADD_TO_ACCOUNT_TARIFF_LIGHT:
                    // Добавить данные в AccountTariffLight
                    SyncAccountTariffLight::addToAccountTariffLight($param);
                    break;

                case SyncAccountTariffLight::EVENT_DELETE_FROM_ACCOUNT_TARIFF_LIGHT:
                    // Удалить данные из AccountTariffLight. Теоретически этого быть не должно, но...
                    SyncAccountTariffLight::deleteFromAccountTariffLight($param);
                    break;

                case ImportServiceUploaded::EVENT:
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

                case AccountTariffBiller::EVENT_RECALC:
                    // Билинговать UU-клиента
                    AccountTariffBiller::recalc($param);
                    break;

                case RecalcRealtimeBalance::EVENT_RECALC:
                    // Пересчитать realtime баланс
                    RecalcRealtimeBalance::recalc($param['clientAccountId']);
                    break;

                case SendToOnlineCashRegister::EVENT_SEND:
                    // В соответствии с ФЗ−54 отправить данные в онлайн-кассу. А она сама отправит чек покупателю и в налоговую
                    $info = SendToOnlineCashRegister::send($param['paymentId']);
                    break;

                case SendToOnlineCashRegister::EVENT_REFRESH:
                    // Обновить статус из онлайн-кассы
                    $info = SendToOnlineCashRegister::refreshStatus($param['paymentId']);
                    break;

                case Event::CHECK_CREATE_CORE_ADMIN:
                    $isCoreServer && ApiCore::checkCreateCoreAdmin($param);
                    break;

                case Event::CORE_CREATE_ADMIN:
                    $isCoreServer && ($info = ApiCore::syncCoreAdmin($param));
                    break;

                case Event::ADD_ACCOUNT:
                    // Пока ничего не делаем
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

                case SyncVmCollocation::EVENT_SYNC:
                    // Синхронизировать в VM manager
                    (new SyncVmCollocation)->syncVm($param['account_tariff_id']);
                    break;

                case Event::UU_ACCOUNT_TARIFF_VOIP:
                    \app\models\Number::dao()->actualizeStatusByE164($param['number']);
                    $isCoreServer && ActaulizerVoipNumbers::me()->actualizeByNumber($param['number']);

                    // Если эта услуга активна - подключить базовый пакет. Если неактивна - закрыть все пакеты.
                    AccountTariff::findOne(['id' => $param['account_tariff_id']])
                        ->addOrCloseDefaultPackage();
                    break;

                case Event::UU_ACCOUNT_TARIFF_CALL_CHAT:
                    ApiFeedback::createChat($param['account_id'], $param['account_tariff_id']);
                    break;

                case Event::UU_ACCOUNT_TARIFF_RESOURCE_VOIP:
                    // Отправить измененные ресурсы телефонии на платформу и другим поставщикам услуг
                    $isCoreServer && ApiPhone::me()->editDid(
                        $param['account_id'],
                        $param['number'],
                        $param['lines'],
                        $param['is_fmc_active'],
                        $param['is_fmc_editable']
                    );
                    break;

                case Event::UU_ACCOUNT_TARIFF_RESOURCE_VPBX:
                    // Отправить измененные ресурсы ВАТС на платформу и другим поставщикам услуг
                    $isCoreServer && ApiVpbx::me()->update($param['account_id'], $param['account_tariff_id'], $regionId = null, ClientAccount::VERSION_BILLER_UNIVERSAL);
                    break;

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
            }

            $event->setOk($info);
        } catch (yii\base\InvalidCallException $e) { // для ошибок вызова внешней системы: Повторяем
            echo PHP_EOL . '--------------' . PHP_EOL;
            echo '[' . $event->event . '] Code: ' . $e->getCode() . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' +' . $e->getLine();
            $event->setError($e);
        } catch (Exception $e) { // Для всех остальных ошибок: завершаем выполнение задачи
            echo PHP_EOL . '--------------' . PHP_EOL;
            echo '[' . $event->event . '] Code: ' . $e->getCode() . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' +' . $e->getLine();
            $event->setError($e);
            $event->status = \app\models\EventQueue::STATUS_STOP;
            $event->save();
        }
    }
}
