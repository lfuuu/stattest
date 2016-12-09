<?php
namespace app\controllers\stats;


use Yii;
use app\classes\BaseController;
use app\models\Business;
use app\models\ClientContract;
use app\models\ClientContragent;
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
     * @return string
     */
    public function actionIndex()
    {
        $partners = ClientContract::find()
            ->andWhere(['business_id' => Business::PARTNER])
            ->innerJoin(ClientContragent::tableName(), ClientContragent::tableName() . '.id = contragent_id')
            ->orderBy(ClientContragent::tableName() . '.name')
            ->all();

        $partnerList = [];
        foreach ($partners as $partner) {
            $partnerList[$partner->id] = $partner->contragent->name . ' (#' . $partner->id . ')';
        }

        return $this->render('index', [
            'filterModel' => (new PartnerRewardsFilter)->load(),
            'partnerList' => $partnerList,
        ]);
    }

}