<?php
namespace app\controllers\usage;

use app\classes\Assert;
use app\forms\usage\UsageVoipAddPackageForm;
use app\forms\usage\UsageVoipDeleteHistoryForm;
use app\forms\usage\UsageVoipEditForm;
use app\models\ClientAccount;
use app\models\LogTarif;
use app\models\TariffVoipPackage;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use Yii;
use yii\filters\AccessControl;
use app\classes\BaseController;

class VoipController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionAdd($clientAccountId)
    {
        $clientAccount = ClientAccount::findOne($clientAccountId);

        $model = new UsageVoipEditForm(['no_of_lines' => 1]);
        $model->scenario = Yii::$app->request->post('scenario', 'default');
        $model->initModel($clientAccount);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->scenario == 'add' && $model->validate() && $model->add()) {
                Yii::$app->session->addFlash('success', 'Запись добавлена');
                return $this->redirect(['edit', 'id' => $model->id]);
            }
        }

        $model->processDependenciesNumber();
        $model->processDependenciesTariff();

        return $this->render('add', [
            'model' => $model,
            'clientAccount' => $model->clientAccount,
        ]);
    }

    public function actionEdit($id)
    {
        $usage = UsageVoip::findOne($id);

        $form = new UsageVoipDeleteHistoryForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->process()) {
            Yii::$app->session->addFlash('success', 'Тариф удален');
            return $this->redirect(['edit', 'id' => $id]);
        }

        $form = new UsageVoipAddPackageForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->process()) {
            Yii::$app->session->addFlash('success', 'пакет добавлен');
            return $this->redirect(['edit', 'id' => $id, 'rnd' => time()]);
        }

        $model = new UsageVoipEditForm();
        $model->scenario = Yii::$app->request->post('scenario', 'default');
        $model->initModel($usage->clientAccount, $usage);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->scenario == 'edit' && $model->validate() && $model->edit()) {
                Yii::$app->session->addFlash('success', 'Запись обновлена');
                return $this->redirect(['edit', 'id' => $model->id]);
            }
            if ($model->scenario == 'change-tariff' && $model->validate() && $model->changeTariff()) {
                Yii::$app->session->addFlash('success', 'Тариф тариф сохранен');
                return $this->redirect(['edit', 'id' => $model->id]);
            }
        }

        $model->processDependenciesTariff();

        $tariffHistory =
            LogTarif::find()
                ->andWhere(['service' => 'usage_voip'])
                ->andWhere(['id_service' => $usage->id])
                ->andWhere('id_tarif!=0')
                ->orderBy('date_activation desc, id desc')
                ->all();

        $usagePackages =
            UsageVoipPackage::find()
                ->where(['usage_voip_id' => $model->id])
                ->orderBy('actual_from asc')
                ->all();

        return $this->render('edit', [
            'model' => $model,
            'clientAccount' => $model->clientAccount,
            'usage' => $usage,
            'tariffHistory' => $tariffHistory,
            'usagePackages' => $usagePackages,
        ]);
    }

    public function actionDetachPackage($id)
    {
        $usageVoipPackage = UsageVoipPackage::findOne($id);
        Assert::isObject($usageVoipPackage);

        $usage_id = $usageVoipPackage->usage_voip_id;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $usageVoipPackage->delete();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->redirect(['edit', 'id' => $usage_id]);
    }

}
