<?php
namespace app\classes\behaviors\message;

use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
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
     * @param ModelEvent $event
     * @return bool
     * @throws \yii\db\Exception
     */
    public function setMessageTemplateEvent($event)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            TemplateEvents::deleteAll(['template_id' => $event->sender->id]);

            $link = new TemplateEvents;
            $link->template_id = $event->sender->id;
            $link->event_code = $event->sender->event;
            $link->save();

            $transaction->commit();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }

        return true;
    }

    /**
     * @param ModelEvent $event
     */
    public function unsetMessageTemplateEvent($event)
    {
        TemplateContent::deleteAll(['template_id' => $event->sender->id]);
        TemplateEvents::deleteAll(['template_id' => $event->sender->id]);
    }

}