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

    public function save($destination = false)
    {
        if (!($destination instanceof Destination))
            $destination = new Destination;
        $destination->setAttributes($this->getAttributes(), false);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $destination->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            DestinationPrefixes::deleteAll(['destination_id' => $destination->id]);

            foreach ($this->prefixes as $prefixId) {
                $link = new DestinationPrefixes;
                $link->destination_id = $destination->id;
                $link->prefix_id = $prefixId;
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