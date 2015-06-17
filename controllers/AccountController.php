<?php

namespace app\controllers;

use app\forms\client\ClientEditForm;
use app\forms\contract\ContractEditForm;
use app\forms\contragent\ContragentEditForm;
use app\models\ClientSuper;
use Yii;
use app\classes\BaseController;
use app\classes\Assert;
use yii\filters\AccessControl;
use app\models\LkWizardState;
use app\models\ClientAccount;
use yii\helpers\Url;


class AccountController extends BaseController
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
                    ]
                ],
            ],
        ];
    }

    public function actionChangeWizardState($id, $state)
    {
        $accountId = $id;

        $account = ClientAccount::findOne($accountId);

        if (!$account || !LkWizardState::isBPStatusAllow($account->business_process_status_id, $account->id))
            throw new \Exception("Wizard не доступен на данном статусе бизнес процесса");

        $wizard = LkWizardState::findOne($accountId);

        if (in_array($state, ['on', 'off', 'review', 'rejected', 'approve', 'first', 'next'])) {

            if ($state == "on" && !$wizard) {
                $wizard = new LkWizardState;
                $wizard->contract_id = $accountId;
                $wizard->step = 1;
                $wizard->state = "process";
                $wizard->save();
            } else {

                Assert::isObject($wizard);

                if ($state == "off") {
                    $wizard->delete();
                } else {
                    if ($state == "first" || $state == "next") {
                        $wizard->step = ($state == "first" ? 1 : ($wizard->step < 4 ? $wizard->step + 1 : 4));
                        if ($wizard->step == 4) {
                            $state = "review";
                        } else {
                            $state = "process";
                        }
                    }

                    $wizard->state = $state;
                    $wizard->save();

                    if ($state == "approve") {
                        $wizard->add100Rub();
                    }
                }
            }
        }

        $this->redirect(Url::toRoute(['client/view', 'id' => $id]));
    }

    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $contragent = new ContragentEditForm(['super_id' => 1]);
        $contract = new ContractEditForm(['contragent_id' => 1, 'super_id' => 1]);
        $client = new ClientEditForm(['contract_id' => 1, 'contragent_id' => 1, 'super_id' => 1]);
        if ($request) {
            $transaction = Yii::$app->db->beginTransaction();
            $commit = false;
            $super = new ClientSuper();
            $super->setAttribute('name', 'autocreate');
            if ($super->save()) {
                unset($request['ContragentEditForm']['super_id']);
                $contragent = new ContragentEditForm(['super_id' => $super->id]);
                if ($contragent->load($request) && $contragent->validate() && $contragent->save()) {
                    $contract = new ContractEditForm(['contragent_id' => $contragent->id]);
                    if ($contract->load($request) && $contract->validate() && $contract->save()) {
                        $client = new ClientEditForm(['id' => $contract->id]);
                        if ($client->load($request) && $client->validate() && $client->save())
                            $commit = true;
                    }
                }
            }
            if ($commit) {
                $transaction->commit();
                return $this->redirect(Url::toRoute(['client/view', 'id' => $client->id]));
            } else {
                $transaction->rollback();
            }
        }

        return $this->render('create', ['contragent' => $contragent, 'client' => $client, 'contract' => $contract]);
    }
}
