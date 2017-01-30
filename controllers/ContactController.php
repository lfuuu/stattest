<?php
namespace app\controllers;

use app\classes\BaseController;
use app\classes\uu\forms\CrudMultipleTrait;
use app\models\ClientContact;
use app\models\LkNoticeSetting;
use kartik\widgets\ActiveForm;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\filters\AccessControl;
use yii\web\Response;

class ContactController extends BaseController
{
    use CrudMultipleTrait;

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
                ],
            ],
        ];
    }

    /**
     * @param int $id
     * @return string|Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($id)
    {
        $contacts = ClientContact::find()
            ->where(['client_id' => $id])
            ->orderBy([
                'type' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->indexBy('id')
            ->all();

        $post = Yii::$app->request->post();

        if (isset($post['ClientContact']) && Yii::$app->request->isAjax) {
            // ajax-валидация
            $models = [];
            $modelIds = array_keys($post['ClientContact']);
            foreach ($modelIds as $modelId) {
                $models[$modelId] = new ClientContact();
            }

            Model::loadMultiple($models, $post);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validateMultiple($models);
        }

        // заготовка новой модели
        $clientContactNew = new ClientContact();
        $clientContactNew->client_id = $id;

        // создать/отредактировать модели
        if (isset($post['ClientContact'])) {
            $contacts = $this->crudMultiple($contacts, $post, $clientContactNew);

            if ($this->validateErrors) {
                Yii::$app->session->setFlash('error', implode('. ', $this->validateErrors));
            } else {
                Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            }
        }

        return $this->render('edit', [
            'id' => $id,
            'contacts' => $contacts,
        ]);
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
