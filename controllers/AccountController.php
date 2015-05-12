<?php

namespace app\controllers;

use Yii;
use app\classes\BaseController;
use app\classes\Assert;
use yii\filters\AccessControl;
use app\models\LkWizardState;
use app\models\ClientContract;
use app\models\ClientAccount;


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

        if (in_array($state, ['on', 'off', 'review', 'rejected', 'approve', 'first', 'next']))
        {

            if ($state == "on" && !$wizard)
            {
                $wizard = new LkWizardState;
                $wizard->account_id = $accountId;
                $wizard->step = 1;
                $wizard->state = "process";
                $wizard->save();
            } else {

                Assert::isObject($wizard);

                if ($state == "off")
                {
                    $wizard->delete();
                } else {
                    if ($state == "first" || $state == "next")
                    {
                        $wizard->step = ($state == "first" ? 1 : ($wizard->step < 4 ? $wizard->step+1 : 4));
                        if ($wizard->step == 4)
                        {
                            $state = "review";
                        } else {
                            $state = "process";
                        }
                    }

                    $wizard->state = $state; 
                    $wizard->save();

                    if ($state == "approve")
                    {
                        $wizard->add100Rub();
                    }
                }
            }
        }

        return $this->redirect('/?module=clients&id='.$accountId);
    }

    public function actionDocumentCreate($id)
    {
        $content = Yii::$app->request->post('contract_content');
        $contractType = Yii::$app->request->post('contract_type');
        $contractGroup = Yii::$app->request->post('contract_template_group');
        $contractTemplate = Yii::$app->request->post('contract_template');
        $contractDate = Yii::$app->request->post('contract_date');
        $contractNo = Yii::$app->request->post('contract_no');
        $comment =  Yii::$app->request->post('comment');


        $contractId = ClientContract::dao()->addContract(
            $id,

            $contractType,
            $contractGroup,
            $contractTemplate,

			$contractNo,
            $contractDate,

            $content,
            $comment
		);

        $this->redirect("/?module=clients&id=".$id."&contract_open=true");
    }

}
