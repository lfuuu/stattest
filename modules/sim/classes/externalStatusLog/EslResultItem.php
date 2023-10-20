<?php

namespace app\modules\sim\classes\externalStatusLog;

use app\helpers\DateTimeZoneHelper;

class EslResultItem extends \yii\base\Component
{
    const STATUS_INFO = 'info';
    const STATUS_ERROR = 'error';
    const STATUS_WARNING = 'warning';
    const STATUS_TRANSPORT_ERROR = 'transport_error';
    const STATUS_RAW = 'raw';

    private array $textClassMap = [
        self::STATUS_INFO => 'text-success',
        self::STATUS_ERROR => 'text-info',
        self::STATUS_WARNING => 'text-warning',
        self::STATUS_TRANSPORT_ERROR => 'text-danger',
        self::STATUS_RAW => 'text-danger',
    ];

    public string $itemStatus = self::STATUS_INFO;
    public string $itemText = '';
    public array $info = [];
    public string $insertDt = '';

    public function __toString()
    {
        $insertDate = $this->insertDt ? DateTimeZoneHelper::getDateTime($this->insertDt) : '';
        $title = "";

        if ($this->info) {
            $titleStr = htmlspecialchars(var_export($this->info, true));
            $title = <<<TITLE
 title="{$titleStr}"
TITLE;
        }

        return <<<HTML
        <div class="row">
            <div class="col-md-3">{$insertDate}</div>
            <div class="col-md-9 {$this->textClassMap[$this->itemStatus]}"{$title}>{$this->itemText}</div>
        </div>
HTML;
    }
}
