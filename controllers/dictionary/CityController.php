<?php
/**
 * Страны
 */

namespace app\controllers\dictionary;

use app\classes\BaseController;
use app\models\filter\CityFilter;
use Yii;

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

}