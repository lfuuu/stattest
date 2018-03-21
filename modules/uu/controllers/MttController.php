<?php
/**
 * Статистика Mtt
 */

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\exceptions\ModelValidationException;
use app\models\filter\mtt_raw\MttRawFilter;
use app\models\mtt_raw\MttRaw;
use app\modules\mtt\classes\MttAdapter;
use app\modules\uu\models\AccountTariff;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\web\Response;

class MttController extends BaseController
{
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
                        'actions' => ['index', 'update-number', 'update-balance'],
                        'roles' => ['services_voip.r'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Статистика
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new MttRawFilter;
        $filterModel->load(Yii::$app->request->get());

        $serviceidCount = count($filterModel->serviceid);
        if ($filterModel->group_time !== '' &&
            ((
                $serviceidCount === 1 &&
                !in_array($filterModel->serviceid[0], array_merge(MttRaw::SERVICE_ID_SMS, MttRaw::SERVICE_ID_INET))
            ) || (
                $serviceidCount === 2 &&
                (
                    !empty(array_diff($filterModel->serviceid, MttRaw::SERVICE_ID_SMS)) &&
                    !empty(array_diff($filterModel->serviceid, MttRaw::SERVICE_ID_INET))
                )
            ))
        ) {
            Yii::$app->session->addFlash('error', 'Поддерживаются однотипные услуги SMS и Интернет');
            return $this->redirect(['/uu/mtt/']);
        }

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Обновить МТТ
     *
     * @param string $field
     * @param string $method
     * @param int $id
     * @return string|Response
     * @throws \yii\base\InvalidParamException
     * @throws ModelValidationException
     */
    private function _updateMtt($field, $method, $id)
    {
        $accountTariff = AccountTariff::findOne(['id' => $id]);
        if (!$accountTariff) {
            throw new InvalidParamException('Неправильная услуга');
        }

        $accountTariff->{$field} = null;
        if (!$accountTariff->save()) {
            throw new ModelValidationException($accountTariff);
        }

        MttAdapter::me()->{$method}($accountTariff->voip_number, $accountTariff->id);
        Yii::$app->session->setFlash('success', 'Запрос в МТТ отправлен. Вероятно, ответ будет через несколько секунд.');

        return $this->redirect(['/uu/account-tariff/edit', 'id' => $accountTariff->id]);
    }

    /**
     * Обновить баланс МТТ
     *
     * @param int $accountTariffId
     * @return string|Response
     * @throws \yii\base\InvalidParamException
     * @throws ModelValidationException
     */
    public function actionUpdateBalance($accountTariffId)
    {
        return $this->_updateMtt('mtt_balance', 'getAccountBalance', $accountTariffId);
    }

    /**
     * Обновить номер МТТ
     *
     * @param int $accountTariffId
     * @return string|Response
     * @throws \yii\base\InvalidParamException
     * @throws ModelValidationException
     */
    public function actionUpdateNumber($accountTariffId)
    {
        return $this->_updateMtt('mtt_number', 'getAccountData', $accountTariffId);
    }

}