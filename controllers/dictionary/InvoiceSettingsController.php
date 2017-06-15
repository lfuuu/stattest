<?php

namespace app\controllers\dictionary;

use app\models\ClientContract;
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
                        'roles' => ['dictionary.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'edit', 'delete', 'recalculate'],
                        'roles' => ['dictionary.invoice-settings'],
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
        if ($model->load(Yii::$app->request->post())) {
            if (InvoiceSettings::findOne($model->primaryKey)) {
                Yii::$app->session->setFlash('error', 'Подобная настройка платежных документов уже существует');
            } else {
                if ($model->validate() && $model->save()) {
                    $this->redirect('/dictionary/invoice-settings');
                }
            }
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $doerOrganizationId
     * @param int $settlementAccountTypeId
     * @param int $vatApplyScheme
     * @param int $customerCountryCode
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionEdit(
        $doerOrganizationId,
        $settlementAccountTypeId,
        $vatApplyScheme,
        $customerCountryCode = null
    ) {
        /** @var InvoiceSettings $model */
        $model = InvoiceSettings::findOne([
            'doer_organization_id' => $doerOrganizationId,
            'customer_country_code' => $customerCountryCode,
            'settlement_account_type_id' => $settlementAccountTypeId,
            'vat_apply_scheme' => $vatApplyScheme,
        ]);
        Assert::isObject($model);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/dictionary/invoice-settings');
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $doerOrganizationId
     * @param int $settlementAccountTypeId
     * @param int $vatApplyScheme
     * @param int $customerCountryCode
     */
    public function actionDelete(
        $doerOrganizationId,
        $settlementAccountTypeId,
        $vatApplyScheme,
        $customerCountryCode = null
    ) {
        InvoiceSettings::deleteAll([
            'doer_organization_id' => $doerOrganizationId,
            'customer_country_code' => $customerCountryCode,
            'settlement_account_type_id' => $settlementAccountTypeId,
            'vat_apply_scheme' => $vatApplyScheme,
        ]);

        $this->redirect('/dictionary/invoice-settings');
    }

    /**
     * Пересчитать эффективную ставку НДС по данным справочника Настройки платежных документов
     *
     * @throws \Exception
     */
    public function actionRecalculate()
    {
        set_time_limit(0);

        ob_start();
        $info = ClientContract::dao()->resetAllEffectiveVATRate();
        ob_clean();

        Yii::$app->session->addFlash('success', sprintf('Из %d ЛС установлена новая ставка НДС на %d ЛС, из них, с НДС не из справочника %d', $info['countAll'], $info['countSet'], $info['countFromOrganization']));

        $this->redirect('/dictionary/invoice-settings');
    }

}