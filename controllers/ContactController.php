<?php
namespace app\controllers;

use app\classes\BaseController;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\LkNoticeSetting;
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

        $client = ClientAccount::findOne($clientId);
        if (isset($data['admin-lk-id']) && isset($data['set-admin-lk'])) {
            if ($data['admin-lk-id'] != $client->admin_contact_id) {
                $client->admin_contact_id = $data['admin-lk-id'];
                $client->save();
            }
        } elseif (!empty($data['data'])) {
            $model = new ClientContact();
            $model->setAttributes($data, false);
            $model->client_id = $clientId;
            $model->is_active = 1;
            $model->save();
            if(isset($data['admin-lk-id']) && $model->type == 'email') {
                $client->admin_contact_id = $model->id;
                $client->save();
            }
        }

        $this->redirect(Yii::$app->request->referrer);
    }

    public function actionActivate($id)
    {
        $model = ClientContact::findOne($id);
        if (!$model)
            throw new Exception('Contact not found');

        $model->is_active = intval(!$model->is_active);
        $model->save();
        $this->redirect(Yii::$app->request->referrer);
    }

    public function actionLkActivate($id)
    {
        $statuses = ['working', 'connecting'];
        $contact = ClientContact::findOne($id);
        if (!$contact)
            throw new Exception('Contact not found');

        $lk = LkNoticeSetting::find()->where('client_id', $contact->client_id)->one();

        if (!$lk)
            throw new Exception('Contact not found');

        $contact->is_active = intval(!$contact->is_active);
        $lk->status = $statuses[$contact->is_active];
        $contact->save();
        $lk->save();
        $this->redirect(Yii::$app->request->referrer);
    }
}
