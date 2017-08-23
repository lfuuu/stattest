<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\Organization;

/**
 * @method static OrganizationDao me($args = null)
 */
class OrganizationDao extends Singleton
{

    /**
     * @param bool $isWithEmpty
     * @return string[]|Organization[]
     */
    public function getList($isWithEmpty = false)
    {
        $result = [];
        if ($isWithEmpty) {
            $result = ['' => '----'];
        }

        /** @var Organization[] $organizations */
        $organizations = Organization::find()
            ->select('organization_id')
            ->groupBy('organization_id')
            ->orderBy(['actual_from' => SORT_ASC])
            ->all();
        foreach ($organizations as $organization) {

            /** @var Organization $actual */
            $actual = Organization::find()->byId($organization->organization_id)->actual()->one();

            if ($actual instanceof Organization) {
                $result[$actual->organization_id] = $actual;
            } else {
                $result[$organization->organization_id] = $organization;
            }
        }

        return $result;
    }
}