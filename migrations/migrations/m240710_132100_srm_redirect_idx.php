<?php


/**
 * Class m240710_132100_srm_redirect_idx
 */
class m240710_132100_srm_redirect_idx extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn('sorm_redirect_ranges', 'id', $this->primaryKey());

        $sql = <<<SQL
CREATE TRIGGER sorm_redirect_ranges_after_ins_tr
    after insert
    on sorm_redirect_ranges
    for each row
BEGIN
     insert into z_model_life_log (model, model_id, action, created_at) values ('redirect_ranges', NEW.id, 'insert', NOW());
END;

SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE TRIGGER sorm_redirect_ranges_after_upd_tr
    after update
    on sorm_redirect_ranges
    for each row
BEGIN
     insert into z_model_life_log (model, model_id, action, created_at) values ('redirect_ranges', NEW.id, 'update', NOW());
END;

SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE TRIGGER sorm_redirect_ranges_before_upd_tr
    before delete
    on sorm_redirect_ranges
    for each row
BEGIN
     insert into z_model_life_log (model, model_id, action, created_at) values ('redirect_ranges', OLD.id, 'delete', NOW());
END;

SQL;
        $this->execute($sql);

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn('sorm_redirect_ranges', 'id');
        $this->execute('DROP TRIGGER IF EXISTS sorm_redirect_ranges_after_ins_tr');
        $this->execute('DROP TRIGGER IF EXISTS sorm_redirect_ranges_after_upd_tr');
        $this->execute('DROP TRIGGER IF EXISTS sorm_redirect_ranges_before_upd_tr');
    }
}
