<?php

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\modules\uu\models\AccountLogResource;
use Yii;
use yii\filters\AccessControl;


class ResourceController extends BaseController
{

    /**
     * Права доступа
     *
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
                        'actions' => ['clear'],
                        'roles' => ['services_voip.edit'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Очистить ресурсы телефонии и пересчитать их заново
     */
    public function actionClear()
    {
        $post = Yii::$app->request->post();
        if (isset($post['dropButton'])) {

            if (isset($post['isPrevMonth'])) {
                Yii::$app->session->setFlash('success',
                    'Удалено ресурсов: ' . AccountLogResource::clearCalls('first day of previous month', 'last day of previous month')
                );
            }

            if (isset($post['isThisMonth'])) {
                Yii::$app->session->setFlash('success',
                    'Удалено ресурсов: ' . AccountLogResource::clearCalls('first day of this month', 'last day of this month')
                );
            }
        }

        return $this->render('clear');
    }
}