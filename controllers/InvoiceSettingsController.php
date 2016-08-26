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
     * @param int $customer_country_code
     * @param int $doer_country_code
     * @param int $settlement_account_type_id
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionEdit($customer_country_code, $doer_country_code, $settlement_account_type_id)
    {
        /** @var InvoiceSettings $model */
        $model = InvoiceSettings::findOne([
            'customer_country_code' => $customer_country_code,
            'doer_country_code' => $doer_country_code,
            'settlement_account_type_id' => $settlement_account_type_id,
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
     * @param int $customer_country_code
     * @param int $doer_country_code
     * @param int $settlement_account_type_id
     * @throws \Exception
     */
    public function actionDelete($customer_country_code, $doer_country_code, $settlement_account_type_id)
    {
        /** @var InvoiceSettings $model */
        $model = InvoiceSettings::findOne([
            'customer_country_code' => $customer_country_code,
            'doer_country_code' => $doer_country_code,
            'settlement_account_type_id' => $settlement_account_type_id,
        ]);

        if (!is_null($model)) {
            $model->delete();
        }

        $this->redirect('/invoice-settings');
    }

}