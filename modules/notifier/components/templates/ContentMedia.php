<?php

namespace app\modules\notifier\components\templates;

use Yii;
use yii\db\ActiveRecord;
use app\classes\media\MediaManager;
use app\modules\notifier\models\templates\TemplateContent;

class ContentMedia extends MediaManager
{

    /** @var TemplateContent */
    private $_message;

    /**
     * @param TemplateContent $message
     */
    public function __construct(TemplateContent $message)
    {
        $this->_message = $message;
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return 'files/messages_content';
    }

    /**
     * @param int $withContent
     * @return array
     */
    public function getFile($withContent = 0)
    {
        if (empty($this->_message->filename) || !is_file($this->getFilePath($this->_message))) {
            return false;
        }

        $file = new \stdClass;
        $file->name = $this->_message->filename;

        $fileData = [
            'ext' => $this->getMime($file)[0],
            'mimeType' => $this->getMime($file)[1],
            'size' => $this->getSize($this->_message),
            'name' => $file->name,
        ];

        if ($withContent) {
            $fileData['content'] = file_get_contents($this->getFilePath($this->_message));
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
        $this->_message->filename = $name;
        return $this->_message;
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
        return (array)$this->_message;
    }

    /**
     * @param ActiveRecord $fileModel
     * @return string
     */
    public function getFilePath(ActiveRecord $fileModel)
    {
        return implode('/',
            [Yii::$app->params['STORE_PATH'], static::getFolder(), implode('_', $fileModel->primaryKey)]);
    }

}
