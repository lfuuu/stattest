<?php
namespace app\controllers;

use Yii;
use yii\base\Exception;
use yii\filters\AccessControl;
use app\classes\Assert;
use app\forms\client\ContractEditForm;
use app\classes\BaseController;
use app\forms\client\ContractRewardsEditForm;
use app\models\ClientContract;

class ContractController extends BaseController
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
                        'roles' => ['clients.edit'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'roles' => ['clients.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     * @throws Exception
     */
    public function actionView($id)
    {
        $model = ClientContract::findOne($id);
        if (!$model) {
            throw new Exception('Contract does not exists');
        }

        $accountId = $model->getAccounts()[0]->id;
        if (!($this->getFixClient() && $this->getFixClient()->id == $accountId)) {
            if ($accountId) {
                Yii::$app->session->set('clients_client', $accountId);
                $this->applyFixClient($accountId);
            }
        }
        return $this->redirect(['client/view', 'id' => $accountId]);
    }

    /**
     * @param int $parentId
     * @param int|null $childId
     * @return string|\yii\web\Response
     */
    public function actionCreate($parentId, $childId = null)
    {
        $model = new ContractEditForm(['contragent_id' => $parentId]);
        $request = Yii::$app->request->post();
        $notSave = (isset($request['notSave']) && $request['notSave']);
        if ($model->load($request) && !$notSave && $model->validate() && $model->save()) {
            return $this->redirect([
                'contract/edit',
                'id' => $model->id,
                'childId' => $childId,
                'showLastChanges' => 1
            ]);
        }

        return $this->render('edit', [
            'model' => $model
        ]);

    }

    /**
     * @param int $id
     * @param int|null $childId
     * @param string|null $date
     * @return string|\yii\web\Response
     */
    public function actionEdit($id, $childId = null, $date = null)
    {
        $model = new ContractEditForm(['id' => $id, 'historyVersionRequestedDate' => $date]);

        $accountId = $model->getModel()->getAccounts()[0]->id;
        if (!($this->getFixClient() && $this->getFixClient()->id == $accountId)) {
            if ($accountId) {
                Yii::$app->session->set('clients_client', $accountId);
                $this->applyFixClient($accountId);
            }
        }

        if ($childId === null) {
            parse_str(parse_url(Yii::$app->request->referrer, PHP_URL_QUERY), $get);
            $params = Yii::$app->request->getQueryParams();
            $childId = $params['childId'] = ($get['childId']) ? $get['childId'] : $get['id'];
            Yii::$app->request->setQueryParams($params);
            Yii::$app->request->setUrl(Yii::$app->request->getUrl() . '&childId=' . $childId);
        }

        $request = Yii::$app->request->post();
        $notSave = (isset($request['notSave']) && $request['notSave']);
        if ($model->load($request) && !$notSave && $model->validate() && $model->save()) {
            $returnTo =
                Yii::$app->request->get('returnTo')
                    ?: [
                    'contract/edit',
                    'id' => $id,
                    'childId' => $childId,
                    'showLastChanges' => 1,
                    'date' => $date ? $model->historyVersionStoredDate : null
                ];

            return $this->redirect($returnTo);
        }

        return $this->render('edit', [
            'model' => $model
        ]);
    }

    /**
     * @param int $contractId
     * @param string $usageType
     * @throws Exception
     */
    public function actionEditRewards($contractId, $usageType)
    {
        $contract = ClientContract::findOne($contractId);
        Assert::isObject($contract);

        $model = new ContractRewardsEditForm(['contract_id' => $contractId, 'usage_type' => $usageType]);

        if (!($model->load(Yii::$app->request->post(), 'ClientContractReward') && $model->validate() && $model->save())) {
            Yii::$app->session->setFlash('error', $model->getErrorsAsString());
        }

        $this->redirect(['contract/edit', 'id' => $contractId, '#' => 'rewards']);
    }

}