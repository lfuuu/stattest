<?php

use app\classes\Migration;

/**
 * Class m231227_082603_ip_reg_address
 */
class m231227_082603_ip_reg_address extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\ClientContragent::tableName(), 'address_registration_ip', $this->string(1024)->notNull()->defaultValue(''));
        $this->update(\app\models\ClientContragent::tableName(), ['address_registration_ip' => new \yii\db\Expression('address_jur')]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\ClientContragent::tableName(), 'address_registration_ip');
    }
}
