<?php
/**
 * Универсальные услуги
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\uu\filter\AccountTariffFilter;
use app\classes\uu\forms\AccountTariffAddForm;
use app\classes\uu\forms\AccountTariffEditForm;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\helpers\DateTimeZoneHelper;
use InvalidArgumentException;
use LogicException;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;

class AccountTariffController extends BaseController
{
    // Установить юзерские фильтры + добавить фильтр по клиенту, если он есть
    use AddClientAccountFilterTraits;

    /**
     * Права доступа
     * @return []
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
                        'actions' => ['new', 'edit', 'edit-voip', 'save-voip', 'cancel'],
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
     */
    public function actionIndex($serviceTypeId = '')
    {
        $filterModel = new AccountTariffFilter($serviceTypeId);
        $this->addClientAccountFilter($filterModel);

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Создать
     *
     * @param int $serviceTypeId
     * @return string|Response
     */
    public function actionNew($serviceTypeId)
    {
        $formModel = new AccountTariffAddForm([
            'serviceTypeId' => $serviceTypeId,
        ]);

        if ($formModel->isSaved) {

            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was created successfully'));

            if ($formModel->id) {
                // добавили одного - на его карточку
                return $this->redirect([
                    'edit',
                    'id' => $formModel->id,
                ]);
            } else {
                // добавили мульти - на их список
                return $this->redirect([
                    'index',
                    'serviceTypeId' => $serviceTypeId,
                    'AccountTariffFilter[client_account_id]' => $formModel->clientAccountId,
                ]);
            }
        } else {
            return $this->render('edit', [
                'formModel' => $formModel,
            ]);
        }

    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string|Response
     */
    public function actionEdit($id)
    {
        try {
            $formModel = new AccountTariffEditForm([
                'id' => $id,
            ]);
        } catch (\InvalidArgumentException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());

            return $this->render('//layouts/empty', [
                'content' => '',
            ]);
        }

        if ($formModel->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was saved successfully'));
            return $this->redirect([
                'edit',
                'id' => $formModel->id,
            ]);
        } else {
            return $this->render('edit', [
                'formModel' => $formModel,
            ]);
        }

    }

    /**
     * Отобразить аяксом форму смены тарифа телефонии
     *
     * @param int $id
     * @return string
     */
    public function actionEditVoip($id = null, $cityId = null)
    {
        $this->layout = 'minimal';

        try {
            $formModel = $id ?
                // редактировать телефонию или пакет телефонии
                (new AccountTariffEditForm([
                    'id' => $id,
                ])) :
                // добавить пакет телефонии
                (new AccountTariffAddForm([
                    'serviceTypeId' => ServiceType::ID_VOIP_PACKAGE,
                ]));

            $cityId = (int)$cityId;
            if ($cityId && !$formModel->accountTariff->city_id) {
                // при добавлении пакета нужен город для фильтрации доступных пакетов
                $formModel->accountTariff->city_id = $cityId;
            }
        } catch (\InvalidArgumentException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());

            return $this->render('//layouts/empty', [
                'content' => '',
            ]);
        }

        return $this->render('editVoip', [
            'formModel' => $formModel,
        ]);
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
        try {
            $post = Yii::$app->request->post();

            if (!isset(
                    $post['AccountTariff'],
                    $post['AccountTariff']['ids'],
                    $post['AccountTariff']['tariff_period_id'],
                    $post['AccountTariffLog'],
                    $post['AccountTariffLog']['tariff_period_id'],
                    $post['AccountTariffLog']['actual_from'],
                    $post['accountTariffId']
                )
                || !($actualFromTimestamp = strtotime($post['AccountTariffLog']['actual_from']))
            ) {
                throw new InvalidArgumentException('Неправильные параметры');
            }

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

                // найти услугу телефонии или пакета телефонии
                $accountTariff = $this->findAccountTariff($accountTariffId, $accountTariffFirstHash);

                // изменить услугу
                $accountTariff->tariff_period_id = $tariffPeriodIdNew;
                if (!$accountTariff->save()) {
                    $errors = $accountTariff->getFirstErrors();
                    throw new LogicException(reset($errors));
                }

                // записать в лог тарифа
                $accountTariffLog = new AccountTariffLog;
                $accountTariffLog->account_tariff_id = $accountTariff->id;
                $accountTariffLog->tariff_period_id = $tariffPeriodIdNew;
                $accountTariffLog->actual_from = date(DateTimeZoneHelper::DATE_FORMAT, $actualFromTimestamp);
                if (!$accountTariffLog->save()) {
                    $errors = $accountTariffLog->getFirstErrors();
                    throw new LogicException(reset($errors));
                }

                if (!$tariffPeriodIdNew) {
                    // если закрывается услуга, то надо закрыть и все пакеты
                    foreach ($accountTariff->nextAccountTariffs as $accountTariffPackage) {

                        if (!$accountTariffPackage->tariff_period_id) {
                            // эта услуга и так закрыта
                            continue;
                        }

                        // изменить услугу
                        $accountTariffPackage->tariff_period_id = null;
                        if (!$accountTariffPackage->save()) {
                            $errors = $accountTariffPackage->getFirstErrors();
                            throw new LogicException(reset($errors));
                        }

                        // записать в лог тарифа
                        $accountTariffLogPackage = new AccountTariffLog;
                        $accountTariffLogPackage->account_tariff_id = $accountTariffPackage->id;
                        $accountTariffLogPackage->tariff_period_id = null;
                        $accountTariffLogPackage->actual_from = date(DateTimeZoneHelper::DATE_FORMAT, $actualFromTimestamp);
                        if (!$accountTariffLogPackage->save()) {
                            $errors = $accountTariffLogPackage->getFirstErrors();
                            throw new LogicException(reset($errors));
                        }

                    }
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

        return $this->redirect([
            'index',
            'serviceTypeId' => ServiceType::ID_VOIP,
        ]);
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

                // найти услугу телефонии или пакета телефонии
                $accountTariff = $this->findAccountTariff($accountTariffId, $accountTariffHash);
                $serviceTypeId = $accountTariff->service_type_id;

                // лог тарифов
                $accountTariffLogs = $accountTariff->accountTariffLogs;

                // отменяемый тариф
                /** @var AccountTariffLog $accountTariffLogCancelled */
                $accountTariffLogCancelled = array_shift($accountTariffLogs);
                if (!$accountTariff->isCancelable()) {
                    throw new LogicException('Нельзя отменить уже примененный тариф');
                }

                // отменить (удалить) последний тариф
                if (!$accountTariffLogCancelled->delete()) {
                    $errors = $accountTariffLogCancelled->getFirstErrors();
                    throw new LogicException(reset($errors));
                }

                if (!count($accountTariffLogs)) {

                    // услуга еще даже не начинала действовать, текущего тарифа нет - удалить услугу полностью
                    if (!$accountTariff->delete()) {
                        $errors = $accountTariff->getFirstErrors();
                        throw new LogicException(reset($errors));
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
                        $errors = $accountTariff->getFirstErrors();
                        throw new LogicException(reset($errors));
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
            // редактировали один - на его карточку
            return $this->redirect([
                'edit',
                'id' => $id,
            ]);
        } else {
            // редактировали много - на их список
            if (!$serviceTypeId || $serviceTypeId == ServiceType::ID_VOIP_PACKAGE) {
                $serviceTypeId = ServiceType::ID_VOIP;
            }
            return $this->redirect([
                'index',
                'serviceTypeId' => $serviceTypeId,
            ]);
        }
    }

    /**
     * найти услугу телефонии или пакета телефонии
     * @param int $accountTariffId
     * @param string $accountTariffFirstHash хэш первой услуги (тарифа или пакета) или null (добавление пакета)
     * @return AccountTariff
     */
    protected function findAccountTariff($accountTariffId, $accountTariffFirstHash)
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
            $accountTariffPackage->service_type_id = ServiceType::ID_VOIP_PACKAGE;
            $accountTariffPackage->prev_account_tariff_id = $accountTariff->id;
            $accountTariffPackage->client_account_id = $accountTariff->client_account_id;
            $accountTariffPackage->region_id = $accountTariff->region_id;
            $accountTariffPackage->city_id = $accountTariff->city_id;
            return $accountTariffPackage;
        }

        if ($accountTariff->getHash() == $accountTariffFirstHash) {
            // тариф телефонии
            return $accountTariff;
        }

        foreach ($accountTariff->nextAccountTariffs as $accountTariffPackage) {
            if ($accountTariffPackage->getHash() == $accountTariffFirstHash) {
                // тариф пакета телефонии
                return $accountTariffPackage;
            }
        }
        unset($accountTariffPackage);

        throw new InvalidArgumentException(sprintf('Услуга %d с хэшем %s не найдена', $accountTariffId, $accountTariffFirstHash));
    }
}