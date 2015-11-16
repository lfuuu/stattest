<?php
/* @var $className string the new migration class name */
echo "<?php\n";
?>

class <?= $className ?> extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("

        ");
    }

    public function down()
    {
        echo "<?= $className ?> cannot be reverted.\n";

        return false;
    }
}