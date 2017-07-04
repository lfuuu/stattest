<?php

namespace app\modules\nnp\media;

use app\classes\media\MediaManager;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\CountryFile;
use DateTime;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Yii;
use yii\db\ActiveRecord;

class CountryMedia extends MediaManager
{
    const SIZE_SMALL_CSV = 5000000;// текст считается маленьким, если он менее 5Мб
    const SIZE_SMALL_ZIP = 500000; // архив считается маленьким, если он менее 500Кб

    /** @var Country */
    private $_country;

    /**
     * @param Country $country
     */
    public function __construct(Country $country)
    {
        $this->_country = $country;
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return 'nnp/country';
    }

    /**
     * @param string $name
     * @param string $comment
     * @return CountryFile
     * @throws \app\exceptions\ModelValidationException
     */
    protected function createFileModel($name, $comment)
    {
        $countryFile = new CountryFile;
        $countryFile->country_code = $this->_country->code;
        $countryFile->ts = (new DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $countryFile->name = $name;
        $countryFile->comment = $comment;
        $countryFile->user_id = Yii::$app->user->getId();

        if (!$countryFile->save()) {
            throw new ModelValidationException($countryFile);
        }

        return $countryFile;
    }

    /**
     * @param ActiveRecord $countryFile
     * @throws \Symfony\Component\Finder\Exception\AccessDeniedException
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     * @throws \app\exceptions\ModelValidationException
     */
    protected function deleteFileModel(ActiveRecord $countryFile)
    {
        /** @var CountryFile $countryFile */
        if ($countryFile->country_code != $this->_country->code) {
            throw new AccessDeniedException();
        }

        if (!$countryFile->delete()) {
            throw new ModelValidationException($countryFile);
        }
    }

    /**
     * @return CountryFile[]
     */
    protected function getFileModels()
    {
        return CountryFile::find()
            ->where(['country_code' => $this->_country->code])
            ->orderBy(['id' => SORT_DESC])
            ->indexBy('id')
            ->all();
    }

    /**
     * Можно ли импортировать файл сразу или надо откладывать в очередь
     *
     * @param CountryFile $countryFile
     * @return bool
     */
    public function isSmall(CountryFile $countryFile)
    {
        $fileInfo = $this->getFile($countryFile);
        $isZip = ($fileInfo['ext'] == 'zip');
        return $fileInfo['size'] < ($isZip ? self::SIZE_SMALL_ZIP : self::SIZE_SMALL_CSV);

    }
}