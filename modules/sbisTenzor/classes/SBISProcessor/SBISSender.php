<?php

namespace app\modules\sbisTenzor\classes\SBISProcessor;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\modules\sbisTenzor\classes\SBISDocumentStatus;
use app\modules\sbisTenzor\classes\SBISProcessor;
use app\modules\sbisTenzor\exceptions\SBISTensorException;
use app\modules\sbisTenzor\models\SBISDocument;
use DateTime;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * Обработчик отправки пакетов документов
 */
class SBISSender extends SBISProcessor
{
    const LIMIT_PER_PROCESS = 10;

    /**
     * Получаем пакеты документов на отправку
     *
     * @param int $sbisOrganizationId
     * @return SBISDocument[]
     */
    protected function getDocumentsToSend($sbisOrganizationId)
    {
        return SBISDocument::find()
            ->with('sbisOrganization')
            ->with('attachments')
            ->where(['sbis_organization_id' => $sbisOrganizationId])
            ->andWhere([
                'state' => [
                    SBISDocumentStatus::SIGNED,
                    SBISDocumentStatus::SAVED,
                    SBISDocumentStatus::NOT_SIGNED,
                    SBISDocumentStatus::READY,
                ]
            ])
            ->andWhere(['<', 'tries', SBISDocument::getMaxTries()])
            ->orderBy([
                'priority' => SORT_DESC,
                'state' => SORT_DESC,
                'date' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->limit(self::LIMIT_PER_PROCESS)
            ->all();
    }

    /**
     * Точка входа обработчика
     *
     * @throws ModelValidationException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function run()
    {
        $processed = 0;
        foreach ($this->apiPool as $sbisOrganizationId => $api) {
            $documents = $this->getDocumentsToSend($sbisOrganizationId);

            $processed += count($documents);
            foreach ($documents as $document) {
                $this->beforeProcess($document);

                $transaction = SBISDocument::getDb()->beginTransaction();
                try {

                    $success = $this->process($document);
                    $this->afterProcess($document, $success);

                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::error($e);
                    $errorText = sprintf(
                        'Ошибка обработчика отправки документов (document id: %s): %s',
                        $document->id,
                        $e->getMessage()
                    );

                    $document->addErrorText($errorText);
                }
            }
        }

        return $processed;
    }

    /**
     * Предобработка пакета документов
     *
     * @param SBISDocument $document
     * @return bool
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected function beforeProcess(SBISDocument $document)
    {
        $tries = $document->tries;

        if (++$tries <= SBISDocument::getMaxTries()) {
            $document->tries = $tries;
        } else {
            $document->setState(SBISDocumentStatus::ERROR);
        }

        if (!$document->save()) {
            throw new ModelValidationException($document);
        }

        return true;
    }

    /**
     * Обработка пакета документов
     *
     * @param SBISDocument $document
     * @return bool
     * @throws BadRequestHttpException
     * @throws SBISTensorException
     * @throws \yii\base\Exception
     * @throws \Exception
     */
    protected function process(SBISDocument $document)
    {
        $document->sbisOrganization->checkExpirationDate();

        $api = $this->getAPIByDocument($document);

        $success = false;
        switch ($document->state) {
            case SBISDocumentStatus::SIGNED:
                $success = $api->saveDocument($document);
                break;

            case SBISDocumentStatus::SAVED:
                $success = $api->prepareAction($document);
                break;

            case SBISDocumentStatus::NOT_SIGNED:
            case SBISDocumentStatus::READY:
                $success = $api->executeAction($document);
                break;
        }

        return $success;
    }

    /**
     * Постобработка пакета документов
     *
     * @param SBISDocument $document
     * @param bool $success
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected function afterProcess(SBISDocument $document, $success)
    {
        $dateNow = new DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));

        $newState = $document->state;
        if ($success) {

            switch ($document->state) {
                case SBISDocumentStatus::SIGNED:
                    $newState = SBISDocumentStatus::SAVED;
                    break;

                case SBISDocumentStatus::SAVED:
                    $newState = SBISDocumentStatus::NOT_SIGNED;
                    break;

                case SBISDocumentStatus::NOT_SIGNED:
                case SBISDocumentStatus::READY:
                    $newState = SBISDocumentStatus::SENT;
                    break;
            }

            if ($newState != $document->state) {
                // сбрасываем попытки
                $document->tries = 0;
            }
        } else {
            // failed
            if ($document->tries >= SBISDocument::getMaxTries()) {
                $newState = SBISDocumentStatus::ERROR;
            }
        }

        $document->setState($newState, $dateNow);

        if (!$document->save()) {
            throw new ModelValidationException($document);
        }
    }
}