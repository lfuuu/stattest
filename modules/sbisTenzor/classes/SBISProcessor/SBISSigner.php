<?php

namespace app\modules\sbisTenzor\classes\SBISProcessor;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\modules\sbisTenzor\classes\SBISDocumentStatus;
use app\modules\sbisTenzor\classes\SBISProcessor;
use app\modules\sbisTenzor\models\SBISDocument;
use DateTime;
use Yii;

/**
 * Обработчик подписи пакетов документов
 */
class SBISSigner extends SBISProcessor
{
    const LIMIT_PER_PROCESS = 10;

    /**
     * Получаем пакеты документов на подпись
     *
     * @param int $sbisOrganizationId
     * @return SBISDocument[]
     */
    protected function getDocumentsToSign($sbisOrganizationId)
    {
        return SBISDocument::find()
            ->with('sbisOrganization')
            ->joinWith('attachments')
            ->where(['sbis_organization_id' => $sbisOrganizationId])
            ->andWhere(['=', 'state', SBISDocumentStatus::PROCESSING])
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
     * Подписывает пакет документов
     *
     * @param SBISDocument $document
     * @return bool
     * @throws \Exception
     */
    protected function signDocument(SBISDocument $document)
    {
        $document->sbisOrganization->checkExpirationDate();

        $success = true;

        $signCommand = $this->getAPIByDocument($document)->signCommand;
        $hashCommand = $this->getAPIByDocument($document)->hashCommand;
        foreach ($document->attachments as $attachment) {
            if ($attachment->is_sign_needed !== $attachment->is_signed) {
                if (!$attachment->sign($signCommand, $hashCommand)) {
                    return false;
                }
            }
        }

        return $success;
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
            $documents = $this->getDocumentsToSign($sbisOrganizationId);
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
                        'Ошибка обработчика подписи документов (document id: %s): %s',
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
     * Обработка пакета документов
     *
     * @param SBISDocument $document
     * @return bool
     * @throws \Exception
     */
    protected function process(SBISDocument $document)
    {
        $success = false;
        switch ($document->state) {
            case SBISDocumentStatus::PROCESSING:
                $success = $this->signDocument($document);
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
                case SBISDocumentStatus::PROCESSING:
                    $newState = SBISDocumentStatus::SIGNED;
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