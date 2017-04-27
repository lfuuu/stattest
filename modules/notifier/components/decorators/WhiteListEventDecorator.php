<?php

namespace app\modules\notifier\components\decorators;

use app\classes\Html;
use yii\base\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $value
 * @property string $comment
 * @property int $group_id
 * @property bool $isActive
 *
 * @property string $title
 * @property string $editLink
 */
class WhiteListEventDecorator extends Model
{

    /** @var int */
    public $id;

    /** @var string */
    public $code;

    /** @var int */
    public $group_id;

    /** @var string */
    public $value;

    /** @var string */
    public $comment;

    /** @var bool */
    public $isActive = false;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getEditLink()
    {
        return Html::a($this->title, ['/important_events/names/edit', 'id' => $this->id]);
    }

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setActivity($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

}