<?php

namespace app\forms\user;

use yii\db\Query;
use app\forms\user\UserForm;
use app\classes\Form;
use app\models\User;

class UserListForm extends UserForm
{

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        //'select u.*, d.name as depart_name from user_users u left join user_departs d on (d.id = u.depart_id)'.($group?' where usergroup in ("'.implode("\",\"",$group).'")':'').' and enabled = "yes" order by u.name'

        return User::find()->orderBy('name asc');
    }

    public function applyFilter(Query $query)
    {
        /*if ($this->cityId) {
            $query->andWhere(['voip_numbers.city_id' => $this->cityId]);
        }*/
    }

}
