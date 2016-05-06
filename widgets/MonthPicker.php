<?php

namespace app\widgets;

use Yii;
use yii\helpers\Html;

/**
 * Виджет выбора года и месяца
 * @link https://github.com/KidSysco/jquery-ui-month-picker
 */
class MonthPicker extends \yii\widgets\InputWidget
{
    public $widgetOptions = [
        'Button' => false,
        'MaxMonth' => 0,
        'MonthFormat' => 'yy-mm',
    ];

    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     */
    public function init()
    {
        parent::init();
        $this->widgetOptions['i18n'] = [
            'year' => Yii::t('widgets/MonthPicker', 'Year'),
            'prevYear' => Yii::t('widgets/MonthPicker', 'Previous Year'),
            'nextYear' => Yii::t('widgets/MonthPicker', 'Next Year'),
            'next12Years' => Yii::t('widgets/MonthPicker', 'Jump Forward 12 Years'),
            'prev12Years' => Yii::t('widgets/MonthPicker', 'Jump Back 12 Years'),
            'nextLabel' => Yii::t('widgets/MonthPicker', 'Next'),
            'prevLabel' => Yii::t('widgets/MonthPicker', 'Prev'),
            'buttonText' => Yii::t('widgets/MonthPicker', 'Open Month Chooser'),
            'jumpYears' => Yii::t('widgets/MonthPicker', 'Jump Years'),
            'backTo' => Yii::t('widgets/MonthPicker', 'Back to'),
            'months' => [
                Yii::t('widgets/MonthPicker', 'Jan.'),
                Yii::t('widgets/MonthPicker', 'Feb.'),
                Yii::t('widgets/MonthPicker', 'Mar.'),
                Yii::t('widgets/MonthPicker', 'Apr.'),
                Yii::t('widgets/MonthPicker', 'May'),
                Yii::t('widgets/MonthPicker', 'June'),
                Yii::t('widgets/MonthPicker', 'July'),
                Yii::t('widgets/MonthPicker', 'Aug.'),
                Yii::t('widgets/MonthPicker', 'Sep.'),
                Yii::t('widgets/MonthPicker', 'Oct.'),
                Yii::t('widgets/MonthPicker', 'Nov.'),
                Yii::t('widgets/MonthPicker', 'Dec.'),
            ]
        ];
    }

    public function run()
    {
        $view = $this->getView();
        MonthPickerAsset::register($view);

        $js = sprintf('$("#%s").MonthPicker(%s).MonthPicker("option", "OnAfterChooseMonth", function() { $(this).trigger("change"); });',
            $this->options['id'], json_encode($this->widgetOptions));
        $view->registerJs($js, $view::POS_READY);

        return
            $this->hasModel()
                ? Html::activeTextInput($this->model, $this->attribute, $this->options)
                : Html::textInput($this->name, $this->value, $this->options);
    }

}