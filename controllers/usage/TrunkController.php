<?php
namespace app\controllers\usage;

use app\classes\Assert;
use app\classes\BaseController;
use app\forms\usage\UsageTrunkCloseForm;
use app\forms\usage\UsageTrunkEditForm;
use app\forms\usage\UsageTrunkSettingsAddForm;
use app\forms\usage\UsageTrunkSettingsEditForm;
use app\models\ClientAccount;
use app\models\UsageTrunk;
use app\models\UsageTrunkSettings;
use Yii;
use yii\filters\AccessControl;

class TrunkController extends BaseController
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
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $clientAccountId
     * @return string|\yii\web\Response
     * @throws \Exception
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidParamException
     */
    public function actionAdd($clientAccountId)
    {
        $clientAccount = ClientAccount::findOne($clientAccountId);

        Assert::isObject($clientAccount);

        if (!UsageTrunk::dao()->isPossibleAddService($clientAccount)) {
            return $this->render('//error', ['error' => UsageTrunk::dao()->lastErrorMessage]);
        }

        $model = new UsageTrunkEditForm();
        $model->orig_min_payment = 0;
        $model->term_min_payment = 0;
        $model->scenario = Yii::$app->request->post('scenario', 'default');
        $model->initModel($clientAccount);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->scenario == 'add' && $model->validate() && $model->add()) {
                return $this->redirect(['edit', 'id' => $model->id]);
            }
            if ($model->scenario == 'edit' && $model->validate() && $model->edit()) {
                return $this->redirect(['edit', 'id' => $model->id]);
            }
        }

        return $this->render('add', [
            'model' => $model,
            'clientAccount' => $model->clientAccount,
        ]);
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidParamException
     * @throws \Exception
     */
    public function actionEdit($id)
    {
        $usage = UsageTrunk::findOne($id);
        Assert::isObject($usage);
        return $this->_editUsageTrunk($usage);
    }

    /**
     * @param int $clientAccountId
     * @param int $trunkId
     * @return string|\yii\web\Response
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidParamException
     * @throws \Exception
     */
    public function actionEditBy($clientAccountId, $trunkId)
    {
        $usage = UsageTrunk::find()
            ->where([
                'client_account_id' => $clientAccountId,
                'trunk_id' => $trunkId,
            ])
            ->one();
        Assert::isObject($usage);
        return $this->_editUsageTrunk($usage);
    }

    /**
     * @param UsageTrunk $usage
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidParamException
     * @throws \Exception
     */
    private function _editUsageTrunk(UsageTrunk $usage)
    {
        $clientAccount = $usage->clientAccount;

        $model = new UsageTrunkEditForm();
        $model->scenario = Yii::$app->request->post('scenario', 'default');
        $model->initModel($clientAccount, $usage);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->scenario == 'edit' && $model->validate() && $model->edit()) {
                return $this->redirect(['edit', 'id' => $usage->id]);
            }
        }

        $form = new UsageTrunkSettingsAddForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->process()) {
            return $this->redirect(['edit', 'id' => $usage->id]);
        }

        $form = new UsageTrunkSettingsEditForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->process()) {
            return $this->redirect(['edit', 'id' => $usage->id]);
        }

        $form = new UsageTrunkCloseForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->process()) {
            return $this->redirect(['edit', 'id' => $usage->id]);
        }

        $origination =
            UsageTrunkSettings::find()
                ->andWhere(['usage_id' => $usage->id])
                ->andWhere(['type' => UsageTrunkSettings::TYPE_ORIGINATION])
                ->orderBy('order')
                ->all();

        $termination =
            UsageTrunkSettings::find()
                ->andWhere(['usage_id' => $usage->id])
                ->andWhere(['type' => UsageTrunkSettings::TYPE_TERMINATION])
                ->orderBy('order')
                ->all();

        $destination =
            UsageTrunkSettings::find()
                ->andWhere(['usage_id' => $usage->id])
                ->andWhere(['type' => UsageTrunkSettings::TYPE_DESTINATION])
                ->orderBy('order')
                ->all();

        return $this->render('edit', [
            'model' => $model,
            'clientAccount' => $clientAccount,
            'usage' => $usage,
            'origination' => $origination,
            'termination' => $termination,
            'destination' => $destination,
        ]);
    }

}
