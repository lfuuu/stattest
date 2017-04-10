<?php
/**
 * Универсальные услуги
 */

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\UsageTrunkSettings;
use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\forms\AccountTariffAddForm;
use app\modules\uu\forms\AccountTariffEditForm;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use InvalidArgumentException;
use LogicException;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\web\Response;

class AccountTariffController extends BaseController
{
    // Установить юзерские фильтры + добавить фильтр по клиенту, если он есть
    use AddClientAccountFilterTraits;

    /**
     * Права доступа
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['tarifs.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit', 'edit-voip', 'save-voip', 'cancel', 'resource-cancel'],
                        'roles' => ['tarifs.edit'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Дефолтный обработчик
     * Список
     *
     * @param int $serviceTypeId
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex($serviceTypeId = null)
    {
        $this->_checkNonPackage($serviceTypeId);

        $filterModel = new AccountTariffFilter($serviceTypeId);
        $this->_addClientAccountFilter($filterModel);

        return $this->render(
            'index',
            [
                'filterModel' => $filterModel,
            ]
        );
    }

    /**
     * Создать
     *
     * @param int $serviceTypeId
     * @return string|Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionNew($serviceTypeId)
    {
        $this->_checkNonPackage($serviceTypeId);

        $formModel = new AccountTariffAddForm(
            [
                'serviceTypeId' => $serviceTypeId,
            ]
        );

        if ($formModel->isSaved) {

            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was created successfully'));

            if ($formModel->id) {
                // добавили одного - на его карточку
                return $this->redirect(
                    [
                        'edit',
                        'id' => $formModel->id,
                    ]
                );
            } else {
                // добавили мульти - на их список
                return $this->redirect(
                    [
                        'index',
                        'serviceTypeId' => $serviceTypeId,
                        'AccountTariffFilter[client_account_id]' => $formModel->clientAccountId,
                    ]
                );
            }
        } else {
            return $this->render(
                'edit',
                [
                    'formModel' => $formModel,
                ]
            );
        }

    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string|Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($id)
    {
        try {
            $formModel = new AccountTariffEditForm(
                [
                    'id' => $id,
                ]
            );
        } catch (\InvalidArgumentException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());

            return $this->render(
                '//layouts/empty',
                [
                    'content' => '',
                ]
            );
        }

        if ($formModel->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was saved successfully'));
            return $this->redirect(
                [
                    'edit',
                    'id' => $formModel->id,
                ]
            );
        } else {
            return $this->render(
                'edit',
                [
                    'formModel' => $formModel,
                ]
            );
        }
    }

    /**
     * Отобразить аяксом форму смены тарифа телефонии
     *
     * @param int $id
     * @param int $cityId
     * @param int $serviceTypeId
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionEditVoip($id = null, $cityId = null, $serviceTypeId = null)
    {
        $this->layout = 'minimal';

        try {
            $formModel = $id ?
                // редактировать телефонию или пакет телефонии
                new AccountTariffEditForm(
                    [
                        'id' => $id,
                    ]
                ) :
                // добавить пакет телефонии
                new AccountTariffAddForm(
                    [
                        'serviceTypeId' => $serviceTypeId,
                    ]
                );

            $cityId = (int)$cityId;
            if ($cityId && !$formModel->accountTariff->city_id) {
                // при добавлении пакета нужен город для фильтрации доступных пакетов
                $formModel->accountTariff->city_id = $cityId;
            }
        } catch (\InvalidArgumentException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());

            return $this->render(
                '//layouts/empty',
                [
                    'content' => '',
                ]
            );
        }

        return $this->render(
            'editVoip',
            [
                'formModel' => $formModel,
            ]
        );
    }

    /**
     * Сменить тариф телефонии
     *
     * @return string|Response
     */
    public function actionSaveVoip()
    {
        // загрузить параметры от юзера
        // здесь сильно разнородные данные, поэтому проще хардкорно валидировать, чем писать штатный обработчик
        $transaction = \Yii::$app->db->beginTransaction();
        $serviceTypeId = ServiceType::ID_VOIP;
        try {
            $post = Yii::$app->request->post();

            if (!isset(
                    $post['AccountTariff'],
                    $post['AccountTariff']['ids'],
                    $post['AccountTariff']['tariff_period_id'],
                    $post['AccountTariffLog'],
                    $post['AccountTariffLog']['tariff_period_id'],
                    $post['AccountTariffLog']['actual_from'],
                    $post['accountTariffId'],
                    $post['serviceTypeId']
                )
                || !($actualFromTimestamp = strtotime($post['AccountTariffLog']['actual_from']))
            ) {
                throw new InvalidArgumentException('Неправильные параметры');
            }

            $serviceTypeId = (int)$post['serviceTypeId'];

            // id первой услуги (тарифа или пакета) или 0 (добавление пакета)
            $accountTariffFirstId = (int)$post['accountTariffId'];
            if ($accountTariffFirstId) {
                $accountTariffFirst = AccountTariff::findOne(['id' => $accountTariffFirstId]);
                if (!$accountTariffFirst) {
                    throw new InvalidArgumentException(Yii::t('common', 'Wrong first ID ' . $accountTariffFirstId));
                }

                $accountTariffFirstHash = $accountTariffFirst->getHash();
            } else {
                $accountTariffFirstHash = null;
            }

            $tariffPeriodIdNew = (isset($post['closeTariff']) ? null : (int)$post['AccountTariffLog']['tariff_period_id']);

            $accountTariffIds = (array)$post['AccountTariff']['ids'];
            foreach ($accountTariffIds as $accountTariffId) {

                // найти базовую услугу или пакета
                $packageServiceTypeId = $tariffPeriodIdNew ? TariffPeriod::findOne(['id' => $tariffPeriodIdNew])->tariff->service_type_id : null;
                $accountTariff = $this->findAccountTariff($accountTariffId, $accountTariffFirstHash, $packageServiceTypeId);

                // создать новую услугу, но не менять существующую
                // если тариф меняется с текущего момента, она пересчитается триггером; если с будущего - по крону
                if ($accountTariff->isNewRecord) {
                    $accountTariff->tariff_period_id = $tariffPeriodIdNew;
                    if (!$accountTariff->save()) {
                        throw new ModelValidationException($accountTariff);
                    }
                }

                if ($accountTariff->tariff_period_id && in_array($accountTariff->service_type_id, [ServiceType::ID_TRUNK_PACKAGE_ORIG, ServiceType::ID_TRUNK_PACKAGE_TERM])) {
                    // дополнительно добавить этот пакет транка в маршрутизацию "логического транка"
                    //
                    $type = ($accountTariff->service_type_id == ServiceType::ID_TRUNK_PACKAGE_ORIG) ?
                        UsageTrunkSettings::TYPE_ORIGINATION :
                        UsageTrunkSettings::TYPE_TERMINATION;

                    $usageTrunkSettings = UsageTrunkSettings::findOne([
                        'usage_id' => $accountTariff->prev_account_tariff_id,
                        'package_id' => $accountTariff->id,
                        'type' => $type,
                    ]);

                    if (!$usageTrunkSettings) {
                        $maxOrder = UsageTrunkSettings::find()
                            ->andWhere([
                                'usage_id' => $accountTariff->prev_account_tariff_id,
                                'type' => $type,
                            ])
                            ->max('`order`');

                        $usageTrunkSettings = new UsageTrunkSettings;
                        $usageTrunkSettings->usage_id = $accountTariff->prev_account_tariff_id;
                        $usageTrunkSettings->package_id = $accountTariff->tariffPeriod->tariff_id;
                        $usageTrunkSettings->type = $type;
                        $usageTrunkSettings->order = $maxOrder + 1;
                        if (!$usageTrunkSettings->save()) {
                            throw new ModelValidationException($usageTrunkSettings);
                        }
                    }
                }

                // записать в лог тарифа
                $accountTariffLog = new AccountTariffLog;
                $accountTariffLog->account_tariff_id = $accountTariff->id;
                $accountTariffLog->tariff_period_id = $tariffPeriodIdNew;
                $accountTariffLog->actual_from = date(DateTimeZoneHelper::DATE_FORMAT, $actualFromTimestamp);
                if (!$accountTariffLog->save()) {
                    throw new ModelValidationException($accountTariffLog);
                }
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was saved successfully'));

        } catch (InvalidArgumentException $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());

        } catch (LogicException $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            Yii::$app->session->setFlash('error', YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error'));
        }

        return $this->redirect(
            [
                'index',
                'serviceTypeId' => $serviceTypeId,
            ]
        );
    }

    /**
     * Отменить последнюю смену тарифа
     *
     * @param string $accountTariffHash хэш услуги
     * @return string|Response
     */
    public function actionCancel($accountTariffHash = null)
    {
        $id = 0;
        $serviceTypeId = null;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$accountTariffHash) {
                throw new InvalidArgumentException('Неправильные параметры (accountTariffHash)');
            }

            $ids = Yii::$app->request->get('ids');
            $id = (int)Yii::$app->request->get('id');
            if (!$ids || !is_array($ids) || !count($ids)) {
                if (!$id) {
                    throw new InvalidArgumentException('Неправильные параметры');
                }

                $ids = [$id];
            }

            foreach ($ids as $accountTariffId) {

                // найти базовую услугу или пакета
                $accountTariff = $this->findAccountTariff($accountTariffId, $accountTariffHash, null);
                $serviceTypeId = $accountTariff->service_type_id;

                // лог тарифов
                $accountTariffLogs = $accountTariff->accountTariffLogs;

                // отменяемый тариф
                /** @var AccountTariffLog $accountTariffLogCancelled */
                $accountTariffLogCancelled = array_shift($accountTariffLogs);
                if (!$accountTariff->isLogCancelable()) {
                    throw new LogicException('Нельзя отменить уже примененный тариф');
                }

                // отменить (удалить) последний тариф
                if (!$accountTariffLogCancelled->delete()) {
                    throw new ModelValidationException($accountTariffLogCancelled);
                }

                if (!count($accountTariffLogs)) {

                    // услуга еще даже не начинала действовать, текущего тарифа нет - удалить услугу полностью
                    if (!$accountTariff->delete()) {
                        throw new ModelValidationException($accountTariff);
                    }

                    // редиректить на список, а не карточку
                    $id = null;

                } else {

                    // предпоследний тариф становится текущим
                    /** @var AccountTariffLog $accountTariffLogActual */
                    $accountTariffLogActual = array_shift($accountTariffLogs);

                    // у услуги сменить кэш тарифа
                    $accountTariff->tariff_period_id = $accountTariffLogActual->tariff_period_id;
                    if (!$accountTariff->save()) {
                        throw new ModelValidationException($accountTariff);
                    }
                }
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Тариф успешно отменен');

        } catch (InvalidArgumentException $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());

        } catch (LogicException $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            Yii::$app->session->setFlash('error', YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error'));
        }

        if ($id) {
            // редактировали одну услугу - на ее карточку
            return $this->redirect(
                [
                    'edit',
                    'id' => $id,
                ]
            );
        } else {
            // редактировали много услуг одновременно - на их список
            switch ($serviceTypeId) {
                case ServiceType::ID_TRUNK_PACKAGE_ORIG:
                case ServiceType::ID_TRUNK_PACKAGE_TERM:
                    $serviceTypeId = ServiceType::ID_TRUNK;
                    break;

                case ServiceType::ID_VOIP_PACKAGE:
                case null:
                    $serviceTypeId = ServiceType::ID_VOIP;
                    break;
            }

            return $this->redirect(
                [
                    'index',
                    'serviceTypeId' => $serviceTypeId,
                ]
            );
        }
    }

    /**
     * Отменить последнюю смену количества ресурса
     *
     * @param int $accountTariffId
     * @param int $resourceId
     * @return string|Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionResourceCancel($accountTariffId, $resourceId)
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::findOne(['id' => $accountTariffId]);
        if (!$accountTariff) {
            throw new InvalidParamException('Услуга не найдена');
        }

        try {

            if (!$accountTariff->isResourceCancelable($resourceId)) {
                throw new InvalidParamException('Ресурс невозможно отменить');
            }

            /** @var AccountTariffResourceLog[] $accountTariffResourceLogs */
            $accountTariffResourceLogs = $accountTariff->getAccountTariffResourceLogs($resourceId)->all();
            $accountTariffResourceLog = reset($accountTariffResourceLogs);
            if (!$accountTariffResourceLog->delete()) {
                throw new ModelValidationException($accountTariffResourceLog);
            }
        } catch (\Exception $e) {

            Yii::error($e);
            Yii::$app->session->setFlash('error', YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error'));
        }

        return $this->redirect(
            [
                '/uu/account-tariff/edit',
                'id' => $accountTariffId,
            ]
        );
    }

    /**
     * Найти базовую услугу или пакета
     *
     * @param int $accountTariffId
     * @param string $accountTariffFirstHash хэш первой услуги (тарифа или пакета) или null (добавление пакета)
     * @param int $packageServiceTypeId
     * @return AccountTariff
     */
    protected function findAccountTariff($accountTariffId, $accountTariffFirstHash, $packageServiceTypeId)
    {
        $accountTariffId = (int)$accountTariffId;
        if (!$accountTariffId) {
            throw new InvalidArgumentException(Yii::t('common', 'Wrong ID'));
        }

        $accountTariff = AccountTariff::findOne($accountTariffId);
        if (!$accountTariff) {
            throw new InvalidArgumentException(Yii::t('common', 'Wrong ID ' . $accountTariffId));
        }

        if (!$accountTariffFirstHash) {
            // создание нового пакета
            $accountTariffPackage = new AccountTariff;
            $accountTariffPackage->service_type_id = $packageServiceTypeId;
            $accountTariffPackage->prev_account_tariff_id = $accountTariff->id;
            $accountTariffPackage->client_account_id = $accountTariff->client_account_id;
            $accountTariffPackage->region_id = $accountTariff->region_id;
            $accountTariffPackage->city_id = $accountTariff->city_id;
            return $accountTariffPackage;
        }

        if ($accountTariff->getHash() == $accountTariffFirstHash) {
            // базовый тариф телефонии или транка
            return $accountTariff;
        }

        foreach ($accountTariff->nextAccountTariffs as $accountTariffPackage) {
            if ($accountTariffPackage->getHash() == $accountTariffFirstHash) {
                // базовый тариф телефонии или транка
                return $accountTariffPackage;
            }
        }

        unset($accountTariffPackage);

        throw new InvalidArgumentException(sprintf('Услуга %d с хэшем %s не найдена', $accountTariffId, $accountTariffFirstHash));
    }

    /**
     * @param int $serviceTypeId
     */
    private function _checkNonPackage($serviceTypeId)
    {
        if (in_array($serviceTypeId, ServiceType::$packages)) {
            // для пакетов услуги подключаются через базовую услугу
            throw new InvalidArgumentException('Пакеты надо подключать через базовую услугу');
        }

    }
}