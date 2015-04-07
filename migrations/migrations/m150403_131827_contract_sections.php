<?php

use app\models\Contract;

class m150403_131827_contract_sections extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `client_contracts` 
            ADD COLUMN `type` enum('blank','agreement','contract') NOT NULL DEFAULT 'contract' AFTER `is_active`,
            ADD COLUMN `contract_dop_no`  int NOT NULL DEFAULT 0 AFTER `contract_dop_date`;
                ");

        $this->execute("update client_contracts set `type` = 'agreement' where contract_dop_date != '2012-01-01'");

        $this->execute("CREATE TABLE `contract` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `type` enum('blank','agreement','contract') NOT NULL DEFAULT 'contract',
            PRIMARY KEY (`id`),
            INDEX `name` (`name`) 
        ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
        ");

        $storePath = realpath(Yii::$app->basePath."/../") . "/store/";

        if(is_dir($storePath))
        {
            if (is_dir($storePath."contracts/"))
            {
                if (is_readable($storePath."contracts/"))
                {
                    foreach (glob($storePath.'contracts/template_*.html') as $s) 
                    {
                        $t = str_replace(array('template_','.html'),array('',''),basename($s));

                        echo "\nadd: ".$t." as contract";

                        $c = new Contract();
                        $c->name = $t;
                        $c->type = "contract";
                        $c->save();
                    }
                } else {
                    echo "\n !!! is_readable(STORE_PATH.contracts/)";
                }
            } else {
                echo "\n !!! is_dir(STORE_PATH.contracts/)";
            }
        } else {
            echo "\n !!! is_dir(STORE_PATH)";
        }
    }


    public function down()
    {
        echo "m150403_131827_contract_sections cannot be reverted.\n";

        return false;
    }
}
