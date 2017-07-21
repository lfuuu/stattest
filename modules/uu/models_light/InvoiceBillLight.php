<?php

namespace app\modules\uu\models_light;

use Yii;
use yii\base\Component;

class InvoiceBillLight extends Component implements InvoiceLightInterface
{

    public
        $id = 0,
        $date,
        $summary_without_vat = 0,
        $summary_vat = 0,
        $summary_with_vat = 0;

    private $_language;

    /**
     * @param string $billId
     * @param string $billDate
     * @param string $language
     */
    public function __construct($billId, $billDate, $language)
    {
        parent::__construct();

        $this->id = $billId;
        $this->date = $billDate;
        $this->_language = $language;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setSummaryVat($value)
    {
        $this->summary_vat += $value;
        return $this;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setSummaryWithoutVat($value)
    {
        $this->summary_without_vat += $value;
        return $this;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setSummaryWithVat($value)
    {
        $this->summary_with_vat += $value;
        return $this;
    }

    /**
     * @return string
     */
    public static function getKey()
    {
        return 'bill';
    }

    /**
     * @return string
     */
    public static function getTitle()
    {
        return 'Данные о счете';
    }

    /**
     * @return array
     */
    public static function attributeLabels()
    {
        return [
            'id' => 'Номер счета',
            'date' => 'Дата выставления счета',
            'summary_without_vat' => 'Сумма счета без НДС',
            'summary_vat' => 'Сумма НДС',
            'summary_with_vat' => 'Сумма счета с НДС',
        ];
    }

}