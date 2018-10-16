<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\classes\Html;
use app\models\EventQueue;
use app\modules\nnp\filters\NumberRangeFilter;
use app\modules\nnp\forms\numberRange\FormEdit;
use app\modules\nnp\models\NumberRange;
use Yii;
use yii\filters\AccessControl;

/**
 * Диапазон номеров
 */
class NumberRangeController extends BaseController
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
                        'roles' => ['nnp.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['edit'],
                        'roles' => ['nnp.write'],
                    ],
                ],
            ],
        ];
    }

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
            $get['NumberRangeFilter']['is_active'] = 1; // по умолчанию только "вкл."
        }

        $filterModel->load($get);

        $post = Yii::$app->request->post();

        /*
            if (isset($post['resetOptions']) && $post['resetOptions']) {
                $filterModel->resetLinks($post['resetOptions']);
            }
        */

/*        if (isset($post['disableTriggerButton'])) {
            NumberRange::disableTrigger();
        }

        if (isset($post['enableTriggerButton'])) {
            NumberRange::enableTrigger();
        }
*/

        if (isset($post['syncNnpAll'])) {
            NumberRange::syncNnpAll();
        }

        if (isset($post['filterToPrefixButton'])) {
            // поставить в очередь
            $eventQueue = EventQueue::go(\app\modules\nnp\Module::EVENT_FILTER_TO_PREFIX);
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
            return $this->redirect(['index', 'NumberRangeFilter[country_code]' => $formModel->numberRange->country_code, 'NumberRangeFilter[is_active]' => 1]);
        }

        return $this->render('edit', [
            'formModel' => $formModel,
        ]);
    }
}
