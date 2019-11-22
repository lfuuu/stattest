<?php

namespace app\modules\sbisTenzor\classes;

use app\modules\sbisTenzor\models\SBISExchangeForm;

class SBISExchangeFile
{
    const EXTENSION_PDF = 'pdf';
    const EXTENSION_XML = 'xml';

    /** @var SBISExchangeForm */
    public $form;
    /** @var string */
    public $extension;

    /**
     * SBISExchangeFile constructor.
     * @param SBISExchangeForm $form
     * @param string $extension
     */
    public function __construct(SBISExchangeForm $form, $extension)
    {
        $this->form = $form;
        $this->extension = $extension;
    }

    /**
     * @return bool
     */
    public function isPdf()
    {
        return $this->extension == self::EXTENSION_PDF;
    }

    /**
     * @return bool
     */
    public function isXML()
    {
        return $this->extension == self::EXTENSION_XML;
    }

}