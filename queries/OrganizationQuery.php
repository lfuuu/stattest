<?php
namespace app\queries;

use DateTime;
use DateTimeZone;
use yii\db\ActiveQuery;

class OrganizationQuery extends ActiveQuery
{

    public function actual($date = '')
    {
        $date = $this->resolveDate($date);
        if (!is_null($date))
            $date = date('Y-m-d', $date);

        $filter_date =
            (new DateTime($date))
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d');

        return
            $this
                ->select('organization.*')
                ->leftJoin('organization o2', 'organization.`organization_id` = o2.`organization_id` and organization.`actual_from` = o2.`actual_from`')
                ->andWhere('organization.`actual_from` <= CAST(:date AS date)', [':date' => $filter_date])
                ->andWhere('o2.`actual_to` >= CAST(:date AS date)', [':date' => $filter_date])
                ->orderBy('organization.`actual_from` DESC');
    }

    /**
     * @param int $id
     * @return static
     */
    public function byId($id)
    {
        return
            $this
                ->andWhere('organization.`organization_id` = :id', [':id' => $id]);
    }

    /**
     * @param $person
     * @return static
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

    private static function resolveDate($bill_or_time)
    {
        $billDate = null;

        if (is_array($bill_or_time) && isset($bill_or_time["bill_date"])) {
            $billDate = strtotime($bill_or_time["bill_date"]);
        }
        elseif (preg_match("/^\d+$/", $bill_or_time)) { //timestamp
            $billDate = $bill_or_time;
        }
        elseif (preg_match("/\d{4}-\d{2}-\d{2}/", $bill_or_time)) { // date
            $billDate = strtotime($bill_or_time);
        }

        return $billDate;
    }

}
