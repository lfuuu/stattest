<?php

namespace app\modules\mchs\controllers;

use app\classes\BaseController;
use app\classes\DynamicModel;
use app\exceptions\ModelValidationException;
use app\modules\mchs\models\MchsMessage;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

/**
 * Default
 */
class DefaultController extends BaseController
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
                        'actions' => ['index'],
                        'roles' => ['mchs.read'],
                        'allow' => true,
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        if (!$_SERVER['MCHS_API_KEY']) {
            throw new InvalidParamException('Доступ невозможен');
        }

        $message = \Yii::$app->request->post('message');

        $query = MchsMessage::find()
            ->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db' => MchsMessage::getDb(),
            'pagination' => [
                'totalCount' => $query->count(),
                'pageSize' => 5,
            ],
        ]);

        try {
            if (!\Yii::$app->request->post('doSend')) {
                return $this->render('message', ['message' => '', 'dataProvider' => $dataProvider]);
            }

            if (!\Yii::$app->user->can('mchs.send')) {
                throw new InvalidParamException('Доступ закрыт');
            }

            $form = DynamicModel::validateData(['message' => $message], [
                ['message', 'required'],
                ['message', 'string', 'min' => 10, 'max' => 500],
            ]);

            if ($form->hasErrors()) {
                throw new ModelValidationException($form);
            }

            $messageModel = new MchsMessage();
            $messageModel->message = $message;
            $messageModel->status = "Сохранен";

            if (!$messageModel->save()) {
                throw new ModelValidationException($messageModel);
            }

            if (!$messageModel->send()) {
                $messageModel->status = $messageModel->getError();
                \Yii::$app->session->addFlash('error', $messageModel->status);
            } else {
                $messageModel->status = 'Ok';
                \Yii::$app->session->addFlash('success', 'Сообщение отправлено');
                $message = '';
            }

            if (!$messageModel->save()) {
                throw new ModelValidationException($messageModel);
            }
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->render('message', ['message' => $message, 'dataProvider' => $dataProvider]);
    }
}
