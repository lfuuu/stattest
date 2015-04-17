<?php
namespace app\forms\usage;

use app\classes\Assert;
use app\models\UsageTrunkSettings;
use Yii;

class UsageTrunkSettingsDeleteForm extends UsageTrunkSettingsForm
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

        return true;
    }
}