<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filter\PrefixFilter;
use app\modules\nnp\forms\PrefixFormEdit;
use app\modules\nnp\forms\PrefixFormNew;
use Yii;

/**
 * Префиксы
 */
class PrefixController extends BaseController
{
    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new PrefixFilter();
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
        /** @var PrefixFormNew $form */
        $form = new PrefixFormNew();

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
        /** @var PrefixFormEdit $form */
        $form = new PrefixFormEdit([
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
