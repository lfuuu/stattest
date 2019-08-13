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
            ->distinct()
            ->select('organization_id')
            ->with('actual')
            ->all();

        foreach ($organizations as $organization) {
            /** @var Organization $actual */
            $actual = $organization->actual;

            if ($actual instanceof Organization) {
                $result[$actual->organization_id] = $actual;
            } else {
                $result[$organization->organization_id] = $organization;
            }
        }

        return $result;
    }
}