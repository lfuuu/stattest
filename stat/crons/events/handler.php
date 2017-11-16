<?php

use app\classes\ActaulizerCallChatUsage;
use app\classes\ActaulizerVoipNumbers;
use app\classes\api\ApiCore;
use app\classes\api\ApiFeedback;
use app\classes\api\ApiPhone;
use app\classes\api\ApiVmCollocation;
use app\classes\api\ApiVpbx;
use app\classes\Event;
use app\classes\HandlerLogger;
use app\classes\partners\RewardCalculate;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
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

    /** @var \EventQueue $event */
    foreach ((EventQueue::getPlanedEvents() + EventQueue::getPlanedErrorEvents()) as $event) {
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
                    if ($isCoreServer) {
                        ApiCore::checkCreateCoreAdmin($param);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::CORE_CREATE_OWNER:
                    if ($isCoreServer) {
                        $info = ApiCore::syncCoreOwner($param);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::USAGE_VIRTPBX__INSERT:
                case Event::USAGE_VIRTPBX__UPDATE:
                case Event::USAGE_VIRTPBX__DELETE:
                    $usageId = isset($param[0]) ? $param[0] : (isset($param['usage_id']) ? $param['usage_id'] : 0);
                    if ($isCoreServer) {
                        VirtPbx3::check($usageId);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::SYNC__VIRTPBX3:
                    $usageId = isset($param[0]) ? $param[0] : (isset($param['usage_id']) ? $param['usage_id'] : 0);
                    if ($isCoreServer) {
                        VirtPbx3::sync($usageId);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::ACTUALIZE_NUMBER:
                    \app\models\Number::dao()->actualizeStatusByE164($param['number']);
                    if ($isCoreServer) {
                        ActaulizerVoipNumbers::me()->actualizeByNumber($param['number']);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::ACTUALIZE_CLIENT:
                    if ($isCoreServer) {
                        ActaulizerVoipNumbers::me()->actualizeByClientId($param['client_id']);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::CHECK__VOIP_NUMBERS:
                    if ($isCoreServer) {
                        ActaulizerVoipNumbers::me()->actualizeAll();
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::ATS3__SYNC:
                    if ($isCoreServer) {
                        ActaulizerVoipNumbers::me()->sync($param['number']);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::CALL_CHAT__ADD:
                case Event::CALL_CHAT__UPDATE:
                case Event::CALL_CHAT__DEL:
                    // события услуги звонок_чат
                    if ($isFeedbackServer) {
                        ActaulizerCallChatUsage::me()->actualizeUsage($param['usage_id']);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::ACCOUNT_BLOCKED:
                    if ($isVpbxServer) {
                        Event::goWithIndicator(
                            Event::VPBX_BLOCKED,
                            $param['account_id'],
                            ClientAccount::tableName(),
                            $param['account_id'],
                            EventQueueIndicator::SECTION_ACCOUNT_BLOCK
                        );
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }

                    // Синхронизировать в VM manager
                    if ($isVmServer) {
                        (new SyncVmCollocation)->disableAccount($param['account_id']);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::ACCOUNT_UNBLOCKED:
                    if ($isVpbxServer) {
                        Event::goWithIndicator(
                            Event::VPBX_UNBLOCKED,
                            $param['account_id'],
                            ClientAccount::tableName(),
                            $param['account_id'],
                            EventQueueIndicator::SECTION_ACCOUNT_BLOCK
                        );
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }

                    // Синхронизировать в VM manager
                    if ($isVmServer) {
                        (new SyncVmCollocation)->enableAccount($param['account_id']);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::VPBX_BLOCKED:
                    // Синхронизировать в Vpbx. Блокировка
                    if ($isVpbxServer) {
                        ApiVpbx::me()->lockAccount($param);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::VPBX_UNBLOCKED:
                    // Синхронизировать в Vpbx. Разблокировка
                    if ($isVpbxServer) {
                        ApiVpbx::me()->unlockAccount($param);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case Event::PARTNER_REWARD: {
                    RewardCalculate::run($param['client_id'], $param['bill_id'], $param['created_at']);
                    break;
                }

                // --------------------------------------------
                // Псевдо-логирование
                // @todo выпилить
                // --------------------------------------------
                case Event::NEWBILLS__INSERT:
                case Event::NEWBILLS__UPDATE:
                case Event::NEWBILLS__DELETE:
                case Event::DOC_DATE_CHANGED:
                case Event::YANDEX_PAYMENT:
                case Event::ATS3__BLOCKED:
                case Event::ADD_ACCOUNT:
                case Event::ADD_SUPER_CLIENT:
                case Event::SYNC_CORE_ADMIN:
                case Event::ATS2_NUMBERS_CHECK:
                case Event::ATS3__DISABLED_NUMBER:
                case Event::ATS3__UNBLOCKED:
                case Event::CLIENT_SET_STATUS:
                case Event::CYBERPLAT_PAYMENT:
                case Event::PRODUCT_PHONE_ADD:
                case Event::PRODUCT_PHONE_REMOVE:
                case Event::UPDATE_PRODUCTS:
                    break;


                // --------------------------------------------
                // Универсальные услуги
                // --------------------------------------------
                case \app\modules\uu\Module::EVENT_ADD_LIGHT:
                    // УУ. Добавить данные в AccountTariffLight
                    if ($isAccountTariffLightServer) {
                        SyncAccountTariffLight::addToAccountTariffLight($param);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\uu\Module::EVENT_DELETE_LIGHT:
                    // УУ. Удалить данные из AccountTariffLight. Теоретически этого быть не должно, но...
                    if ($isAccountTariffLightServer) {
                        SyncAccountTariffLight::deleteFromAccountTariffLight($param);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
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
                    if ($isVmServer) {
                        (new SyncVmCollocation)->syncVm($param['account_tariff_id']);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\uu\Module::EVENT_VPBX:
                    // УУ. Услуга ВАТС
                    if ($isCoreServer) {
                        VirtPbx3::check($param['account_tariff_id']);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\uu\Module::EVENT_VOIP_CALLS:
                    // УУ. Услуга телефонии
                    \app\models\Number::dao()->actualizeStatusByE164($param['number']);

                    if ($isCoreServer) {
                        ActaulizerVoipNumbers::me()->actualizeByNumber($param['number']); // @todo выпилить этот костыль и использовать напрямую ApiPhone::me()->addDid/editDid
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }

                    AccountTariff::actualizeDefaultPackages($param['account_tariff_id']);
                    break;

                case \app\modules\uu\Module::EVENT_VOIP_INTERNET:
                    // УУ. Услуга пакет интернет-трафика
                    if ($isMttServer) {
                        \app\modules\mtt\Module::addInternetPackage($param['account_tariff_id'], $param['internet_traffic']);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\uu\Module::EVENT_CALL_CHAT:
                    // УУ. Услуга call chat
                    if ($isFeedbackServer) {
                        ApiFeedback::createChat($param['account_id'], $param['account_tariff_id']);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\uu\Module::EVENT_RESOURCE_VOIP:
                    // УУ. Отправить измененные ресурсы телефонии на платформу
                    if ($isCoreServer) {
                        ApiPhone::me()->editDid(
                            $param['account_id'],
                            $param['number'],
                            $param['lines'],
                            $param['is_fmc_active'],
                            $param['is_fmc_editable'],
                            $param['is_mobile_outbound_active'],
                            $param['is_mobile_outbound_editable']
                        );
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\uu\Module::EVENT_RESOURCE_VPBX:
                    // УУ. Отправить измененные ресурсы ВАТС на платформу
                    if ($isCoreServer) {
                        ApiVpbx::me()->update(
                            $param['account_id'],
                            $param['account_tariff_id'],
                            $regionId = null,
                            ClientAccount::VERSION_BILLER_UNIVERSAL
                        );
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
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
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\atol\Module::EVENT_REFRESH:
                    // АТОЛ. Обновить статус из онлайн-кассы
                    if ($isAtolServer) {
                        $info = SendToOnlineCashRegister::refreshStatus($param['paymentId']);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
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
                        Event::go(\app\modules\nnp\Module::EVENT_LINKER);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\nnp\Module::EVENT_LINKER:
                    // ННП. Линковка исходных к ID
                    if ($isNnpServer) {
                        $info .= 'Операторы: ' . OperatorLinker::me()->run() . PHP_EOL;
                        $info .= 'Регионы: ' . RegionLinker::me()->run() . PHP_EOL;
                        $info .= 'Города: ' . CityLinker::me()->run() . PHP_EOL;
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\nnp\Module::EVENT_FILTER_TO_PREFIX:
                    // ННП. Конвертировать фильтры в префиксы
                    if ($isNnpServer) {
                        $info .= implode(PHP_EOL, RefreshPrefix::me()->filterToPrefix()) . PHP_EOL;
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                // --------------------------------------------
                // МТТ
                // --------------------------------------------
                case \app\modules\mtt\Module::EVENT_CALLBACK_GET_ACCOUNT_BALANCE:
                    // МТТ. Callback обработчик API-запроса getAccountBalance
                    if ($isMttServer) {
                        \app\modules\mtt\Module::getAccountBalanceCallback($param);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\mtt\Module::EVENT_CALLBACK_GET_ACCOUNT_DATA:
                    // МТТ. Callback обработчик API-запроса getAccountData
                    if ($isMttServer) {
                        \app\modules\mtt\Module::getAccountDataCallback($param);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                case \app\modules\mtt\Module::EVENT_CALLBACK_BALANCE_ADJUSTMENT:
                    // МТТ. Callback обработчик API-запроса balanceAdjustment
                    if ($isMttServer) {
                        \app\modules\mtt\Module::balanceAdjustmentCallback($param);
                    } else {
                        $info = Event::API_IS_SWITCHED_OFF;
                    }
                    break;

                default:
                    // неизвестное событие
                    $info = Event::API_IS_SWITCHED_OFF;
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
