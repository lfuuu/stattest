<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filter\NumberRangeFilter;
use app\modules\nnp\forms\NumberRangeFormEdit;
use Yii;

/**
 * Диапазон номеров
 */
class NumberRangeController extends BaseController
{
    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new NumberRangeFilter();

        $get = Yii::$app->request->get();
        if (!isset($get['CountryFilter'])) {
            $get['NumberRangeFilter']['is_active'] = 1; // по-умолчанию только "вкл."
        }
        $filterModel->load($get);

        $post = Yii::$app->request->post();
        if (isset($post['Prefix'])) {
            $filterModel->addOrRemoveFilterModelToPrefix($post['Prefix']);
        }

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string
     */
    public function actionEdit($id)
    {
        /** @var NumberRangeFormEdit $form */
        $form = new NumberRangeFormEdit([
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
