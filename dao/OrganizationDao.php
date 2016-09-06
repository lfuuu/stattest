<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\Organization;

class OrganizationDao extends Singleton
{

    /**
     * @param bool|false $isWithEmpty
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getList($isWithEmpty = false)
    {
        $list =
            Organization::find()
                ->select('organization_id')
                ->groupBy('organization_id')
                ->orderBy(['actual_from' => SORT_ASC])
                ->all();

        $result = [];
        if ($isWithEmpty) {
            $result = ['' => '----'];
        }

        foreach ($list as $organization) {
            $actual = Organization::find()->byId($organization->organization_id)->actual()->one();
            $result[$actual->organization_id] = $actual->name;
        }

        return $result;
    }


    /**
     * @return array
     */
    public function getCompleteList()
    {
        $result = [];

        $records =
            Organization::find()
                ->select('organization_id')
                ->groupBy('organization_id')
                ->orderBy(['actual_from' => SORT_ASC])
                ->all();

        foreach ($records as $record) {
            $actual = Organization::find()->byId($record->organization_id)->actual()->one();
            if ($actual instanceof Organization) {
                $result[] = $actual;
            } else {
                $result[] = $record;
            }
        }

        return $result;
    }

}