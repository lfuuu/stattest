<?php

class m151111_113546_actual_virtpbx_region extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
                ALTER TABLE `actual_virtpbx`
                    ADD COLUMN `region_id` INT(11) NULL DEFAULT NULL;
        ');

        $this->execute('
            UPDATE `actual_virtpbx` av
                LEFT JOIN `usage_virtpbx` uv ON uv.`id` = av.`usage_id`
            SET
                av.`region_id` = uv.`region`;
        ');
    }

    public function down()
    {
        echo "m151111_113546_actual_virtpbx_region cannot be reverted.\n";

        return false;
    }
}