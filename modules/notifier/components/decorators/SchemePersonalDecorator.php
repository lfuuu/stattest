<?php

namespace app\modules\notifier\components\decorators;

use yii\base\Model;

/**
 * @property string $result
 * @property array $scheme
 *
 * @property array $prettyScheme
 */
class SchemePersonalDecorator extends Model
{

    /** @var string */
    public $result;

    /** @var array */
    public $scheme;

    /** @var array */
    private $_prettyScheme = [];

    public function init()
    {
        foreach ($this->scheme as $record) {
            $this->_prettyScheme[$record['event_type_id']] = [
                'do_email' => $record['do_email'],
                'do_email_personal' => $record['do_email_personal'],
                'do_sms' => $record['do_sms'],
                'do_sms_personal' => $record['do_sms_personal'],
                'do_lk' => $record['do_lk'],
            ];
        }
    }

    /**
     * @return array
     */
    public function getPrettyScheme()
    {
        return $this->_prettyScheme;
    }

}