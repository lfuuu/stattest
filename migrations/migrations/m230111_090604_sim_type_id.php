<?php

/**
 * Class m230111_090604_sim_type_id
 */
class m230111_090604_sim_type_id extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn('sim_registry', 'sim_type_id', $this->integer()->notNull()->defaultValue(\app\modules\sim\models\CardType::ID_DEFAULT));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn('sim_registry', 'sim_type_id');
    }
}
