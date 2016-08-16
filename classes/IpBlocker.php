<?php

namespace app\classes;


use app\helpers\DateTimeZoneHelper;
use app\models\IpBlock;

/**
 * Класс блокировки IP-адреса.
 * На данный момент используется для временной блокировки пользователя, отправившего заявку с сайта.
 * В качестве защиты от повторной отправки заявки.
 *
 * Class IpBlocker
 * @package app\classes
 */
class IpBlocker extends Singleton
{
    /**
     * Устанавливаем блокировку на IP-адрес.
     *
     * @param string $ip
     * @param int $blockTime в секундах
     * @return bool
     */
    public function block($ip, $blockTime = 60)
    {
        $ipBlock = IpBlock::findOne(['ip' => $ip]);

        if (!$ipBlock) {
            $ipBlock = new IpBlock;
            $ipBlock->ip = $ip;
        }

        $now = new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT));
        $ipBlock->block_time = $now->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $ipBlock->unblock_time = $now->modify("+" . $blockTime . "seconds")->format(DateTimeZoneHelper::DATETIME_FORMAT);

        return $ipBlock->save();
    }

    /**
     * Проверяет, заблокирован ли IP-адрес
     *
     * @param string $ip
     * @return bool
     */
    public function isBlocked($ip)
    {
        $this->clean();

        return (bool)IpBlock::find()->where(['ip' => $ip])->count();
    }

    /**
     * Очистка списка IP-адресов, которые к настоящему времени должны быть разблокированны
     */
    private function clean()
    {
        $now = new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT));

        IpBlock::deleteAll('unblock_time <= :now', [':now' => $now->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
    }
}