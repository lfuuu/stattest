<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filter\NumberRangeFilter;
use app\modules\nnp\forms\numberRange\FormEdit;
use app\modules\nnp\models\NumberRange;
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
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidParamException
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

        if (isset($post['disableTriggerButton'])) {
            NumberRange::disableTrigger();
        }

        if (isset($post['enableTriggerButton'])) {
            NumberRange::enableTrigger();
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
     * @throws \yii\base\InvalidParamException
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
