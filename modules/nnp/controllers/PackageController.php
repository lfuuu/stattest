<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filter\PackageFilter;
use app\modules\nnp\forms\package\FormEdit;
use app\modules\nnp\forms\package\FormNew;
use Yii;

/**
 * Пакеты
 */
class PackageController extends BaseController
{
    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new PackageFilter();
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
     * @param int $tariff_id
     * @return string
     */
    public function actionEdit($tariff_id)
    {
        /** @var FormEdit $formModel */
        $formModel = new FormEdit([
            'tariff_id' => $tariff_id
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
