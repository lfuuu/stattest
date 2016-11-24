<?php
namespace app\controllers;

use app\classes\BaseController;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\LkNoticeSetting;
use app\models\LkWizardState;
use \Yii;
use yii\base\Exception;
use yii\filters\AccessControl;

class ContactController extends BaseController
{
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
                ],
            ],
        ];
    }

    public function actionCreate($clientId)
    {
        $data = Yii::$app->request->post();

        if (!empty($data['data'])) {
            $model = new ClientContact(["client_id" => $clientId]);
            $model->setAttributes($data, false);
            $model->save();
        }

        $this->redirect(Yii::$app->request->referrer);
    }

    public function actionActivate($id)
    {
        $model = ClientContact::findOne($id);
        if (!$model) {
            throw new Exception('Contact not found');
        }

        $model->is_active = (int)!$model->is_active;
        $model->save(false);
        $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Установка, что контакт добавленный в ЛК проверен (или не проверен), в обход процедуры валидации.
     *
     * @param int $id id контакта
     * @throws Exception
     */
    public function actionLkActivate($id)
    {
        $contact = ClientContact::findOne($id);
        if (!$contact) {
            throw new Exception('Contact not found');
        }

        $lk = LkNoticeSetting::findOne([
            'client_id' => $contact->client_id,
            'client_contact_id' => $contact->id
        ]);

        if (!$lk) {
            throw new Exception('Contact not found');
        }

        // статус через поведение сохраниться в контакт
        $lk->status = $lk->status == LkNoticeSetting::STATUS_CONNECT ? LkNoticeSetting::STATUS_WORK : LkNoticeSetting::STATUS_CONNECT;
        $lk->save();

        $this->redirect(Yii::$app->request->referrer);
    }
}
