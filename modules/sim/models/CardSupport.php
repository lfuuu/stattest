<?php

namespace app\modules\sim\models;

/**
 * Класс-обертка, содержащий состояние процесса
 *
 * @see /modules/sim/controllers/CardController.php
 */
class CardSupport
{
    // Замена потерянной SIM-карты
    const LOST_CARD = 0;
    // Обмен MSISDN между SIM-картами
    const BETWEEN_CARDS = 1;
    // Обмен MSISDN между SIM-картой и неназначенным номером
    const UNASSIGNED_NUMBER = 2;
    // Статус ошибки
    const ERROR_BEHAVIOUR = 3;

    // Массив, задающий конфигурацию для ajax-запроса
    const AJAX_RESOLVER = [
        self::LOST_CARD => [
            'label' => 'Выполнить замену утерянной сим-карты',
            'button' => 'buttonLostCard',
            'method' => '/sim/card/change-iccid-and-imsi',
        ],
        self::BETWEEN_CARDS => [
            'label' => 'Обмен между сим-картами',
            'button' => 'buttonBetweenCards',
            'method' => '/sim/card/change-msisdn',
        ],
        self::UNASSIGNED_NUMBER => [
            'label' => 'Обмен между сим-картой и неназначенным номером',
            'button' => 'buttonUnassignedNumber',
            'method' => '/sim/card/change-unassigned-number',
        ],
    ];

    /**
     * Оригинальная сим-карта, запрашиваемая по URL
     *
     * @var Card|null
     */
    public $origin_card = null;

    /**
     * Виртуальная сим-карта, с которой происходит обмен данными оригинальной сим-карты
     *
     * @var VirtualCard|null
     */
    public $virtual_card = null;

    /**
     * Неназначенным номер
     *
     * @var null
     */
    public $unassigned_number = null;

    /**
     * Поведение логики: Replacement of lost SIM card, Exchange of MSISDN between SIM cards,
     * Exchange of MSISDN between a SIM card and an unassigned number
     *
     * @var null
     */
    public $behaviour = null;

    /**
     * Сообщение по некоторым статусам поведения
     *
     * @var null
     */
    public $message = null;

    /**
     * @return bool
     */
    public function isLostCard()
    {
        return $this->behaviour === self::LOST_CARD;
    }

    /**
     * @return bool
     */
    public function isBetweenCards()
    {
        return $this->behaviour === self::BETWEEN_CARDS;
    }

    /**
     * @return bool
     */
    public function isUnassignedNumber()
    {
        return $this->behaviour === self::UNASSIGNED_NUMBER;
    }

    /**
     * @return bool
     */
    public function isErrorBehaviour()
    {
        return $this->behaviour === self::ERROR_BEHAVIOUR;
    }
}