<?php
namespace app\forms\voip\destination;

use app\classes\Form;
use app\models\voip\Destination;
use app\models\voip\DestinationPrefixes;
use app\models\voip\Prefixlist;

class DestinationForm extends Form
{

    public
        $id,
        $name,
        $prefixes = [];

    public function rules()
    {
        return [
            [['name',], 'required'],
            ['prefixes', 'each', 'rule' => ['integer']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'prefixes' => 'Списки префиксов',
        ];
    }

    public function save(Destination $destination = null)
    {
        if ($destination === null) {
            $destination = new Destination;
        }
        $destination->setAttributes($this->getAttributes(), false);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $destination->save();

            DestinationPrefixes::deleteAll(['destination_id' => $destination->id]);

            foreach ($this->prefixes as $prefixlistId) {
                $link = new DestinationPrefixes;
                $link->destination_id = $destination->id;
                $link->prefixlist_id = $prefixlistId;
                $link->save();
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->id = $destination->id;

        return true;
    }

    public function delete(Destination $destination)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            DestinationPrefixes::deleteAll(['destination_id' => $destination->id]);

            $destination->delete();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}