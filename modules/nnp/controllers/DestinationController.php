<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filter\DestinationFilter;
use app\modules\nnp\forms\DestinationFormEdit;
use app\modules\nnp\forms\DestinationFormNew;
use Yii;

/**
 * Направления
 */
class DestinationController extends BaseController
{
    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new DestinationFilter();
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
        /** @var DestinationFormNew $form */
        $form = new DestinationFormNew();

        if ($form->isSaved) {
            return $this->redirect(['index']);
        } else {
            return $this->render('edit', [
                'formModel' => $form,
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
        /** @var DestinationFormEdit $form */
        $form = new DestinationFormEdit([
            'id' => $id
        ]);

        if ($form->isSaved) {
            return $this->redirect(['index']);
        } else {
            return $this->render('edit', [
                'formModel' => $form,
            ]);
        }
    }
}
