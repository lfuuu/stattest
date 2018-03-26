<?php

/**
 * Class m180323_095531_add_export_free_number
 */
class m180323_095531_add_export_free_number extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $statusInStock = \app\models\Number::STATUS_INSTOCK;
        $eventFree = \app\modules\freeNumber\Module::EVENT_EXPORT_FREE;
        $eventBusy = \app\modules\freeNumber\Module::EVENT_EXPORT_BUSY;

        $sql = <<<SQL
CREATE TRIGGER `export_free_number_insert` AFTER INSERT ON `voip_numbers` FOR EACH ROW BEGIN
                IF NEW.status = "{$statusInStock}" THEN
                    call add_event("{$eventFree}", NEW.number);
                END IF;
            END;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE TRIGGER `export_free_number_update` AFTER UPDATE ON `voip_numbers` FOR EACH ROW BEGIN
                IF OLD.status <> "{$statusInStock}" AND NEW.status = "{$statusInStock}" THEN
                    call add_event("{$eventFree}", NEW.number);
                ELSEIF OLD.status = "{$statusInStock}" AND NEW.status <> "{$statusInStock}" THEN
                    call add_event("{$eventBusy}", NEW.number);
                END IF;
            END;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE TRIGGER `export_free_number_delete` AFTER DELETE ON `voip_numbers` FOR EACH ROW BEGIN
                IF OLD.status = "{$statusInStock}" THEN
                    call add_event("{$eventBusy}", OLD.number);
                END IF;
            END;
SQL;
        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->execute('DROP TRIGGER IF EXISTS `export_free_number_insert`');
        $this->execute('DROP TRIGGER IF EXISTS `export_free_number_update`');
        $this->execute('DROP TRIGGER IF EXISTS `export_free_number_delete`');
    }
}
