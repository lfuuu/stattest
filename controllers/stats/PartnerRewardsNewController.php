<?php

namespace app\controllers\stats;

use app\classes\BaseController;
use app\models\filter\PartnerRewardsNewFilter;
use Yii;
use yii\base\InvalidParamException;

class PartnerRewardsNewController extends BaseController
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
     * @throws InvalidParamException
     */
    public function actionIndex($isExtends = false)
    {
        return $this->render('index', [
            'filterModel' => (new PartnerRewardsNewFilter($isExtends))->load(),
        ]);
    }

}