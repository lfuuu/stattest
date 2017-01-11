<?php

namespace app\controllers\stats;

use Yii;
use app\classes\BaseController;
use app\models\filter\PartnerRewardsFilter;

class PartnerRewardsController extends BaseController
{

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['index'],
                'roles' => ['clients.read'],
            ],
        ];
        return $behaviors;
    }

    /**
     * @param bool $isExtends
     * @return string
     */
    public function actionIndex($isExtends = false)
    {
        return $this->render('index',
            [
                'filterModel' => (new PartnerRewardsFilter($isExtends))->load(),
            ]
        );
    }

}