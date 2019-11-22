<?php

namespace app\modules\sbisTenzor\classes\SBISDocumentManager;

use app\modules\sbisTenzor\classes\XmlGenerator;
use yii\web\UploadedFile;

class SBISFile
{
    /** @var UploadedFile */
    protected $uploadFile;

    /** @var string */
    protected $pdfFile;

    /** @var XmlGenerator */
    protected $xmlFile;

    /**
     * SBISFile constructor
     *
     * @param UploadedFile|null $uploadFile
     * @param string $pdfFile
     * @param XmlGenerator|null $xmlFile
     * @throws \Exception
     */
    public function __construct($uploadFile = null, $xmlFile = null, $pdfFile = '')
    {
        if ($uploadFile instanceof UploadedFile) {
            $this->uploadFile = $uploadFile;
        } elseif ($xmlFile instanceof XmlGenerator) {
            $this->xmlFile = $xmlFile;
        } elseif ($pdfFile) {
            $this->pdfFile = $pdfFile;
        } else {
            throw new \Exception('Файл не выбран!');
        }
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        if ($this->uploadFile instanceof UploadedFile) {
            return $this->uploadFile->name;
        } elseif ($this->xmlFile instanceof XmlGenerator) {
            return $this->xmlFile->getFileName();
        }

        $parts = explode('-', basename($this->pdfFile));
        array_shift($parts);

        return implode('-', $parts);
    }

    /**
     * @return string
     */
    public function getOfficialFileName()
    {
        if ($this->uploadFile instanceof UploadedFile) {
            return '';
        } elseif ($this->xmlFile instanceof XmlGenerator) {
            return $this->xmlFile->getFileName();
        }

        return $this->getFileName();
    }

    /**
     * @param string $fileName
     * @return bool
     * @throws \Exception
     */
    public function saveAs($fileName = '')
    {
        if ($this->uploadFile instanceof UploadedFile) {
            return $this->uploadFile->saveAs($fileName, $deleteTempFile = true);
        } elseif ($this->xmlFile instanceof XmlGenerator) {
            return $this->xmlFile->save($fileName);
        }

        return copy($this->pdfFile, $fileName);
    }

}