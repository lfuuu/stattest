<?php

namespace app\modules\nnp2\media\related;

abstract class Transformer
{
    const FUNC_PREG_REPLACE = 'preg_replace'; // замена с помощью регулярного выражения
    const FUNC_STR_REPLACE = 'str_replace'; // строчная замена
    const FUNC_STRPOS = 'strpos'; // замена, если есть вхождение

    protected array $preProcessing = [];

    /**
     * Обработать напильником
     *
     * @param string $value
     * @return string
     */
    public function transformValue($value)
    {
        foreach ($this->preProcessing as $preProcessing) {
            switch ($preProcessing[0]) {

                case self::FUNC_PREG_REPLACE:
                    $value = preg_replace($preProcessing[1], $preProcessing[2], $value);
                    break;

                case self::FUNC_STR_REPLACE:
                    $value = str_replace($preProcessing[1], $preProcessing[2], $value);
                    break;

                case self::FUNC_STRPOS:
                    if (strpos($value, $preProcessing[1]) !== false) {
                        $value = $preProcessing[2];
                    }
                    break;
            }
        }

        return trim($value);
    }
}
