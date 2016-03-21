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
     * @return bool
     */
    public function addFile(\yii\web\UploadedFile $file)
    {
        if ($file->error) {
            return false;
        }

        return parent::addFile([
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
        if (empty($this->message->filename) || !is_file($this->getFilePath($this->message))) {
            return false;
        }

        $file = new \stdClass;
        $file->name = $this->message->filename;

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
     * @param string $name
     * @param string $comment
     * @return TemplateContent
     */
    protected function createFileModel($name, $comment)
    {
        $this->message->filename = $name;
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