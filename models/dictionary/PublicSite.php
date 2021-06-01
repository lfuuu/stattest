<?php

namespace app\models\dictionary;

use app\classes\model\ActiveRecord;
use app\classes\validators\ArrayValidator;
use app\exceptions\ModelValidationException;
use app\models\Country;
use app\models\EntryPoint;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $title
 * @property int $domain
 *
 * @property-read PublicSiteCountry[] $publicSiteCountries
 * @property-read EntryPoint $entryPoints
 * @property-read Country $countryFirst
 */
class PublicSite extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

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
     * @return \yii\db\ActiveQuery
     */
    public function getEntryPoints()
    {
        return $this->hasMany(EntryPoint::class, ['site_id' => 'id'])
            ->inverseOf('site');
    }

    /**
     * @return Country
     */
    public function getCountryFirst()
    {
        return
            $this->entryPoints ?
            $this->entryPoints[0]->country :
            Country::findOne([Country::$primaryField => Country::UNITED_KINGDOM]);
    }

    /**
     * @return self[]
     */
    public static function getAllWithCountries()
    {

        return self::find()
            ->from(PublicSite::tableName())
            ->orderBy(['id' => SORT_ASC])
            ->all();
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
            $this->data[$index]['ndc_type_ids'] = ArrayHelper::getColumn($publicSiteCountry->publicSiteNdcTypes, 'ndc_type_id');
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

                $siteCountries = PublicSiteCountry::find()
                    ->where([
                        'site_id' => $this->id
                    ])
                    ->select('country_code')
                    ->column();

                $siteCountries = array_combine($siteCountries, $siteCountries);

                foreach ($this->data as $row) {
                    $countryLinkWithPublicSite = PublicSiteCountry::findOne([
                        'site_id' => $this->id,
                        'country_code' => $row['country_code'],
                    ]);

                    unset($siteCountries[$row['country_code']]);

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

                    if (array_key_exists('ndc_type_ids', $row)) {
                        PublicSiteNdcType::deleteAll(['public_site_country_id' => $countryLinkWithPublicSite->id]);

                        foreach ($row['ndc_type_ids'] as $ndcTypeId) {
                            $publicSiteNdcType = new PublicSiteNdcType();
                            $publicSiteNdcType->public_site_country_id = $countryLinkWithPublicSite->id;
                            $publicSiteNdcType->ndc_type_id = $ndcTypeId;
                            if (!$publicSiteNdcType->save()) {
                                throw new ModelValidationException($publicSiteNdcType);
                            }
                        }
                    }
                }


                if ($siteCountries) {
                    PublicSiteCountry::deleteAll(['site_id' => $this->id, 'country_code' => $siteCountries]);
                }

                $transaction->commit();
            }
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
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

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param string $indexBy
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $indexBy = 'id'
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy,
            $select = 'title',
            $orderBy = ['title' => SORT_ASC]
        );
    }
}
