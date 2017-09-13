<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\classes\Event;
use app\classes\Html;
use app\modules\nnp\classes\RefreshPrefix;
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
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new NumberRangeFilter();

        $get = Yii::$app->request->get();
        if (!isset($get['NumberRangeFilter'])) {
            $get['NumberRangeFilter']['is_active'] = 1; // по-умолчанию только "вкл."
        }

        $filterModel->load($get);

        $post = Yii::$app->request->post();

        if (isset($post['resetOptions']) && $post['resetOptions']) {
            $filterModel->resetLinks($post['resetOptions']);
        }

        if (isset($post['disableTriggerButton'])) {
            NumberRange::disableTrigger();
        }

        if (isset($post['enableTriggerButton'])) {
            NumberRange::enableTrigger();
        }

        if (isset($post['filterToPrefixButton'])) {
            // поставить в очередь
            $eventQueue = Event::go(RefreshPrefix::EVENT_FILTER_TO_PREFIX);
            Yii::$app->session->setFlash('success', 'Префиксы будут пересчитаны через несколько минут. ' . Html::a('Проверить', $eventQueue->getUrl()));
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
        }

        return $this->render('edit', [
            'formModel' => $formModel,
        ]);
    }
}
