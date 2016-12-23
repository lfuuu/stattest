<?php
namespace app\forms\usage;

use app\classes\Assert;
use app\models\UsageTrunk;
use app\models\UsageTrunkSettings;
use Yii;

/**
 * Class UsageTrunkSettingsAddForm
 */
class UsageTrunkSettingsAddForm extends UsageTrunkSettingsForm
{
    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['usage_id', 'type'], 'required'];
        return $rules;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function process()
    {
        $usage = UsageTrunk::findOne($this->usage_id);
        /** @var UsageTrunk $usage */
        Assert::isObject($usage);
        Assert::isTrue($usage->isActive());

        $maxOrder = UsageTrunkSettings::find()
            ->andWhere(['usage_id' => $this->usage_id, 'type' => $this->type])
            ->max('`order`');

        $item = new UsageTrunkSettings();
        $item->usage_id = $this->usage_id;
        $item->type = $this->type;
        $item->order = $maxOrder + 1;

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $item->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->id = $item->id;

        return true;
    }
}