<?php
namespace app\forms\usage;

use app\classes\Assert;
use app\models\UsageTrunkSettings;
use Yii;

/**
 * Class UsageTrunkSettingsEditForm
 */
class UsageTrunkSettingsEditForm extends UsageTrunkSettingsForm
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
     */
    public function process()
    {
        $item = UsageTrunkSettings::findOne($this->id);
        /** @var UsageTrunkSettings $item */
        Assert::isObject($item);
        Assert::isTrue($item->usage->isActive());

        if ($item->type == UsageTrunkSettings::TYPE_DESTINATION) {
            if ($this->dst_number_id) {
                $this->_save($item);
            } else {
                $this->_delete($item);
            }
        } else {
            if ($this->pricelist_id || $this->package_id) {
                $this->_save($item);
            } else {
                $this->_delete($item);
            }
        }

        return true;
    }

    /**
     * @param UsageTrunkSettings $item
     * @throws \Exception
     */
    private function _save(UsageTrunkSettings $item)
    {
        $item->src_number_id = $this->src_number_id;
        $item->dst_number_id = $this->dst_number_id;
        $item->pricelist_id = $this->pricelist_id;
        $item->package_id = $this->package_id;
        if ($item->type == UsageTrunkSettings::TYPE_DESTINATION) {
            $item->minimum_minutes = 0;
            $item->minimum_cost = 0;
            $item->minimum_margin_type = UsageTrunkSettings::MIN_MARGIN_ABSENT;
            $item->minimum_margin = 0;
        } else {
            $item->minimum_minutes = $this->minimum_minutes;
            $item->minimum_cost = $this->minimum_cost;

            $item->minimum_margin_type = $this->minimum_margin_type;

            if ($item->minimum_margin_type == UsageTrunkSettings::MIN_MARGIN_ABSENT) {
                $item->minimum_margin = 0;
            } else {
                $item->minimum_margin = $this->minimum_margin;
            }
        }


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

    /**
     * @param UsageTrunkSettings $item
     * @throws \Exception
     */
    private function _delete(UsageTrunkSettings $item)
    {
        $item = UsageTrunkSettings::findOne($this->id);
        /** @var UsageTrunkSettings $item */
        Assert::isObject($item);

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
    }

}
