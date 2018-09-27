<?php

namespace app\controllers;

use app\classes\BaseController;
use app\forms\client\ContragentEditForm;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;

class ContragentController extends BaseController
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['edit', 'create', 'transfer', 'transfer-success'],
                        'roles' => ['clients.edit'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $parentId
     * @param int|null $childId
     * @return string
     * @throws InvalidParamException
     */
    public function actionCreate($parentId, $childId = null)
    {
        $model = new ContragentEditForm(['super_id' => $parentId]);

        $request = Yii::$app->request->post();
        $notSave = (isset($request['notSave']) && $request['notSave']);
        if ($model->load($request) && !$notSave && $model->validate() && $model->save()) {
            $this->redirect(['client/view', 'id' => $childId]);
        }

        return $this->render('edit', [
            'model' => $model
        ]);
    }

    /**
     * @param int $id
     * @param int|null $childId
     * @param string|null $date
     * @return string
     * @throws InvalidParamException
     */
    public function actionEdit($id, $childId = null, $date = null)
    {
        $model = new ContragentEditForm(['id' => $id, 'historyVersionRequestedDate' => $date]);

        if (!($this->getFixClient() && $this->getFixClient()->getContract()->contragent_id == $id)) {
            $contragentModel = $model->getContragentModel();
            $contracts = $contragentModel->getContracts();

            if (sizeof($contracts)) {
                $account = $contragentModel->getContracts()[0]->getAccounts()[0];
                if ($account) {
                    Yii::$app->session->set('clients_client', $account->id);
                    $this->applyFixClient($account->id);
                }
            }
        }

        if ($childId === null) {
            parse_str(parse_url(Yii::$app->request->referrer, PHP_URL_QUERY), $get);
            $params = Yii::$app->request->getQueryParams();
            $childId = $params['childId'] = ($get['childId']) ? $get['childId'] : $get['id'];
            Yii::$app->request->setQueryParams($params);
            Yii::$app->request->setUrl(Yii::$app->request->getUrl() . '&childId=' . $childId);
        }

        $showLastChanges = false;
        $request = Yii::$app->request->post();
        $notSave = (isset($request['notSave']) && $request['notSave']);
        if ($model->load($request) && !$notSave && $model->validate() && $model->save()) {
            $showLastChanges = true;
        }

        return $this->render('edit', [
            'model' => $model,
            'showLastChanges' => $showLastChanges,
        ]);

    }

}
