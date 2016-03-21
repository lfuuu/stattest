<?php

namespace app\classes\media;

use Yii;
use yii\db\ActiveRecord;
use app\models\message\TemplateContent;

class TemplateContentMedia extends MediaManager
{
    /** @var TemplateContent */
    private $message;

    /**
     * @param TemplateContent $message
     */
    public function __construct(TemplateContent $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return 'files/messages_content';
    }

    /**
     * @param \yii\web\UploadedFile $file
     */
    public function addFile(\yii\web\UploadedFile $file)
    {
        parent::addFile([
            'tmp_name' => $file->tempName,
            'name' => $file->name,
        ]);
    }

    /**
     * @param int $with_content
     * @return array
     */
    public function getFile($with_content = 0)
    {
        if (empty($this->message->file) || !is_file($this->getFilePath($this->message))) {
            return false;
        }

        $file = new \stdClass;
        $file->name = $this->message->file;

        $fileData = [
            'ext' => $this->getMime($file)[0],
            'mimeType' => $this->getMime($file)[1],
            'size' => $this->getSize($this->message),
            'name' => $file->name,
        ];

        if ($with_content) {
            $fileData['content'] = file_get_contents($this->getFilePath($this->message));
        }

        return $fileData;
    }

    /**
     * @return TemplateContent
     */
    protected function createFileModel($name, $comment)
    {
        $this->message->file = $name;
        return $this->message;
    }

    /**
     * @param ActiveRecord $file
     */
    protected function deleteFileModel(ActiveRecord $file)
    {
    }

    /**
     * @return TemplateContent
     */
    protected function getFileModels()
    {
        return $this->message;
    }

    /**
     * @param ActiveRecord $fileModel
     * @return string
     */
    protected function getFilePath(ActiveRecord $fileModel)
    {
        return implode('/', [Yii::$app->params['STORE_PATH'], static::getFolder(), implode('_', $fileModel->primaryKey)]);
    }

}