<?php

namespace app\classes\grid\column\universal;


class StringWithLinkColumn extends StringColumn
{
    public $isTargetBlank = true;

    /**
     * StringWithLinkColumn constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return mixed
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return preg_replace(
            '@(https?|ftp)://[^\s/$.?#].[^\s]*@i',
            "<a href='\\0'" . ($this->isTargetBlank ? ' target="_blank"' : '') . ">\\0</a>",
            $this->getDataCellValue($model, $key, $index)
        );
    }
}