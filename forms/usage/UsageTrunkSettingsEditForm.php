<?php
namespace app\forms\usage;

use app\classes\Assert;
use app\models\UsageTrunkSettings;
use Yii;

class UsageTrunkSettingsEditForm extends UsageTrunkSettingsForm
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['id'], 'required'];
        return $rules;
    }

    public function process()
    {
        $item = UsageTrunkSettings::findOne($this->id); /** @var UsageTrunkSettings $item */
        Assert::isObject($item);
        Assert::isTrue($item->usage->isActive());

        if ($item->type == UsageTrunkSettings::TYPE_DESTINATION) {
            if ($this->dst_number_id) {
                $this->save($item);
            } else {
                $this->delete($item);
            }
        } else {
            if ($this->pricelist_id) {
                $this->save($item);
            } else {
                $this->delete($item);
            }
        }

        return true;
    }

    private function save(UsageTrunkSettings $item)
    {
        $item->src_number_id = $this->src_number_id;
        $item->dst_number_id = $this->dst_number_id;
        $item->pricelist_id = $this->pricelist_id;

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $item->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->id = $item->id;
    }

    private function delete(UsageTrunkSettings $item)
    {
        $item = UsageTrunkSettings::findOne($this->id); /** @var UsageTrunkSettings $item */
        Assert::isObject($item);


        $nextRules =
            UsageTrunkSettings::find()
                ->andWhere(['usage_id' => $this->usage_id, 'type' => $this->type])
                ->andWhere('`order` > :order', [':order' => $item->order])
                ->all(); /** @var UsageTrunkSettings[] $nextRules */

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $item->delete();

            foreach ($nextRules as $nextRule) {
                $nextRule->order -= 1;
                $nextRule->save();
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

}