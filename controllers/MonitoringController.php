<?php

namespace app\controllers;

use app\classes\Assert;
use app\classes\BaseController;
use app\classes\monitoring\MonitorFactory;
use app\classes\traits\AddClientAccountFilterTraits;
use app\dao\MonitoringDao;
use app\exceptions\ModelValidationException;
use app\exceptions\web\NotImplementedHttpException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bik;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientContragent;
use app\models\EquipmentUser;
use app\models\EventQueue;
use app\models\filter\EventQueueFilter;
use app\models\filter\UsageVoipFilter;
use app\models\filter\SormClientFilter;
use app\models\Param;
use app\models\UsageVoip;
use app\modules\transfer\components\services\regular\BasicServiceTransfer as RegularBasicServiceTransfer;
use app\modules\transfer\components\services\regular\RegularTransfer;
use app\modules\transfer\components\services\universal\UniversalTransfer;
use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidValueException;
use yii\db\ActiveQuery;
use yii\db\Expression;

class MonitoringController extends BaseController
{

    use AddClientAccountFilterTraits;

    /**
     * Index
     *
     * @param string $monitor
     * @return string
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex($monitor = 'usages_lost_tariffs')
    {
        return $this->render('index', [
            'monitors' => MonitorFactory::me()->getAll(),
            'current' => MonitorFactory::me()->getOne($monitor),
        ]);
    }

    /**
     * Перенос услуг
     *
     * @param bool $isCurrentOnly
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\Exception
     * @throws InvalidCallException
     * @throws InvalidValueException
     */
    public function actionTransferredServices($isCurrentOnly = true)
    {
        $clientAccount = null;

        if ($isCurrentOnly) {
            $clientAccount = $this->_getCurrentClientAccount();
            if ($clientAccount === null) {
                return $this->redirect('/');
            }
        }

        $regularProcessor = new RegularTransfer;
        $universalProcessor = new UniversalTransfer;

        $knownRegularServices = $regularProcessor->getServices();
        $regularServices = [];
        foreach ($knownRegularServices as $serviceCode => $serviceHandler) {
            /** @var RegularBasicServiceTransfer $serviceHandler */
            $serviceHandler = $regularProcessor->getHandler($serviceCode);

            if (($service = $serviceHandler->getServiceModelName()) === '') {
                continue;
            }

            $regularServices += MonitoringDao::transferredRegularServices($service, $clientAccount);
        }

        $knownUniversalServices = $universalProcessor->getServices();
        $universalServices = [];
        foreach ($knownUniversalServices as $serviceCode => $serviceHandler) {
            /** @var RegularBasicServiceTransfer $serviceHandler */
            $serviceHandler = $regularProcessor->getHandler($serviceCode);

            if (!$serviceHandler->getServiceTypeId()) {
                continue;
            }

            $universalServices += MonitoringDao::transferredUniversalServices($serviceHandler->getServiceTypeId(), $clientAccount);
        }

        return $this->render('transferred_services', [
            'regularServices' => $regularServices,
            'universalServices' => $universalServices,
            'clientAccount' => $clientAccount,
        ]);
    }

    /**
     * Очередь событий
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionEventQueue()
    {
        $get = Yii::$app->request->get();
        $post = Yii::$app->request->post();

        $filterModel = new EventQueueFilter();
        $filterModel->load($get);

        if (isset($post['planButton'])) {
            /** @var ActiveQuery $query */
            $query = $filterModel->search()->query;
            $affectedRows = EventQueue::updateAll([
                'status' => EventQueue::STATUS_PLAN,
                'iteration' => 0,
                'next_start' => new Expression('NOW()'),
            ], $query->where);

            Yii::$app->session->setFlash('success', Yii::t('common', '{n, plural, one{# entry} other{# entries}}', ['n' => $affectedRows]) . ' будут обработаны повторно');
        }

        if (isset($post['okButton'])) {
            /** @var ActiveQuery $query */
            $query = $filterModel->search()->query;
            $affectedRows = EventQueue::updateAll([
                'status' => EventQueue::STATUS_OK,
            ], $query->where);

            Yii::$app->session->setFlash('success', Yii::t('common', '{n, plural, one{# entry} other{# entries}}', ['n' => $affectedRows]) . ' больше не будут обрабатываться');
        }

        return $this->render('eventQueue', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Включение оповещений
     */
    public function actionNotificationOn()
    {
        Param::deleteAll([
            'param' => [
                Param::NOTIFICATIONS_SWITCH_OFF_DATE,
                Param::NOTIFICATIONS_SWITCH_ON_DATE,
            ]
        ]);

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }

    /**
     * Отключение оповещений
     *
     * @throws ModelValidationException
     */
    public function actionNotificationOff()
    {
        $now = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)));
        Param::setParam(
            Param::NOTIFICATIONS_SWITCH_OFF_DATE,
            $now
                ->format(DateTimeZoneHelper::DATETIME_FORMAT),
            $isRawValue = true
        );

        Param::setParam(
            Param::NOTIFICATIONS_SWITCH_ON_DATE,
            $now
                ->modify(Param::NOTIFICATIONS_PERIOD_OFF_MODIFY)
                ->format(DateTimeZoneHelper::DATETIME_FORMAT),
            $isRawValue = true
        );

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }

    /**
     * Отключение пересчета баланса при редактировании счета
     *
     * @throws ModelValidationException
     */
    public function actionRecalculationBalanceWhenBillEditOff()
    {
        Param::setParam(Param::DISABLING_RECALCULATION_BALANCE_WHEN_EDIT_BILL, 1);

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }

    /**
     * Включение пересчета баланса при редактировании счета
     *
     * @throws ModelValidationException
     * @throws \yii\db\StaleObjectException
     * @throws \Exception
     */
    public function actionRecalculationBalanceWhenBillEditOn()
    {
        /** @var Param $param */
        $param = Param::findOne(Param::DISABLING_RECALCULATION_BALANCE_WHEN_EDIT_BILL);

        if ($param) {
            if (!$param->delete()) {
                throw new ModelValidationException($param);
            }
        }

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }


    /**
     * СОРМ: Клиенты
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionSormClients()
    {
        $filterModel = new SormClientFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('sormClients', [
            'filterModel' => $filterModel,
        ]);
    }

    public function actionSormClientsSave($accountId, $field, $value)
    {
        Assert::isInArray($field, ['inn', 'bik', 'bank', 'pay_acc', 'contact_fio', 'contact_phone', 'address_jur', 'address_post', 'equ']);

        $account = ClientAccount::findOne(['id' => $accountId]);

        Assert::isObject($account);

        $value = trim(strip_tags($value));
        Assert::isNotEmpty($value);

        switch ($field) {
            case 'inn':
                $contragent = $account->contragent;

                if ($contragent->inn == $value) {
                    break;
                }

                $contragent->inn = $value;
                if (!$contragent->save()) {
                    throw new ModelValidationException($contragent);
                }
                break;

            case 'bik':
                $bikModel = Bik::findOne(['bik' => $value]);

                Assert::isObject($bikModel);

                $account->bik = $bikModel->bik;
                $account->bank_name = $bikModel->bank_name;
                $account->corr_acc = $bikModel->corr_acc;
                $account->bank_city = $bikModel->bank_city;

                if (!$account->save()) {
                    throw new ModelValidationException($account);
                }
                break;

            case 'bank':
                $account->bank_name = $value;
                if (!$account->save()) {
                    throw new ModelValidationException($account);
                }
                break;

            case 'pay_acc':
                $account->pay_acc = $value;
                if (!$account->save()) {
                    throw new ModelValidationException($account);
                }
                break;

            case 'contact_fio':
            case 'contact_phone':
                $contact = SormClientFilter::getContactByAccount($account);

                if (!$contact) {
                    $contact = new ClientContact();
                    $contact->client_id = $account->id;
                    $contact->type = ClientContact::TYPE_PHONE;
                }

                $contact->{$field == 'contact_fio' ? 'comment' : 'data'} = $value;

                if (!$contact->save()) {
                    throw new ModelValidationException($contact);
                }
                break;

            case 'address_jur':
                $contragent = $account->contragent;

                if ($contragent->legal_type == ClientContragent::PERSON_TYPE) {
                    $model = $contragent->person;
                    $model->registration_address = $value;
                    if ($model->registration_address == $value) {
                        break;
                    }
                } else {
                    $model = $contragent;
                    $model->address_jur = $value;

                    if ($model->address_jur == $value) {
                        break;
                    }
                }

                if (!$model->save()) {
                    throw new ModelValidationException($model);
                }
                break;

            case 'address_post':
                if ($account->address_post == $value) {
                    break;
                }

                $account->address_post = $value;

                if (!$account->save()) {
                    throw new ModelValidationException($account);
                }
                break;

            case 'equ':
                $equ = new EquipmentUser();
                $equ->isStrongCheck = false;
                $equ->client_account_id = $account->id;
                $equ->full_name = $value;
                $equ->birth_date = '2000-01-01';
                if (!$equ->save()) {
                    throw new ModelValidationException($equ);
                }
                break;
            default:
                throw new NotImplementedHttpException();
        }

        return 'ok';
    }


    /**
     * СОРМ Номера
     *
     * @return string
     */
    public function actionSormNumbers()
    {
        $params = Yii::$app->request->get();

        $filterModelSearch = new AccountTariffFilter(ServiceType::ID_VOIP);
        $filterModelSearch->load($params);

        $filterModel = new AccountTariffFilter(ServiceType::ID_VOIP);
        $filterModelOld = new UsageVoipFilter();

        $usageVoipIds = [];
        $accountTariffIds = [];
        if ($regionId = $filterModelSearch->region_id) {
            $ids = SormClientFilter::getAccountTariffIds(
                $regionId,
                $filterModelSearch->is_device_empty === '' ? null : ($filterModelSearch->is_device_empty == TariffPeriod::IS_NOT_SET ? false : true)
            );

            $usageVoipIds = array_filter($ids, function ($v) {
                return $v < AccountTariff::DELTA;
            });
            $accountTariffIds = array_filter($ids, function ($v) {
                return $v >= AccountTariff::DELTA;
            });
        }

        if ($account_manager = $filterModelSearch->account_manager) {
            $filterModel->account_manager = $account_manager;
            $filterModelOld->account_manager = $account_manager;
        }

        $filterModelOld->is_active_client_account = $filterModelSearch->is_active_client_account;
        $filterModel->is_active_client_account = $filterModelSearch->is_active_client_account;

        $filterModelOld->id = $usageVoipIds ?: 0;
        $filterModel->id = $accountTariffIds ?: 0;

        return $this->render('sorm_numbers', [
            'filterModelSearch' => $filterModelSearch,
            'filterModel' => $filterModel,
            'filterModelOld' => $filterModelOld,
        ]);
    }

    /**
     * Сохраняет адрес в соответствующей модели
     *
     * @throws ModelValidationException
     * @throws \yii\base\Exception
     */
    public function actionSaveAddress()
    {
        throw new NotImplementedHttpException('Адреса редактируются только в ЛК');

        $id = Yii::$app->request->post('id');
        $address = Yii::$app->request->post('text');

        Assert::isNotEmpty($id);
        Assert::isNotEmpty($address);

        if ($id >= AccountTariff::DELTA) {
            $accountTariff = AccountTariff::findOne(['id' => $id]);
            Assert::isObject($accountTariff);

            $accountTariff->device_address = $address;
        } else {
            $accountTariff = UsageVoip::findOne(['id' => $id]);
            Assert::isObject($accountTariff);

            $accountTariff->address = $address;
        }

        if (!$accountTariff->save()) {
            throw new ModelValidationException($accountTariff);
        }
    }

    public function actionChangedBills()
    {
        $month = $_GET['month'] ?: 'current';

        $startDate = new \DateTimeImmutable('now');
        $startDate = $startDate->modify('first day of this month');

        if ($month != 'current') {
            $startDate = $startDate->modify('first day of previous month');
        }



        $data = \app\classes\monitoring\ChangedBillsMonitor::me()->getData($startDate);
        return $this->render('changed_bills', ['data' => $data, 'month' => $month]);
    }
}
