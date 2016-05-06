<?php
namespace app\classes\behaviors\message;

use Yii;
use yii\base\Event;
use yii\db\AfterSaveEvent;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\message\TemplateEvents;
use app\models\message\TemplateContent;

class MessageTemplateEvent extends Behavior
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
    }

}