<?php

class m150703_105924_person_update extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `person`
                CHANGE COLUMN `name_nominativus` `name_nominative` VARCHAR(250) NOT NULL AFTER `id`,
                CHANGE COLUMN `name_genitivus` `name_genitive` VARCHAR(150) NOT NULL AFTER `name_nominative`,
                CHANGE COLUMN `post_nominativus` `post_nominative` VARCHAR(150) NOT NULL AFTER `name_genitive`,
                CHANGE COLUMN `post_genitivus` `post_genitive` VARCHAR(250) NOT NULL AFTER `post_nominative`;
        ");
    }

    public function down()
    {
        echo "m150703_105924_person_update cannot be reverted.\n";

        return false;
    }
}