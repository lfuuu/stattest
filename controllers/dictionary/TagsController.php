<?php

namespace app\controllers\dictionary;

use app\classes\BaseController;
use app\forms\dictonary\tags\TagsForm;
use Yii;
use yii\base\InvalidParamException;

class TagsController extends BaseController
{

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