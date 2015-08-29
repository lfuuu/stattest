<?php

namespace app\forms\user;

use app\models\UserGrantGroups;
use Yii;
use yii\db\Query;
use app\classes\Form;
use app\classes\validators\ArrayValidator;
use app\models\UserGroups;

class GroupForm extends Form
{

    public
        $id,
        $usergroup,
        $comment;

    public $initModel, $rights;

    public function rules()
    {
        return [
            [
                ['id', 'usergroup', 'comment',],
                'string'
            ],
            [
                ['usergroup', 'comment',],
                'required'
            ],
            ['usergroup', 'validateExistsGroup'],
            ['rights', ArrayValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'usergroup' => 'Группа',
            'comment' => 'Комментарий',
        ];
    }

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        return UserGroups::find()->orderBy('usergroup asc');
    }

    public function initModel(UserGroups $group)
    {
        $this->initModel = $group;
        $this->setAttributes($group->getAttributes(), false);
        return $this;
    }

    public function validateExistsGroup($model)
    {
        if ($this->{$model} != $this->id) {
            $group = UserGroups::findOne(['usergroup' => $this->{$model}]);
            if ($group instanceof UserGroups && $group->usergroup)
                $this->addError($model, 'Группа уже существует');
        }
    }

    public function save($group = false)
    {
        if (!($group instanceof UserGroups))
            $group = new UserGroups;
        $group->setAttributes($this->getAttributes(), false);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $group->save();

            if (Yii::$app->user->can('users.grant') && count($this->rights)) {
                UserGrantGroups::setRights($group, $this->rights);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    public function delete(UserGroups $group)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            UserGrantGroups::deleteAll(['name' => $group->usergroup]);

            $group->delete();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}

