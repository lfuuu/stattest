<?php

namespace app\classes;

use Yii;

class StatDbDriver extends Singleton
{
    private $db = null;

    /**
     * @return \MySQLDatabase_yii|null
     */
    public function getDbDriver()
    {
        if ($this->db === null) {
            $this->db = new \MySQLDatabase_yii(Yii::$app->getDb());
        }

        return $this->db;
    }
}