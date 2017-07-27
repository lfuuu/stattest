<?php

namespace app\modules\notifier\behaviors\templates;

use app\classes\model\ActiveRecord;
use app\modules\notifier\models\templates\TemplateContent;
use app\modules\notifier\models\templates\TemplateEvents;
use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\AfterSaveEvent;

class TemplateEvent extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'setMessageTemplateEvent',
            ActiveRecord::EVENT_AFTER_UPDATE => 'setMessageTemplateEvent',
            ActiveRecord::EVENT_AFTER_DELETE => 'unsetMessageTemplateEvent',
        ];
    }

    /**
     * @param AfterSaveEvent $event
     * @return bool
     * @throws \yii\db\Exception
     */
    public function setMessageTemplateEvent(AfterSaveEvent $event)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            TemplateEvents::deleteAll(['template_id' => $event->sender->id]);

            $link = new TemplateEvents;
            $link->template_id = $event->sender->id;
            $link->event_code = $event->sender->event;
            $link->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }

        return true;
    }

    /**
     * @param Event $event
     * @return bool
     * @throws \yii\db\Exception
     */
    public function unsetMessageTemplateEvent(Event $event)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            TemplateContent::deleteAll(['template_id' => $event->sender->id]);
            TemplateEvents::deleteAll(['template_id' => $event->sender->id]);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }

        return true;
    }

}
