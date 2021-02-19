<?php

/**
 * Class m210217_110005_newpayments_sber3
 */
class m210217_110005_newpayments_sber3 extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn('newpayments', 'ecash_operator', 'enum(\'cyberplat\',\'paypal\',\'yandex\',\'sberbank\',\'qiwi\',\'stripe\',\'sberOnlineMob\') DEFAULT NULL');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn('newpayments', 'ecash_operator', 'enum(\'cyberplat\',\'paypal\',\'yandex\',\'sberbank\',\'qiwi\',\'stripe\') DEFAULT NULL');
    }
}
