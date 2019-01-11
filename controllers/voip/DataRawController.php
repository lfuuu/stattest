<?php

namespace app\controllers\voip;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\models\filter\DataRawSearch;
use Yii;

class DataRawController extends BaseController
{

    use AddClientAccountFilterTraits;

    public function actionIndex()
    {
        $searchQuery = Yii::$app->request->queryParams;
        $searchModel = new DataRawSearch;
        $dataProvider = $searchModel->search($searchQuery);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }
}
