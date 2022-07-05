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
        if (!defined('PATH_TO_ROOT')) {
            define("PATH_TO_ROOT", \Yii::$app->basePath . '/stat/');
        }


        if (!defined('DESIGN_PATH')) {
            include_once PATH_TO_ROOT . 'conf.php';
        }

        if ($this->db === null) {
            $this->db = new \MySQLDatabase_yii(Yii::$app->getDb());
        }

        return $this->db;
    }
}