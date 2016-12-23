<?php
namespace app\forms\usage;

use app\classes\Assert;
use app\models\UsageTrunkSettings;
use Yii;

/**
 * Class UsageTrunkSettingsDeleteForm
 */
class UsageTrunkSettingsDeleteForm extends UsageTrunkSettingsForm
{
    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['id'], 'required'];
        return $rules;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function process()
    {
        $item = UsageTrunkSettings::findOne($this->id);
        /** @var UsageTrunkSettings $item */
        Assert::isObject($item);
        Assert::isTrue($item->usage->isActive());

        /** @var UsageTrunkSettings[] $nextRules */
        $nextRules = UsageTrunkSettings::find()
            ->andWhere(['usage_id' => $this->usage_id, 'type' => $this->type])
            ->andWhere('`order` > :order', [':order' => $item->order])
            ->all();

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