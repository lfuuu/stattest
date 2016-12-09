<?php

use app\models\DidGroup;

class m161111_140937_add_did_group_number_price extends \app\classes\Migration
{
    public function up()
    {
        $didGroupTableName = DidGroup::tableName();
        $this->addColumn($didGroupTableName, 'price1', $this->float());
        $this->addColumn($didGroupTableName, 'price2', $this->float());
        $this->addColumn($didGroupTableName, 'price3', $this->float());

        $sql = <<<SQL
            UPDATE
                {$didGroupTableName} did_group,
                tarifs_number
            SET
                did_group.price1 = tarifs_number.activation_fee
            WHERE
                did_group.id = tarifs_number.did_group_id
SQL;
        $this->execute($sql);

        $this->execute('DROP TABLE tarifs_number');
    }

    public function down()
    {
        echo "m161111_140937_add_did_group_number_price cannot be reverted.\n";

        return false;
    }
}