<?php

/**
 * Class m200422_142548_mysq_fn_cleaner
 */
class m200422_142548_mysq_fn_cleaner extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->execute('DROP FUNCTION IF EXISTS `clean_str`');
        $sql = <<<SQL
CREATE FUNCTION `clean_str`(str VARCHAR(1024))
  RETURNS VARCHAR(1024)
DETERMINISTIC
READS SQL DATA
  BEGIN

    RETURN
    replace(
        replace(
            replace(
                replace(
                    replace(
                        trim(
                            str
                        ),
                        '\n', ' '),
                    '\r', ' '),
                '\t', ' '),
            '  ', ' '),
        '  ', ' ');
  END;


SQL;

        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->execute('DROP FUNCTION IF EXISTS `clean_str`');
    }
}
