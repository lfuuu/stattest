<?php

use app\classes\Migration;
use app\models\Invoice;
use yii\db\Expression;

/**
 * Class m220222_094942_invoice_eur_date
 */
class m220222_094942_invoice_eur_date extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Invoice::tableName(), 'date_save', $this->date());
        $this->update(Invoice::tableName(), ['date_save' => (new Expression('date'))]);

        // проставляем дату с/ф = дате счета для компаний вне России
        $this->execute(<<<SQL
update invoice i
inner join organization o on i.organization_id = o.organization_id
inner join newbills b on b.bill_no = i.bill_no
set i.date = b.bill_date
where o.country_id != 643
SQL
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update(Invoice::tableName(), ['date' => (new Expression('date_save'))]);
        $this->dropColumn(Invoice::tableName(), 'date_save');
    }
}
