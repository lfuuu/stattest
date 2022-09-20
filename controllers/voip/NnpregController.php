<?php

namespace app\controllers\voip;

use app\classes\BaseController;
use app\exceptions\ModelValidationException;
use app\forms\voip\NnpregForm;
use app\forms\voip\RegistryForm;
use app\models\City;
use app\models\voip\Registry;
use Yii;
use yii\filters\AccessControl;

class NnpregController extends BaseController
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
                        'actions' => ['index'],
                        'roles' => ['voip.admin'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     * @throws ModelValidationException
     */
    public function actionIndex()
    {
        $model = new NnpregForm();

        $post = Yii::$app->request->isPost ? Yii::$app->request->post() : Yii::$app->request->get();

        $model->load($post);

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => false,
        ]);
    }
}
