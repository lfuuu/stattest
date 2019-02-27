<?php

/**
 * Class m190226_115150_add_event_procedure
 */
class m190226_115150_add_event_procedure extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->execute('DROP PROCEDURE IF EXISTS `add_event`');

        $sql = <<<SQL
CREATE PROCEDURE `add_event`(IN `__event` VARCHAR(32), IN `__param` VARCHAR(255))
  BEGIN
    DECLARE _code CHAR(32);
    DECLARE _id INT(4);
    SET _code = md5(concat(__event, "|||", __param));
    
    SELECT id
    INTO _id
    FROM event_queue
    WHERE code = _code AND status NOT IN ('ok', 'stop')
    LIMIT 1;

    IF _id IS NULL
    THEN
      INSERT INTO event_queue (event, param, code, next_start, insert_time)
      VALUES (__event, __param, _code, NOW(), NOW() - INTERVAL 3 HOUR);
    ELSE
      UPDATE event_queue
      SET status = 'plan', iteration = 0, next_start = NOW()
      WHERE id = _id;
    END IF;

  END
SQL;

        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        return $this->safeUp();
    }
}
