<?php

namespace app\modules\sorm\controllers;

use app\exceptions\web\NotImplementedHttpException;
use app\models\ClientContragent;
use app\modules\sorm\classes\ControllerHelperSetDefaults;
use app\modules\sorm\filters\ClientsFilter;
use Yii;
use yii\web\Response;
use app\classes\BaseController;

class ClientsController extends BaseController
{
    public function actionIndex()
    {
        throw new NotImplementedHttpException();
    }

    public function actionPersonB2c()
    {
        $params = Yii::$app->request->post();

        if (!isset($params['ClientsFilter']['is_b2c'])) {
            $params['ClientsFilter']['is_b2c'] = 1;
        }

        ControllerHelperSetDefaults::me()->setDefaults($params);

        $filter = new ClientsFilter(['type' => ClientContragent::PERSON_TYPE]);
        $filter->load($params);

        return $this->render('list/person',[
            'filterModel' => $filter,
        ]);
    }

    public function actionLegalB2c()
    {
        $params = Yii::$app->request->post();

        if (!isset($params['ClientsFilter']['is_b2c'])) {
            $params['ClientsFilter']['is_b2c'] = 1;
        }

        ControllerHelperSetDefaults::me()->setDefaults($params);

        $filter = new ClientsFilter(['type' => ClientContragent::LEGAL_TYPE]);
        $filter->load($params);


        return $this->render('list/legal',[
            'filterModel' => $filter,
        ]);
    }

    public function actionPerson()
    {
        $params = Yii::$app->request->post();

        if (!isset($params['ClientsFilter']['is_b2c'])) {
            $params['ClientsFilter']['is_b2c'] = 0;
        }

        ControllerHelperSetDefaults::me()->setDefaults($params);

        $filter = new ClientsFilter(['type' => ClientContragent::PERSON_TYPE]);
        $filter->load($params);


        return $this->render('list/person',[
            'filterModel' => $filter,
        ]);
    }

    public function actionLegal()
    {
        $params = Yii::$app->request->post();

        if (!isset($params['ClientsFilter']['is_b2c'])) {
            $params['ClientsFilter']['is_b2c'] = 0;
        }

        ControllerHelperSetDefaults::me()->setDefaults($params);

        $filter = new ClientsFilter(['type' => ClientContragent::LEGAL_TYPE]);
        $filter->load($params);


        return $this->render('list/legal',[
            'filterModel' => $filter,
        ]);
    }
}