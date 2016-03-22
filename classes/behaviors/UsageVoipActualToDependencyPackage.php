<?php

namespace app\classes\behaviors;

use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

/**
 * Class UsageVoipActualToDependencyPackage
 * @package app\classes\behaviors
 */
class UsageVoipActualToDependencyPackage extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'setDependencyActualToDate',
        ];
    }

    /**
     * Устанавливает дату закрытия пакета в соответствии с датой закрытия услуги
     *
     * @param AfterSaveEvent $event
     * @throws \Exception
     * @return null|bool
     */
    public function setDependencyActualToDate($event)
    {
        if (!isset($event->changedAttributes['actual_to'])) {
            return true;
        }

        $oldActualTo = $event->changedAttributes['actual_to'];
        $newActualTo = $event->sender->actual_to;

        if ($event->sender instanceof UsageVoip) {
            /** @var UsageVoip $usage */
            $usage = $event->sender;
            /** @var UsageVoipPackage $usagePackage */
            foreach ($usage->getPackages()
                         ->andWhere(['>=', 'actual_to', $oldActualTo])
                         ->all() as $usagePackage) {
                $usagePackage->actual_to = $newActualTo;
                $usagePackage->save();
            }
        }

        return true;
    }
}
