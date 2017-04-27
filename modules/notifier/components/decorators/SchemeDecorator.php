<?php

namespace app\modules\notifier\components\decorators;

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
     * @return array
     */
    public function diff($countryCode)
    {
        /** @todo Need to make multi-dimensional diff */
        /*
        $currentScheme = Schemes::find()
            ->where([
                'country_code' => $countryCode,
            ])
            ->asArray()
            ->all();

        return array_diff_assoc($currentScheme, $this->scheme);
        */
        return [];
    }

}