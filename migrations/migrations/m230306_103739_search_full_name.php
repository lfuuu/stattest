<?php

use app\classes\Migration;
use app\models\ClientContragent;
/**
 * Class m230306_103739_search_full_name
 */
class m230306_103739_search_full_name extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->execute("create fulltext index ft1 on " . ClientContragent::tableName() . " (name_full)");
        $this->execute("create fulltext index ft2 on " . ClientContragent::tableName() . " (name)");
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->execute("drop index ft1 on " . ClientContragent::tableName());
        $this->execute("drop index ft2 on " . ClientContragent::tableName());
    }
}
