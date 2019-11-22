<?php

namespace app\modules\sbisTenzor\forms\document;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\modules\sbisTenzor\classes\SBISDocumentManager;
use app\modules\sbisTenzor\classes\SBISDocumentManager\SBISFile;
use app\modules\sbisTenzor\models\SBISDocument;
use Yii;
use yii\web\UploadedFile;

class EditForm extends \app\classes\Form
{
    /** @var SBISDocumentManager */
    protected $documentManager;

    /**
     * Edit form constructor
     *
     * @param ClientAccount|null $client
     * @throws \Exception
     */
    public function __construct(ClientAccount $client = null)
    {
        parent::__construct();

        $this->documentManager = new SBISDocumentManager($client);
    }

    /**
     * Ссылка на страницу со списком документов
     *
     * @param int $clientId
     * @return string
     */
    public static function getIndexUrl($clientId)
    {
        return '/sbisTenzor/document/' . ($clientId ? '?clientId=' . $clientId : '');
    }

    /**
     * Получить документ
     *
     * @return SBISDocument
     */
    public function getDocument()
    {
        return $this->documentManager->getDocument();
    }

    /**
     * Попробовать сохранить документ, если POST
     *
     * @return bool
     * @throws ModelValidationException
     */
    public function tryToSave()
    {
        if ($this->documentManager->loadData(Yii::$app->request->post())) {
            return $this->saveDocument();
        }

        return false;
    }

    /**
     * Сохранение документа
     *
     * @return bool
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected function saveDocument()
    {
        $document = $this->documentManager->getDocument();

        $files = [];
        for ($i = 1; $i <= SBISDocument::MAX_ATTACHMENTS; $i++) {
            if ($file = UploadedFile::getInstance($document, 'filename[' . $i . ']')) {
                $files[] = new SBISFile($file);
            }
        }
        $this->documentManager->setFiles($files);

        return $this->documentManager->saveDocument();
    }
}