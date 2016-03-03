<?php
namespace app\queries;

use app\models\Bill;
use app\models\BillLine;
use yii\db\ActiveQuery;

class BillQuery extends ActiveQuery
{

    /**
     * @return ActiveQuery
     */
    public function noContainsDeposit()
    {
        return
            $this
                ->leftJoin(
                    ['bl' => BillLine::tableName()],
                    'bl.bill_no = ' . Bill::tableName() . '.bill_no'
                )
                ->andWhere(['bl.type' => BillLine::LINE_TYPE_ZADATOK])
                ->andWhere('bl.pk IS NOT NULL');
    }

}
