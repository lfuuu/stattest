<?php

/**
 * Class m221004_142519_sorm_did_length
 */
class m221004_142519_sorm_did_length extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn('sorm_redirects', 'did', $this->string(32));
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
