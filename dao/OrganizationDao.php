<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\Organization;

class OrganizationDao extends Singleton
{

    public function getCompleteList()
    {
        $result = [];

        $records = Organization::find()
            ->groupBy('id')
            ->orderBy('actual_from ASC')
            ->all();

        foreach ($records as $record) {
            $actual = Organization::find()->byId($record->id)->actual()->one();
            if ($actual instanceof Organization) {
                $result[] = $actual;
            }
            else {
                $result[] = $record;
            }
        }

        return $result;
    }

}