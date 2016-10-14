<?php

class m161013_141331_number_source_portable extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(\app\models\Number::tableName(), 'is_ported', $this->integer()->defaultValue(0)->notNull());
    }

    public function down()
    {
        $this->dropColumn(\app\models\Number::tableName(), 'is_ported');
    }
}