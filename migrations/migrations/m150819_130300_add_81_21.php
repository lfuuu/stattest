<?php

class m150819_130300_add_81_21 extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            INSERT INTO `city` (`id`, `name`, `country_id`, `connection_point_id`, `voip_number_format`) VALUES (3621,'LIECS numbers',348,81,'36 21 000-000');
        ");

        $this->execute("
            INSERT INTO `did_group` (`id`, `name`, `city_id`, `beauty_level`) VALUES (58,'Стандартные',3621,0);
        ");


        $this->execute("
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`)
            VALUES (58,348,'HUF',3621,81,'Standard','public',0.00,0.00,'month',58,NULL,NULL);
        ");



        $this->executeSqlFile("numbers.sql");

    }

    public function down()
    {
        echo "m150819_130300_add_81_21 cannot be reverted.\n";

        return false;
    }
}
