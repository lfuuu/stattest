<?php

namespace app\classes\media;

use Yii;

class MediaManager
{

    public $mimesTypes = [
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'pdf'  => 'application/pdf',
        'gif'  => 'image/gif',
        'tif'  => 'image/tiff',
        'tiff' => 'image/tiff',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'jpe'  => 'image/jpeg',
        'htm'  => 'text/html',
        'html' => 'text/html',
        'txt'  => 'text/plain',
        'zip'  => 'application/zip',
        'rar'  => 'application/rar',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
    ];

    public $downloadable = [
        'doc', 'docx', 'pdf', 'zip', 'rar', 'xls', 'xlsx', 'ppt',
    ];

    protected
        $model,
        $record_id = 0;

    private
        $folder = '',
        $link_field = '';

    public function addFile(array $file, $comment = '', $name = '')
    {
        if (!file_exists($file['path']) || !is_file($file['path']))
            return false;

        if (!$name) {
            $name = $file['name'];
        } else {
            if (!preg_match('/\.([^\.]{2,5})$/', $name) && preg_match('/\.([^\.]{2,5})$/', $file['name'], $m)) {
                $name.= $m[0];
            }
        }

        $mediaLinkField = static::getLinkField();

        $record = new $this->model;
        $record->$mediaLinkField = $this->record_id;
        $record->ts = (new \DateTime())->format(\DateTime::ATOM);

        $record->name = $name;
        $record->comment = $comment;
        $record->user_id = Yii::$app->user->getId();

        $record->save();

        $filePath = implode('/', [Yii::$app->params['STORE_PATH'], 'files', static::getFolder(), $record->id]);
        move_uploaded_file($file['path'], $filePath);
    }

    public function removeFile($id)
    {
        $model = $this->model->findOne([static::getLinkField() => $this->record_id, 'id' => $id]);
        if ($model) {
            $model->delete();

            $filePath = implode('/', [Yii::$app->params['STORE_PATH'], 'files', static::getFolder(), $model->id]);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }

    public function getFiles()
    {
        $files =
            $this->model
                ->find()
                    ->where([static::getLinkField() => $this->record_id])
                    ->all();

        $result = [];
        foreach ($files as $file) {
            $result[] = $this->getFile($file);
        }

        return $result;
    }

    public function getFile($file)
    {
        return [
            'id' => $file->id,
            'ext' => $this->getMime($file)[0],
            'mimeType' => $this->getMime($file)[1],
            'size' => $this->getSize($file),
            'name' => $file->name,
            'comment' => $file->comment,
            'author' => $file->user_id,
            'created' => $file->ts,
        ];
    }

    public function getContent($file)
    {
        $filePath = implode('/', [Yii::$app->params['STORE_PATH'], 'files', static::getFolder(), $file->id]);

        if (file_exists($filePath)) {
            $fileData = $this->getFile($file);

            if (in_array($fileData['ext'], $this->downloadable)) {
                Yii::$app->response->sendContentAsFile(file_get_contents($filePath), $fileData['name']);
                Yii::$app->end();
            }
            else {
                Header('Content-Type: ' . $fileData['mimeType']);
                echo file_get_contents($filePath);
                Yii::$app->end();
            }
        }
    }

    protected function getSize($file)
    {
        $filePath = implode('/', [Yii::$app->params['STORE_PATH'], 'files', static::getFolder(), $file->id]);

        if (file_exists($filePath)) {
            return filesize($filePath);
        }
    }

    protected function getMime($file)
    {
        $name = strtolower($file->name);

        $mime = 'text/plain';
        $info = pathinfo($name);

        if (!isset($info['extension']) || !$info['extension'])
            return ['txt', $mime];

        $ext = $info['extension'];

        return [$ext, isset($this->mimesTypes[$ext]) ? $this->mimesTypes[$ext] : $mime];
    }

    protected function getFolder()
    {
        return $this->folder;
    }

    protected function setLinkField($field)
    {
        $this->link_field = $field;
        return $this;
    }

    protected function getLinkField()
    {
        return $this->link_field;
    }

}