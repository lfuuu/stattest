<?php

namespace app\controllers\stats;

use app\classes\BaseController;
use app\models\filter\BillingApiFilter;
use app\models\filter\PartnerRewardsFilter;
use Yii;
use yii\base\InvalidParamException;

class BillingApiController extends BaseController
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
     * @return string
     * @throws InvalidParamException
     */
    public function actionIndex()
    {
        $account = $this->getFixClient();

        return $this->render('index', [
            'filterModel' => (new BillingApiFilter())->load($account ? $account->id : null),
        ]);
    }

}