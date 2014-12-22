<?php
namespace app\models\support;

use app\classes\behaviors\CreatedAt;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $ticket_id
 * @property string $user_id
 * @property string $text
 * @property string $created_at
 * @property
 */
class TicketComment extends ActiveRecord
{
  public static function tableName()
  {
    return 'support_ticket_comment';
  }

  public function behaviors()
  {
    return [
      'createdAt' => CreatedAt::className(),
    ];
  }

  /**
   * @return \DateTime
   */
  public function getCreatedAt()
  {
    return new \DateTime($this->created_at, new \DateTimeZone('UTC'));
  }

}