<?php

class m150624_150754_stat_report_cache extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("CREATE TABLE `stat_voip_free_cache` (
              `number` varchar(16) NOT NULL,
              `month` tinyint(4) NOT NULL,
              `calls` smallint(6) NOT NULL,
              KEY `number` (`number`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    }

    public function down()
    {
        echo "m150624_150754_stat_report_cache cannot be reverted.\n";

        return false;
    }
}
