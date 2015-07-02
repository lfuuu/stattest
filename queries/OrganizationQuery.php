<?php
namespace app\queries;

use DateTime;
use DateTimeZone;
use yii\db\ActiveQuery;

class OrganizationQuery extends ActiveQuery
{

    public function actual($date = '')
    {
        $filter_date =
            (new DateTime($date))
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d');

        return
            $this
                ->select('organization.*')
                ->leftJoin('organization o2', 'organization.`id` = o2.`id` and organization.`actual_from` = o2.`actual_from`')
                ->andWhere('organization.`actual_from` <= CAST(:date AS date)', [':date' => $filter_date])
                ->andWhere('o2.`actual_to` >= CAST(:date AS date)', [':date' => $filter_date])
                ->orderBy('organization.`actual_from` DESC');
    }

    /**
     * @param int $id
     */
    public function byId($id)
    {
        return
            $this
                ->andWhere('organization.`id` = :id', [':id' => $id]);
    }

    /**
     * @param int $person
     */
    public function byPerson($person)
    {
        return
            $this
                ->andFilterWhere(['or',
                    ['=', 'organization.`director_id`', $person],
                    ['=', 'organization.`accountant_id`', $person]
                ]);
    }

}
