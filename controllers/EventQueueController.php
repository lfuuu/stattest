<?php
/**
 * Очередь событий
 */

namespace app\controllers;

use app\classes\BaseController;
use app\models\filter\EventQueueFilter;
use Yii;

class EventQueueController extends BaseController
{
    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new EventQueueFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }
}