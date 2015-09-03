<?php

class m150903_081652_hu_number_did_group_update extends \app\classes\Migration
{
    public function up()
    {
        // Будапешт
        $this->execute("
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Платиновые', 361, 1);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Золотые', 361, 2);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Серебряные', 361, 3);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Бронзовые', 361, 4);
        ");
        $this->execute("
            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 361 AND `beauty_level` = 1)
            WHERE `city_id` = 361 AND `beauty_level` = 1;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 361 AND `beauty_level` = 2)
            WHERE `city_id` = 361 AND `beauty_level` = 2;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 361 AND `beauty_level` = 3)
            WHERE `city_id` = 361 AND `beauty_level` = 3;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 361 AND `beauty_level` = 4)
            WHERE `city_id` = 361 AND `beauty_level` = 4;
        ");

        // Мишкольц
        $this->execute("
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Платиновые', 3646, 1);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Золотые', 3646, 2);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Серебряные', 3646, 3);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Бронзовые', 3646, 4);
        ");
        $this->execute("
            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3646 AND `beauty_level` = 1)
            WHERE `city_id` = 3646 AND `beauty_level` = 1;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3646 AND `beauty_level` = 2)
            WHERE `city_id` = 3646 AND `beauty_level` = 2;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3646 AND `beauty_level` = 3)
            WHERE `city_id` = 3646 AND `beauty_level` = 3;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3646 AND `beauty_level` = 4)
            WHERE `city_id` = 3646 AND `beauty_level` = 4;
        ");

        // Дебрецен
        $this->execute("
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Платиновые', 3652, 1);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Золотые', 3652, 2);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Серебряные', 3652, 3);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Бронзовые', 3652, 4);
        ");
        $this->execute("
            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3652 AND `beauty_level` = 1)
            WHERE `city_id` = 3652 AND `beauty_level` = 1;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3652 AND `beauty_level` = 2)
            WHERE `city_id` = 3652 AND `beauty_level` = 2;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3652 AND `beauty_level` = 3)
            WHERE `city_id` = 3652 AND `beauty_level` = 3;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3652 AND `beauty_level` = 4)
            WHERE `city_id` = 3652 AND `beauty_level` = 4;
        ");

        // Сегед
        $this->execute("
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Платиновые', 3662, 1);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Золотые', 3662, 2);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Серебряные', 3662, 3);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Бронзовые', 3662, 4);
        ");
        $this->execute("
            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3662 AND `beauty_level` = 1)
            WHERE `city_id` = 3662 AND `beauty_level` = 1;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3662 AND `beauty_level` = 2)
            WHERE `city_id` = 3662 AND `beauty_level` = 2;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3662 AND `beauty_level` = 3)
            WHERE `city_id` = 3662 AND `beauty_level` = 3;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3662 AND `beauty_level` = 4)
            WHERE `city_id` = 3662 AND `beauty_level` = 4;
        ");

        // Печ
        $this->execute("
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Платиновые', 3672, 1);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Золотые', 3672, 2);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Серебряные', 3672, 3);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Бронзовые', 3672, 4);
        ");
        $this->execute("
            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3672 AND `beauty_level` = 1)
            WHERE `city_id` = 3672 AND `beauty_level` = 1;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3672 AND `beauty_level` = 2)
            WHERE `city_id` = 3672 AND `beauty_level` = 2;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3672 AND `beauty_level` = 3)
            WHERE `city_id` = 3672 AND `beauty_level` = 3;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3672 AND `beauty_level` = 4)
            WHERE `city_id` = 3672 AND `beauty_level` = 4;
        ");

        // Дьёр
        $this->execute("
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Платиновые', 3696, 1);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Золотые', 3696, 2);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Серебряные', 3696, 3);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Бронзовые', 3696, 4);
        ");
        $this->execute("
            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3696 AND `beauty_level` = 1)
            WHERE `city_id` = 3696 AND `beauty_level` = 1;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3696 AND `beauty_level` = 2)
            WHERE `city_id` = 3696 AND `beauty_level` = 2;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3696 AND `beauty_level` = 3)
            WHERE `city_id` = 3696 AND `beauty_level` = 3;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3696 AND `beauty_level` = 4)
            WHERE `city_id` = 3696 AND `beauty_level` = 4;
        ");

        // LIECS numbers
        $this->execute("
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Платиновые', 3621, 1);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Золотые', 3621, 2);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Серебряные', 3621, 3);
            INSERT INTO `did_group` (`name`, `city_id`, `beauty_level`) VALUES ('Бронзовые', 3621, 4);
        ");
        $this->execute("
            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3621 AND `beauty_level` = 1)
            WHERE `city_id` = 3621 AND `beauty_level` = 1;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3621 AND `beauty_level` = 2)
            WHERE `city_id` = 3621 AND `beauty_level` = 2;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3621 AND `beauty_level` = 3)
            WHERE `city_id` = 3621 AND `beauty_level` = 3;

            UPDATE `voip_numbers` SET
                `did_group_id` = (SELECT `id` FROM `did_group` WHERE `city_id` = 3621 AND `beauty_level` = 4)
            WHERE `city_id` = 3621 AND `beauty_level` = 4;
        ");

    }

    public function down()
    {
        echo "m150903_081652_hu_number_did_group_update cannot be reverted.\n";

        return false;
    }
}