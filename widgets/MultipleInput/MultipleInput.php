<?php

namespace app\widgets\MultipleInput;

use unclead\multipleinput\MultipleInput as BaseMultipleInput;
use unclead\multipleinput\MultipleInputColumn;

class MultipleInput extends BaseMultipleInput
{

    /**
     * Перечисление ширины столбцов таблицы.
     * Сделано исключительно из-за некорректного поведения таблицы при работе в Select2 в режиме multiple.
     * ```php
     * colgroup => [
     *    '30%',
     *    '70%',
     * ],
     * ```
     *
     * @var array
     */
    public $colgroup = [];

    /**
     * Run widget.
     */
    public function run()
    {
        $config = [
            'id' => $this->options['id'],
            'colgroup' => $this->colgroup,
            'columns' => $this->columns,
            'attributeOptions' => $this->attributeOptions,
            'data' => $this->data,
            'columnClass' => $this->columnClass !== null ? $this->columnClass : MultipleInputColumn::class,
            'allowEmptyList' => $this->allowEmptyList,
            'min' => $this->min,
            'addButtonPosition' => $this->addButtonPosition,
            'rowOptions' => $this->rowOptions,
            'context' => $this,
        ];

        if (!is_null($this->removeButtonOptions)) {
            $config['removeButtonOptions'] = $this->removeButtonOptions;
        }

        if (!is_null($this->addButtonOptions)) {
            $config['addButtonOptions'] = $this->addButtonOptions;
        }

        return (new TableRenderer($config))->render();
    }

}