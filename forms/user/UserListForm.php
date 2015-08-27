<?php

namespace app\forms\user;

use yii\db\Query;
use app\forms\user\UserForm;
use app\classes\Form;
use app\models\User;

class UserListForm extends UserForm
{

    public
        $depart_id,
        $usergroup,
        $user,
        $name;

    public function attributeLabels()
    {
        return [
            'depart_id' => 'Отдел',
        ];
        //return parent::attributeLabels();
    }

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        return User::find()->orderBy('name asc');
    }

    public function applyFilter(Query $query)
    {
        if ($this->depart_id) {
            $query->andWhere(['depart_id' => $this->depart_id]);
        }
        if ($this->usergroup) {
            $query->andWhere(['usergroup' => $this->usergroup]);
        }
        if ($this->name) {
            $query->andWhere(['depart_id' => $this->depart_id]);
        }
        if ($this->depart_id) {
            $query->andWhere(['depart_id' => $this->depart_id]);
        }
    }

}
