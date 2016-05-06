<?php
namespace app\controllers\stats;

use Yii;
use app\models\Business;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\filter\ClientAccountAgentFilter;
use app\classes\BaseController;

class AgentController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['report', 'test'],
                'roles' => ['clients.read'],
            ],
        ];
        return $behaviors;
    }

    public function actionReport()
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

        return $this->render('report', [
            'filterModel' => (new ClientAccountAgentFilter)->load(),
            'partnerList' => $partnerList,
        ]);
    }

}
