<?php

namespace app\classes\operators;

use Yii;
use yii\db\Query;
use app\forms\external_operators\RequestOnlimeStbForm;

class OperatorOnlimeStb extends OperatorOnlimeDevices
{

    const OPERATOR = 'onlime-stb';
    const OPERATOR_CLIENT = 'id36259';

    protected static $requestProducts = [
        [
            'id' => 17609,
            'name' => 'SML-482 HD Base с Wi-Fi',
            'nameFull' => 'Приставка, SML-482 HD Base с опцией Wi-Fi',
            'id_1c' => 'd78e0644-6dbc-11e5-9421-00155d881200',
        ],
        [
            'id' => 13619,
            'name' => 'Доставка внутри МКАД',
            'nameFull' => 'Услуга доставки по Москве в пределах МКАД',
            'id_1c' => '81d52242-4d6c-11e1-8572-00155d881200',
            'type' => 'required_one',
            'is_default' => true,
        ],
        [
            'id' => 13621,
            'name' => 'Доставка за МКАД',
            'nameFull' => 'Услуга доставки за пределами МКАД',
            'id_1c' => '81d52245-4d6c-11e1-8572-00155d881200',
            'type' => 'required_one',
        ],
    ];

    public $isRollback = false;

    public function getRequestForm()
    {
        return new RequestOnlimeStbForm;
    }

    public function modeNewModify(Query $query, $dao)
    {
        $query->leftJoin('tt_stages s', 's.stage_id = t.cur_stage_id');
        $query->leftJoin('tt_doers d', 'd.stage_id = t.cur_stage_id');

        $query->andWhere(['between', 'date_creation', $dao->dateFrom, $dao->dateTo]);
        $query->andWhere('state_id = 33');
        $query->andWhere('d.doer_id IS NULL');
    }

    public function modeWorkModify(Query $query, $dao)
    {
        $query->leftJoin('tt_stages s', 's.stage_id = t.cur_stage_id');
        $query->leftJoin('tt_doers d', 'd.stage_id = t.cur_stage_id');

        $query->andWhere(['between', 'date_creation', $dao->dateFrom, $dao->dateTo]);
        $query->andWhere(['not in', 'state_id', [24, 31, 2, 20, 4, 18, 28, 21]]);
        $query->andWhere('d.doer_id IS NOT NULL');
    }

}