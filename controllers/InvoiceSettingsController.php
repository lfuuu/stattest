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
     * @param int $doerOrganizationId
     * @param int $customerCountryCode
     * @param int $settlementAccountTypeId
     * @param int $vatApplyScheme
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionEdit(
        $doerOrganizationId,
        $customerCountryCode,
        $settlementAccountTypeId,
        $vatApplyScheme
    )
    {
        /** @var InvoiceSettings $model */
        $model = InvoiceSettings::findOne([
            'doer_organization_id' => $doerOrganizationId,
            'customer_country_code' => $customerCountryCode,
            'settlement_account_type_id' => $settlementAccountTypeId,
            'vat_apply_scheme' => $vatApplyScheme,
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
     * @param int $doerOrganizationId
     * @param int $customerCountryCode
     * @param int $settlementAccountTypeId
     * @param int $vatApplyScheme
     */
    public function actionDelete(
        $doerOrganizationId,
        $customerCountryCode,
        $settlementAccountTypeId,
        $vatApplyScheme
    )
    {
        InvoiceSettings::deleteAll([
            'doer_organization_id' => $doerOrganizationId,
            'customer_country_code' => $customerCountryCode,
            'settlement_account_type_id' => $settlementAccountTypeId,
            'vat_apply_scheme' => $vatApplyScheme,
        ]);

        $this->redirect('/invoice-settings');
    }

}