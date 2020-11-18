<?php

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\modules\uu\forms\TagsForm;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;

class TagsController extends BaseController
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
                        'roles' => ['dictionary.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['edit', 'new'],
                        'roles' => ['dictionary.tags'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function actionIndex()
    {
        $form = new TagsForm;

        return $this->render('grid', [
            'form' => $form,
            'dataProvider' => $form->spawnDataProvider([
                'sort' => false,
                'pagination' => false,
            ]),
        ]);
    }

    /**
     * @param int $id
     * @return string
     * @throws InvalidParamException
     */
    public function actionEdit($id = 0)
    {
        /** @var TagsForm $formModel */
        $formModel = new TagsForm(['id' => $id]);

        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
        }

        if ($formModel->isSaved) {
            return $this->redirect(['index']);
        }

        return $this->render('form', [
            'formModel' => $formModel,
        ]);
    }

}