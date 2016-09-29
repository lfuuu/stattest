<?php
namespace app\controllers\usage;

use app\classes\Assert;
use app\classes\BaseController;
use app\forms\usage\UsageVoipAddPackageForm;
use app\forms\usage\UsageVoipDeleteHistoryForm;
use app\forms\usage\UsageVoipEditForm;
use app\forms\usage\UsageVoipEditPackageForm;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\LogTarif;
use app\models\UsageTrunk;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use app\models\billing\StatPackage;
use Yii;
use yii\filters\AccessControl;

class VoipController extends BaseController
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
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $clientAccountId
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionAdd($clientAccountId)
    {
        $clientAccount = ClientAccount::findOne($clientAccountId);

        Assert::isObject($clientAccount);

        if (UsageTrunk::dao()->hasService($clientAccount)) {
            return $this->render('//error', ['error' => 'Услуга номера не совместима с данным ЛС']);
        }


        $model = new UsageVoipEditForm(['no_of_lines' => 1, 'city_id' => Yii::$app->user->identity->city_id]);
        $model->scenario = Yii::$app->request->post('scenario', 'default');
        $model->initModel($clientAccount);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->scenario === 'add' && $model->validate() && $model->add()) {
                Yii::$app->session->addFlash('success', 'Запись добавлена');
                return $this->redirect(['edit', 'id' => $model->id]);
            }
        }

        $model->processDependenciesNumber();

        return $this->render('add', [
            'model' => $model,
            'clientAccount' => $model->clientAccount,
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \Exception
     */
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
            Yii::$app->session->addFlash('success', 'Пакет добавлен');
            return $this->redirect(['edit', 'id' => $id]);
        }

        $model = new UsageVoipEditForm;
        $model->scenario = Yii::$app->request->post('scenario', 'default');
        $model->initModel($usage->clientAccount, $usage);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->scenario === 'edit' && $model->validate() && $model->edit()) {
                Yii::$app->session->addFlash('success', 'Запись обновлена');
                return $this->redirect(['edit', 'id' => $model->id]);
            }
            if ($model->scenario === 'change-tariff' && $model->validate() && $model->changeTariff()) {
                Yii::$app->session->addFlash('success', 'Тариф сохранен');
                return $this->redirect(['edit', 'id' => $model->id]);
            }
        }

        $tariffHistory =
            LogTarif::find()
                ->andWhere(['service' => 'usage_voip'])
                ->andWhere(['id_service' => $usage->id])
                ->andWhere('id_tarif!=0')
                ->andWhere(['<', 'date_activation', $usage->actual_to])
                ->orderBy('date_activation desc, id desc')
                ->all();

        $usagePackages =
            UsageVoipPackage::find()
                ->where(['usage_voip_id' => $model->id])
                ->orderBy('actual_from asc')
                ->all();

        $packageStat = $packagesHistory = [];

        foreach ($usagePackages as $package) {
            $packageStat[$package->id] = StatPackage::findOne(['package_id' => $package->id]);

            $packagesHistory[$package->id] =
                LogTarif::find()
                    ->andWhere(['service' => 'usage_voip_package'])
                    ->andWhere(['id_service' => $package->id])
                    ->andWhere('id_tarif!=0')
                    ->orderBy('date_activation desc, id desc')
                    ->one();
        }

        return $this->render('edit', [
            'model' => $model,
            'clientAccount' => $model->clientAccount,
            'usage' => $usage,
            'tariffHistory' => $tariffHistory,
            'usagePackages' => $usagePackages,
            'packageStat' => $packageStat,
            'packagesHistory' => $packagesHistory

        ]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionDetachPackage($id)
    {
        /** @var \app\models\UsageVoipPackage $package */
        $package = UsageVoipPackage::findOne($id);
        Assert::isObject($package);

        $now = new \DateTime('now', $package->clientAccount->timezone);

        Assert::isTrue($package->actual_from > $now->format(DateTimeZoneHelper::DATE_FORMAT));

        $usage_id = $package->usage_voip_id;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $package->delete();

            $transaction->commit();
            Yii::$app->session->addFlash('success', 'Пакет удален');
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->redirect(['edit', 'id' => $usage_id]);
    }

    /**
     * @param null $id
     * @return string|\yii\web\Response
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    public function actionEditPackage($id = null)
    {
        Assert::isNotNull($id);

        /** @var \app\models\UsageVoipPackage $package */
        $package = UsageVoipPackage::findOne($id);
        Assert::isObject($package);

        $model = new UsageVoipEditPackageForm;
        $model->initModel($package);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $answer = $model->save();
            if ($answer) {
                Yii::$app->session->addFlash('success', 'Данные по пакету сохранены');
            }

            return $this->redirect(['/usage/voip/edit', 'id' => $package->usageVoip->id]);
        }

        return $this->render('package', [
            'model' => $model
        ]);
    }
}
