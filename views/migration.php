<?php
/* @var $className string the new migration class name */
echo "<?php\n";
?>

/**
* Class <?= $className ?>
*/
class <?= $className ?> extends \app\classes\Migration
{
    /**
    * Up
    */
    public function safeUp()
    {
    }

    /**
    * Down
    */
    public function safeDown()
    {
    }
}
