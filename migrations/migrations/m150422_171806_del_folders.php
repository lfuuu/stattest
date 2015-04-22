<?php

class m150422_171806_del_folders extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("delete from `grid_settings` where id in (12,13,14)");
    }

    public function down()
    {
        echo "m150422_171806_del_folders cannot be reverted.\n";

        return false;
    }
}
