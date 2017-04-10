<?php
namespace app\classes\behaviors;

use app\modules\uu\models\AccountTariff;
use app\models\UsageTrunk;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Установить UsageTrunk->Id
 */
class UsageTrunkId extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'setId',
        ];
    }

    /**
     * Установить ID
     *
     * @param Event $event
     */
    public function setId(Event $event)
    {
        /** @var UsageTrunk $sender */
        $sender = $event->sender;
        if ($sender->id) {
            // уже есть, ничего нового не надо
            return;
        }

        // autoincrement БД нельзя использовать, чтобы не пересекались старые "логические транки" и УУ транков
        // поэтому надо autoincrement с педальным приводом
        $sender->id = 1 +
            ((int)UsageTrunk::find()
                ->select(['id' => new Expression('MAX(id)')])
                ->where(['<', 'id', AccountTariff::DELTA])
                ->scalar());
    }

}
