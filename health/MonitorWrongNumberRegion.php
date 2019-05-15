<?php

namespace app\health;

use Yii;

class MonitorWrongNumberRegion extends Monitor
{
    private $_message = '';

    /**
     * @inheritdoc
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 1, 1];
    }

    /**
     * Получение сообщения для статуса
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        $message = '';


        $connection = Yii::$app->getDb();
        $query = 'select c.name, count(*) as cnt
                    from voip_numbers n, city c 
                    where n.city_id = c.id 
                    and n.region != c.connection_point_id 
                    group by c.id';
        $result = $connection->createCommand($query)->queryAll();

        foreach ($result as $item) {
            $message .= $item['name'] . ' (' . $item['cnt'] . ')' . ', ';
        }

        $this->_message = rtrim($message, ', ');

        return count($result);
    }
}