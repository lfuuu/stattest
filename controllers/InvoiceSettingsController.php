<?php

namespace app\controllers;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use app\models\InvoiceSettings;

class InvoiceSettingsController extends BaseController
{

    /**
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
                        'roles' => ['organization.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'edit', 'delete'],
                        'roles' => ['organization.edit'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => InvoiceSettings::find(),
            'sort' => false,
            'pagination' => false,
        ]);

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionAdd()
    {
        /** @var InvoiceSettings $model */
        $model = new InvoiceSettings;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/invoice-settings');
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $customerCountryCode
     * @param int $doerCountryCode
     * @param int $settlementAccountTypeId
     * @param string $contragentType
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionEdit(
        $customerCountryCode,
        $doerCountryCode,
        $settlementAccountTypeId,
        $contragentType
    )
    {
        /** @var InvoiceSettings $model */
        $model = InvoiceSettings::findOne([
            'customer_country_code' => $customerCountryCode,
            'doer_country_code' => $doerCountryCode,
            'settlement_account_type_id' => $settlementAccountTypeId,
            'contragent_type' => $contragentType,
        ]);
        Assert::isObject($model);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/invoice-settings');
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $customerCountryCode
     * @param int $doerCountryCode
     * @param int $settlementAccountTypeId
     * @param string $contragentType
     * @throws \Exception
     */
    public function actionDelete(
        $customerCountryCode,
        $doerCountryCode,
        $settlementAccountTypeId,
        $contragentType
    )
    {
        InvoiceSettings::deleteAll([
            'customer_country_code' => $customerCountryCode,
            'doer_country_code' => $doerCountryCode,
            'settlement_account_type_id' => $settlementAccountTypeId,
            'contragent_type' => $contragentType,
        ]);

        $this->redirect('/invoice-settings');
    }

}