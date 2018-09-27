<?php

namespace app\models\dictionary;

use app\classes\model\ActiveRecord;
use app\classes\validators\ArrayValidator;
use app\exceptions\ModelValidationException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $title
 * @property int $domain
 *
 * @property-read PublicSiteCountry[] $publicSiteCountries
 */
class PublicSite extends ActiveRecord
{

    public $data = [];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'public_site';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['title', 'domain',], 'string'],
            [['title', 'domain',], 'required'],
            ['data', ArrayValidator::class],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'title' => 'Название',
            'domain' => 'Домен',
            'data' => 'Данные для сайта',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPublicSiteCountries()
    {
        return $this->hasMany(PublicSiteCountry::class, ['site_id' => 'id'])
            ->orderBy(['order' => SORT_DESC]);
    }

    /**
     * @param bool $skipIfSet
     * @return $this
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        $publicSiteCountries = $this->publicSiteCountries;
        foreach ($publicSiteCountries as $index => $publicSiteCountry) {
            $this->data[$index]['order'] = $publicSiteCountry->order;
            $this->data[$index]['country_code'] = $publicSiteCountry->country_code;
            $this->data[$index]['city_ids'] = ArrayHelper::getColumn($publicSiteCountry->publicSiteCities, 'city_id');
        }

        return $this;
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $transaction = self::getDb()->beginTransaction();

        try {
            if (parent::save($runValidation = true, $attributeNames = null)) {
                foreach ($this->data as $row) {
                    $countryLinkWithPublicSite = PublicSiteCountry::findOne([
                        'site_id' => $this->id,
                        'country_code' => $row['country_code'],
                    ]);

                    if ($countryLinkWithPublicSite === null) {
                        $countryLinkWithPublicSite = new PublicSiteCountry;
                    }

                    $countryLinkWithPublicSite->site_id = $this->id;
                    $countryLinkWithPublicSite->country_code = (int)$row['country_code'];
                    $countryLinkWithPublicSite->order = (int)$row['order'];

                    if (!$countryLinkWithPublicSite->save()) {
                        throw new ModelValidationException($countryLinkWithPublicSite);
                    }

                    if (array_key_exists('city_ids', $row)) {
                        PublicSiteCity::deleteAll(['public_site_country_id' => $countryLinkWithPublicSite->id]);

                        foreach ($row['city_ids'] as $cityId) {
                            $cityLinkWithPublicSiteCountry = new PublicSiteCity;
                            $cityLinkWithPublicSiteCountry->public_site_country_id = $countryLinkWithPublicSite->id;
                            $cityLinkWithPublicSiteCountry->city_id = $cityId;
                            if (!$cityLinkWithPublicSiteCountry->save()) {
                                throw new ModelValidationException($cityLinkWithPublicSiteCountry);
                            }
                        }
                    }
                }

                $transaction->commit();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e);
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['/dictionary/public-site/edit', 'id' => $this->id]);
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return Url::to(['/dictionary/public-site/delete', 'id' => $this->id]);
    }

}