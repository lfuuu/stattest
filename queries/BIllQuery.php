<?php
namespace app\queries;

use app\models\Bill;
use app\models\BillLine;
use yii\db\ActiveQuery;
use yii\db\Expression;

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
                ->andWhere(new Expression('bl.pk IS NOT NULL'));
    }

}
