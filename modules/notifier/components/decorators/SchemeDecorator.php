<?php

namespace app\modules\notifier\components\decorators;

use app\modules\notifier\models\Schemes;
use yii\base\Model;

/**
 * @property string $result
 * @property array $scheme
 * @property array $log
 *
 * @property LogDecorator[] $prettyLog
 */
class SchemeDecorator extends Model
{

    /** @var string */
    public $result;

    /** @var array */
    public $scheme;

    /** @var array */
    public $log;

    /** @var LogDecorator[] */
    public $prettyLog;

    /**
     * @inheritdoc
     */
    public function init()
    {
        foreach ($this->log as $record) {
            $this->prettyLog[] = new LogDecorator($record);
        }
    }

    /**
     * @return array
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @param int $countryCode
     * @return bool
     */
    public function isActual($countryCode)
    {
        $currentScheme = Schemes::find()
            ->select([
                'country_code',
                'event_type_id' => 'event',
                'do_email',
                'do_sms',
                'do_email_monitoring',
                'do_email_operator',
            ])
            ->where([
                'country_code' => $countryCode,
            ])
            ->asArray()
            ->all();

        return $this->scheme == $currentScheme;
    }

}