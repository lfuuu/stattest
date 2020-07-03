<?php

namespace app\modules\nnp2\controllers;

use app\classes\BaseController;
use app\modules\nnp\models\Country;
use yii\filters\AccessControl;

/**
 * Default
 */
class DefaultController extends BaseController
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
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect(['/nnp2/number-range/', 'NumberRangeFilter[country_code]' => Country::RUSSIA, 'NumberRangeFilter[is_active]' => 1]);
    }

}
