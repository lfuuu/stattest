<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filter\CityFilter;
use app\modules\nnp\forms\city\FormEdit;
use app\modules\nnp\forms\city\FormNew;
use Yii;

/**
 * Города
 */
class CityController extends BaseController
{
    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new CityFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Создать
     *
     * @return string
     */
    public function actionNew()
    {
        /** @var FormNew $formModel */
        $formModel = new FormNew();

        // сообщение об ошибке
        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
        }

        if ($formModel->isSaved) {
            return $this->redirect(['index']);
        } else {
            return $this->render('edit', [
                'formModel' => $formModel,
            ]);
        }
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string
     */
    public function actionEdit($id)
    {
        /** @var FormEdit $formModel */
        $formModel = new FormEdit([
            'id' => $id
        ]);

        // сообщение об ошибке
        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
        }

        if ($formModel->isSaved) {
            return $this->redirect(['index']);
        } else {
            return $this->render('edit', [
                'formModel' => $formModel,
            ]);
        }
    }
}
