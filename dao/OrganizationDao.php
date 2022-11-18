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


    /**
     * @param int $contractId
     * @return array
     */
    function getWhenOrganizationSwitched($contractId)
    {
        $result = [];
        $lastOrganizationId = 0;

        foreach (
            \app\models\HistoryVersion::find()
                ->andWhere(['regexp', 'data_json', '"organization_id":"?[0-9]+"?,'])
                ->andWhere(['model' => 'app\\models\\ClientContract', 'model_id' => $contractId])
                ->orderBy(['date' => SORT_ASC])
                ->all() as $record
        ) {
            $historyData = json_decode($record->data_json, true);
            if ($lastOrganizationId != $historyData['organization_id']) {
                $result[$record->date] = $historyData['organization_id'];
                $lastOrganizationId = $historyData['organization_id'];
            }
        }

        return $result;
    }
}