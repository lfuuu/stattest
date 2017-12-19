<?php

use app\classes\ActaulizerCallChatUsage;
use app\classes\ActaulizerVoipNumbers;
use app\classes\api\ApiCore;
use app\classes\api\ApiFeedback;
use app\classes\api\ApiPhone;
use app\classes\api\ApiVmCollocation;
use app\classes\api\ApiVpbx;
use app\classes\HandlerLogger;
use app\classes\partners\RewardCalculate;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\EventQueue;
use app\models\EventQueueIndicator;
use app\modules\atol\behaviors\SendToOnlineCashRegister;
use app\modules\mtt\classes\MttAdapter;
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

echo PHP_EOL . 'Start ' . date(DateTimeZoneHelper::DATETIME_FORMAT);

$sleepTime = 3;
$workTime = 120;
$counter = 2;

do {
    doEvents();
    sleep($sleepTime);
} while ($counter++ < round($workTime / $sleepTime));

echo PHP_EOL . 'Stop ' . date(DateTimeZoneHelper::DATETIME_FORMAT) . PHP_EOL;

/**
 * Выполнить запланированное
 */
function doEvents()
{
    $isCoreServer = (isset(\Yii::$app->params['CORE_SERVER']) && \Yii::$app->params['CORE_SERVER']);
    $isVpbxServer = ApiVpbx::me()->isAvailable();
    $isVmServer = ApiVmCollocation::me()->isAvailable();
    $isFeedbackServer = ApiFeedback::isAvailable();
    $isAccountTariffLightServer = SyncAccountTariffLight::isAvailable();
    $isNnpServer = \app\modules\nnp\Module::isAvailable();
    $isAtolServer = \app\modules\atol\classes\Api::me()->isAvailable();
    $isMttServer = MttAdapter::me()->isAvailable();
    echo '. ';

    $activeQuery = EventQueue::getPlannedQuery();
    /** @var EventQueue $event */
    foreach ($activeQuery->each() as $event) {
        HandlerLogger::me()->clear();

        $info = '';

        try {
            echo PHP_EOL . $event->event . ', ' . $event->param;

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

            if ($event->account_tariff_id) {
                // все запросы по одной услуге надо выполнять строго последовательно
                if ($event->hasPrevEvent()) {
                    throw new LogicException('Еще не выполнен предыдущий запрос по этой услуге');
                }
            }

            switch ($event->event) {
                case EventQueue::USAGE_VOIP__INSERT:
                case EventQueue::USAGE_VOIP__UPDATE:
                case EventQueue::USAGE_VOIP__DELETE: {
                    // ats2Numbers::check();
                    break;
                }

                case EventQueue::ADD_PAYMENT: {
                    EventHandler::updateBalance($param[1]);
                    // (new AddPaymentNotificationProcessor($param[1], $param[0]))->makeSingleClientNotification();
                    break;
                }

                case EventQueue::UPDATE_BALANCE: {
                    EventHandler::updateBalance($param);
                    break;
                }

                case EventQueue::MIDNIGHT: {

                    // проверка необходимости включать или выключать услуги
                    EventQueue::go(EventQueue::CHECK__USAGES);

                    /*
                        // каждый 2-ой рабочий день, помечаем, что все счета показываем в LK
                        if (WorkDays::isWorkDayFromMonthStart(time(), 2)) {
                            Event::go(Event::MIDNIGHT__LK_BILLS4ALL);
                        }
                    */

                    // за 4 дня предупреждаем о списании абонентки аваносовым клиентам
                    if (WorkDays::isWorkDayFromMonthEnd(time(), 4)) {
                        EventQueue::go(EventQueue::MIDNIGHT__MONTHLY_FEE_MSG);
                    }

                    // очистка предоплаченных счетов
                    EventQueue::go(EventQueue::MIDNIGHT__CLEAN_PRE_PAYED_BILLS);

                    // очистка очереди событий
                    EventQueue::go(EventQueue::MIDNIGHT__CLEAN_EVENT_QUEUE);

                    break;
                }

                case EventQueue::CHECK__USAGES: {
                    // проверка необходимости включить или выключить услугу UsageVoip
                    EventQueue::go(EventQueue::CHECK__VOIP_OLD_NUMBERS);

                    // проверка необходимости включить или выключить услугу в новой схеме
                    EventQueue::go(EventQueue::CHECK__VOIP_NUMBERS);

                    // проверка необходимости включить или выключить услугу UsageVirtPbx
                    EventQueue::go(EventQueue::CHECK__VIRTPBX3);

                    // проверка необходимости включить или выключить улугу UsageCallChat
                    EventQueue::go(EventQueue::CHECK__CALL_CHAT);

                    break;
                }

                // проверка необходимости включить или выключить услугу UsageVoip
                case EventQueue::CHECK__VOIP_OLD_NUMBERS: {
                    voipNumbers::check();
                    break;
                }

                // проверка необходимости включить или выключить услугу UsageVirtPbx
                case EventQueue::CHECK__VIRTPBX3: {
                    $usageId = isset($param[0]) ? $param[0] : (isset($param['usage_id']) ? $param['usage_id'] : 0);
                    VirtPbx3::check($usageId);
                    break;
                }

                // проверка необходимости включить или выключить услугу UsageCallChat
                // @todo перенести в новый демон
                case EventQueue::CHECK__CALL_CHAT: {
                    ActaulizerCallChatUsage::me()->actualizeUsages();
                    break;
                }

                // каждый 2-ой рабочий день, помечаем, что все счета показываем в LK
                case EventQueue::MIDNIGHT__LK_BILLS4ALL: {
                    NewBill::setLkShowForAll();
                    break;
                }

                // за 4 дня предупреждаем о списании абонентки аваносовым клиентам
                case EventQueue::MIDNIGHT__MONTHLY_FEE_MSG: {
                    // $execStr = "cd ".PATH_TO_ROOT."crons/stat/; php -c /etc/ before_billing.php >> /var/log/nispd/cron_before_billing.php";
                    // echo " exec: ".$execStr;
                    // exec($execStr);
                    break;
                }

                // очистка предоплаченных счетов
                case EventQueue::MIDNIGHT__CLEAN_PRE_PAYED_BILLS: {
                    Bill::cleanOldPrePayedBills();
                    break;
                }

                // очистка очереди событий
                case EventQueue::MIDNIGHT__CLEAN_EVENT_QUEUE: {
                    EventQueue::clean();
                    break;
                }

                case EventQueue::LK_SETTINGS_TO_MAILER: {
                    /** @var \app\modules\notifier\Module $notifier */
                    $notifier = Yii::$app->getModule('notifier');
                    $notifier->actions->applySchemePersonalSubscribe($param);
                    break;
                }

                case EventQueue::CHECK_CREATE_CORE_OWNER:
                    if ($isCoreServer) {
                        ApiCore::checkCreateCoreAdmin($param);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::CORE_CREATE_OWNER:
                    if ($isCoreServer) {
                        $info = ApiCore::syncCoreOwner($param);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::USAGE_VIRTPBX__INSERT:
                case EventQueue::USAGE_VIRTPBX__UPDATE:
                case EventQueue::USAGE_VIRTPBX__DELETE:
                    $usageId = isset($param[0]) ? $param[0] : (isset($param['usage_id']) ? $param['usage_id'] : 0);
                    if ($isCoreServer) {
                        VirtPbx3::check($usageId);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::SYNC__VIRTPBX3:
                    $usageId = isset($param[0]) ? $param[0] : (isset($param['usage_id']) ? $param['usage_id'] : 0);
                    if ($isCoreServer) {
                        VirtPbx3::sync($usageId);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::ACTUALIZE_NUMBER:
                    \app\models\Number::dao()->actualizeStatusByE164($param['number']);
                    if ($isCoreServer) {
                        ActaulizerVoipNumbers::me()->actualizeByNumber($param['number']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::ACTUALIZE_CLIENT:
                    if ($isCoreServer) {
                        ActaulizerVoipNumbers::me()->actualizeByClientId($param['client_id']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::CHECK__VOIP_NUMBERS:
                    if ($isCoreServer) {
                        ActaulizerVoipNumbers::me()->actualizeAll();
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::ATS3__SYNC:
                    if ($isCoreServer) {
                        ActaulizerVoipNumbers::me()->sync($param['number']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::CALL_CHAT__ADD:
                case EventQueue::CALL_CHAT__UPDATE:
                case EventQueue::CALL_CHAT__DEL:
                    // события услуги звонок_чат
                    if ($isFeedbackServer) {
                        ActaulizerCallChatUsage::me()->actualizeUsage($param['usage_id']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::ACCOUNT_BLOCKED:
                    if ($isVpbxServer) {
                        EventQueue::goWithIndicator(
                            EventQueue::VPBX_BLOCKED,
                            $param['account_id'],
                            ClientAccount::tableName(),
                            $param['account_id'],
                            EventQueueIndicator::SECTION_ACCOUNT_BLOCK
                        );
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }

                    // Синхронизировать в VM manager
                    if ($isVmServer) {
                        (new SyncVmCollocation)->disableAccount($param['account_id']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::ACCOUNT_UNBLOCKED:
                    if ($isVpbxServer) {
                        EventQueue::goWithIndicator(
                            EventQueue::VPBX_UNBLOCKED,
                            $param['account_id'],
                            ClientAccount::tableName(),
                            $param['account_id'],
                            EventQueueIndicator::SECTION_ACCOUNT_BLOCK
                        );
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }

                    // Синхронизировать в VM manager
                    if ($isVmServer) {
                        (new SyncVmCollocation)->enableAccount($param['account_id']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::VPBX_BLOCKED:
                    // Синхронизировать в Vpbx. Блокировка
                    if ($isVpbxServer) {
                        ApiVpbx::me()->lockAccount($param);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::VPBX_UNBLOCKED:
                    // Синхронизировать в Vpbx. Разблокировка
                    if ($isVpbxServer) {
                        ApiVpbx::me()->unlockAccount($param);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case EventQueue::PARTNER_REWARD: {
                    RewardCalculate::run($param['client_id'], $param['bill_id'], $param['created_at']);
                    break;
                }

                // --------------------------------------------
                // Псевдо-логирование
                // @todo выпилить
                // --------------------------------------------
                case EventQueue::NEWBILLS__INSERT:
                case EventQueue::NEWBILLS__UPDATE:
                case EventQueue::NEWBILLS__DELETE:
                case EventQueue::DOC_DATE_CHANGED:
                case EventQueue::YANDEX_PAYMENT:
                case EventQueue::ATS3__BLOCKED:
                case EventQueue::ADD_ACCOUNT:
                case EventQueue::ADD_SUPER_CLIENT:
                case EventQueue::SYNC_CORE_ADMIN:
                case EventQueue::ATS2_NUMBERS_CHECK:
                case EventQueue::ATS3__DISABLED_NUMBER:
                case EventQueue::ATS3__UNBLOCKED:
                case EventQueue::CLIENT_SET_STATUS:
                case EventQueue::CYBERPLAT_PAYMENT:
                case EventQueue::PRODUCT_PHONE_ADD:
                case EventQueue::PRODUCT_PHONE_REMOVE:
                case EventQueue::UPDATE_PRODUCTS:
                    break;


                // --------------------------------------------
                // Универсальные услуги
                // --------------------------------------------
                case \app\modules\uu\Module::EVENT_ADD_LIGHT:
                    // УУ. Добавить данные в AccountTariffLight
                    if ($isAccountTariffLightServer) {
                        SyncAccountTariffLight::addToAccountTariffLight($param);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\uu\Module::EVENT_DELETE_LIGHT:
                    // УУ. Удалить данные из AccountTariffLight. Теоретически этого быть не должно, но...
                    if ($isAccountTariffLightServer) {
                        SyncAccountTariffLight::deleteFromAccountTariffLight($param);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\uu\Module::EVENT_RECALC_ACCOUNT:
                    // УУ. Билинговать клиента
                    if (Yii::$app->params['eventQueueIsUnderTheHighLoad']) {
                        $info = EventQueue::HANDLER_IS_SWITCHED_OFF;
                    } else {
                        AccountTariffBiller::recalc($param);
                    }
                    break;

                case \app\modules\uu\Module::EVENT_RECALC_BALANCE:
                    // УУ. Пересчитать realtime баланс
                    if (Yii::$app->params['eventQueueIsUnderTheHighLoad']) {
                        $info = EventQueue::HANDLER_IS_SWITCHED_OFF;
                    } else {
                        RecalcRealtimeBalance::recalc($param['client_account_id']);
                    }
                    break;

                case \app\modules\uu\Module::EVENT_VM_SYNC:
                    // УУ. Услуга VM collocation
                    if ($isVmServer) {
                        (new SyncVmCollocation)->syncVm($param['account_tariff_id']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\uu\Module::EVENT_VPBX:
                    // УУ. Услуга ВАТС
                    if ($isCoreServer) {
                        VirtPbx3::check($param['account_tariff_id']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\uu\Module::EVENT_VOIP_CALLS:
                    // УУ. Услуга телефонии
                    \app\models\Number::dao()->actualizeStatusByE164($param['number']);

                    if ($isCoreServer) {
                        ActaulizerVoipNumbers::me()->actualizeByNumber($param['number']); // @todo выпилить этот костыль и использовать напрямую ApiPhone::me()->addDid/editDid
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }

                    // УУ. Добавление/выключение дефолтных пакетов телефонии
                    AccountTariff::actualizeDefaultPackages($param['account_tariff_id']);
                    break;

                case \app\modules\uu\Module::EVENT_ADD_DEFAULT_PACKAGES:
                    // УУ. Добавление/выключение дефолтных пакетов телефонии
                    AccountTariff::actualizeDefaultPackages($param['account_tariff_id']);
                    break;

                case \app\modules\uu\Module::EVENT_CALL_CHAT:
                    // УУ. Услуга call chat
                    if ($isFeedbackServer) {
                        ApiFeedback::createChat($param['client_account_id'], $param['account_tariff_id']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\uu\Module::EVENT_RESOURCE_VOIP:
                    // УУ. Отправить измененные ресурсы телефонии на платформу
                    if ($isCoreServer) {
                        ApiPhone::me()->editDid(
                            $param['client_account_id'],
                            $param['number'],
                            $param['lines'],
                            $param['is_fmc_active'],
                            $param['is_fmc_editable'],
                            $param['is_mobile_outbound_active'],
                            $param['is_mobile_outbound_editable']
                        );
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\uu\Module::EVENT_RESOURCE_VPBX:
                    // УУ. Отправить измененные ресурсы ВАТС на платформу
                    if ($isCoreServer) {
                        ApiVpbx::me()->update(
                            $param['client_account_id'],
                            $param['account_tariff_id'],
                            $regionId = null,
                            ClientAccount::VERSION_BILLER_UNIVERSAL
                        );
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                // --------------------------------------------
                // АТОЛ
                // --------------------------------------------
                case \app\modules\atol\Module::EVENT_SEND:
                    // АТОЛ. В соответствии с ФЗ−54 отправить данные в онлайн-кассу. А она сама отправит чек покупателю и в налоговую
                    if ($isAtolServer) {
                        $info = SendToOnlineCashRegister::send($param['paymentId']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\atol\Module::EVENT_REFRESH:
                    // АТОЛ. Обновить статус из онлайн-кассы
                    if ($isAtolServer) {
                        $info = SendToOnlineCashRegister::refreshStatus($param['paymentId']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                // --------------------------------------------
                // ННП
                // --------------------------------------------
                case \app\modules\nnp\Module::EVENT_IMPORT:
                    // ННП. Импорт страны
                    if ($isNnpServer) {
                        $info = CountryFile::importById($param['fileId']);

                        // поставить в очередь для пересчета операторов, регионов и городов
                        EventQueue::go(\app\modules\nnp\Module::EVENT_LINKER);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\nnp\Module::EVENT_LINKER:
                    // ННП. Линковка исходных к ID
                    if ($isNnpServer) {
                        $info .= 'Операторы: ' . OperatorLinker::me()->run() . PHP_EOL;
                        $info .= 'Регионы: ' . RegionLinker::me()->run() . PHP_EOL;
                        $info .= 'Города: ' . CityLinker::me()->run() . PHP_EOL;
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\nnp\Module::EVENT_FILTER_TO_PREFIX:
                    // ННП. Конвертировать фильтры в префиксы
                    if ($isNnpServer) {
                        $info .= implode(PHP_EOL, RefreshPrefix::me()->filterToPrefix()) . PHP_EOL;
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                // --------------------------------------------
                // МТТ
                // --------------------------------------------
                case \app\modules\mtt\Module::EVENT_ADD_INTERNET:
                    // МТТ. Добавить интернет
                    if ($isMttServer) {
                        $info = \app\modules\mtt\Module::addInternetPackage($param['package_account_tariff_id'], $param['internet_traffic']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\mtt\Module::EVENT_CLEAR_INTERNET:
                    // МТТ. Сжечь интернет
                    if ($isMttServer) {
                        $info = \app\modules\mtt\Module::clearInternet($param['account_tariff_id']);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\mtt\Module::EVENT_CALLBACK_GET_ACCOUNT_BALANCE:
                    // МТТ. Callback обработчик API-запроса getAccountBalance
                    if ($isMttServer) {
                        \app\modules\mtt\Module::getAccountBalanceCallback($param);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\mtt\Module::EVENT_CALLBACK_GET_ACCOUNT_DATA:
                    // МТТ. Callback обработчик API-запроса getAccountData
                    if ($isMttServer) {
                        \app\modules\mtt\Module::getAccountDataCallback($param);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\mtt\Module::EVENT_CALLBACK_BALANCE_ADJUSTMENT:
                    // МТТ. Callback обработчик API-запроса balanceAdjustment
                    if ($isMttServer) {
                        \app\modules\mtt\Module::balanceAdjustmentCallback($param);
                    } else {
                        $info = EventQueue::API_IS_SWITCHED_OFF;
                    }
                    break;

                default:
                    // неизвестное событие
                    $info = EventQueue::API_IS_SWITCHED_OFF;
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
