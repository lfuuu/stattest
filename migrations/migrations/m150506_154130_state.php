<?php

class m150506_154130_state extends \app\classes\Migration
{
    public function up()
    {
        $this->execute(
"UPDATE `tt_types` SET folders = 70231305224193, states = 70231305224193 WHERE pk = 8

INSERT INTO `tt_states` (`pk`, `name`, `folder`, `deny`) VALUES (35184372088832, 'Проверка документов', 35184372088833, 29274497089536);
UPDATE `tt_states` SET deny = deny + 35184372088832 WHERE id IN (
	select id from tt_states 
	where pk & ( select states from tt_types where code=\"connect\" ) 
	AND id NOT IN (41,42)
) AND pk != 35184372088832


INSERT INTO `tt_folders` (`pk`, `name`, `order`) VALUES (35184372088832, 'Проверка документов', 27);"
                );
    }

    public function down()
    {
        echo "m150506_154130_state cannot be reverted.\n";

        return false;
    }
}