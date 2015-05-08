<?php

class m150508_181329_fix_state__connect_wait_docs extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("update tt_types set states = '70231305224192' where code='connect'");
        $this->execute("update tt_states SET `order`='3' WHERE (`id`='49')");
        $this->execute("update tt_states set deny = deny+35184372088832 where id in (47, 48, 44, 45, 46)");
    }

    public function down()
    {
        echo "m150508_181329_fix_state__connect_wait_docs cannot be reverted.\n";

        return false;
    }
}
