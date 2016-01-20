<?php

class m160120_101958_virtpbx_recreate_primary extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            ALTER TABLE `virtpbx_stat`
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (`client_id`, `usage_id`, `date`);
        ');
    }

    public function down()
    {
        echo "m160120_101958_virtpbx_recreate_primary cannot be reverted.\n";

        return false;
    }
}