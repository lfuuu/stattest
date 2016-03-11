<?php
/**
 * Страны
 */

namespace app\controllers\dictionary;

use app\classes\BaseController;
use app\models\filter\CountryFilter;
use Yii;

class CountryController extends BaseController
{
    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new CountryFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

}