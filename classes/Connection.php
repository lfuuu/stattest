<?php
namespace app\classes;

class Connection extends \yii\db\Connection
{
    public $initQuery;

    protected function initConnection()
    {
        parent::initConnection();

        if ($this->initQuery) {
            $this->createCommand($this->initQuery)->execute();
        }
    }

}