<?php

namespace app\modules\sbisTenzor\classes\SBISTensorAPI;

/**
 * Краткое описание объекта Документ в СБИС API
 */
class SBISDocumentInfo
{
    public $externalId;
    public $externalState;
    public $externalStateName;
    public $externalStateDescription;
    public $lastEventId;
    public $urlExternal;
    public $urlOur;
    public $urlPDF;
    public $urlArchive;

    /**
     * SBISDocumentInfo constructor
     *
     * @param array $documentData Массив информации по документу
     */
    public function __construct(array $documentData)
    {
        if (!empty($documentData['Идентификатор'])) {
            $this->externalId = $documentData['Идентификатор'];
        }

        if (array_key_exists('СсылкаДляКонтрагент', $documentData)) {
            $this->urlExternal = $documentData['СсылкаДляКонтрагент'];
        }

        if (array_key_exists('СсылкаДляНашаОрганизация', $documentData)) {
            $this->urlOur = $documentData['СсылкаДляНашаОрганизация'];
        }

        if (array_key_exists('СсылкаНаPDF', $documentData)) {
            $this->urlPDF = $documentData['СсылкаНаPDF'];
        }

        if (array_key_exists('СсылкаНаАрхив', $documentData)) {
            $this->urlArchive = $documentData['СсылкаНаАрхив'];
        }

        if (!empty($documentData['Событие']) && is_array($documentData['Событие'])) {
            if ($lastEvent = end($documentData['Событие'])) {
                $this->lastEventId = $lastEvent['Идентификатор'];
            }
        }

        if (!empty($documentData['Состояние'])) {
            $this->externalState = intval($documentData['Состояние']['Код']);
            $this->externalStateName = $documentData['Состояние']['Название'];
            $this->externalStateDescription = $documentData['Состояние']['Описание'];
        }
    }
}