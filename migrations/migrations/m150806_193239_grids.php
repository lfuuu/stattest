<?php

class m150806_193239_grids extends \app\classes\Migration
{
    public function up()
    {
        $this->executeSqlFile('grid.sql');
    }

    public function down()
    {
        echo "m150806_193239_grids cannot be reverted.\n";

        return false;
    }
}