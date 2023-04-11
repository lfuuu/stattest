<?php

use app\classes\Migration;
use app\modules\sbisTenzor\models\SBISContractor;

/**
 * Class m230411_093259_brach_code_expand
 */
class m230411_093259_brach_code_expand extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(SBISContractor::tableName(), 'branch_code',  $this->string(8));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(SBISContractor::tableName(), 'branch_code',  $this->string(3));
    }

}