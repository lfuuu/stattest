<?php
namespace app\queries;

use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
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

    /**
     * Фильтр счетов: выставленные только универсальным биллером
     */
    public function billerUniversal()
    {
        $this->andWhere(['biller_version' => ClientAccount::VERSION_BILLER_UNIVERSAL]);
    }

    /**
     * Фильтр счетов: выставленные только "старым" биллером
     */
    public function billerUsage()
    {
        $this->andWhere(['biller_version' => ClientAccount::VERSION_BILLER_USAGE]);
    }

}
