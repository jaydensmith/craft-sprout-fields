<?php

namespace barrelstrength\sproutfields\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use libphonenumber\PhoneNumberUtil;
use yii\db\Schema;

use barrelstrength\sproutfields\SproutFields;
use barrelstrength\sproutbase\SproutBase;
use CommerceGuys\Intl\Country\CountryRepository;

class Phone extends Field implements PreviewableFieldInterface
{
    /**
     * @var string|null
     */
    public $customPatternErrorMessage;

    /**
     * @var bool|null
     */
    public $multipleCountriesToggle;

    /**
     * @var string|null
     */
    public $country;

    /**
     * @var string|null
     */
    public $placeholder;

    public static function displayName(): string
    {
        return SproutFields::t('Phone Number');
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'sprout-fields/_fieldtypes/phone/settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = $this->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);
        $countries = $this->getCountries();
        $country = $value['country'] ?? $this->country;
        $val = $value['phone'] ?? null;

        return Craft::$app->getView()->renderTemplate(
            'sprout-base/sproutfields/_includes/forms/phone/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'id' => $inputId,
                'name' => $this->handle,
                'value' => $val,
                'placeholder' => $this->placeholder,
                'countries' => $countries,
                'country' => $country,
                'multipleCountriesToggle' => $this->multipleCountriesToggle
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        $phoneInfo = [];

        if (is_array($value)){
            $namespace = "fields.".$this->handle;
            $phoneInfo = Craft::$app->getRequest()->getBodyParam($namespace);
            // bad phone or empty phone
            if (!isset($phoneInfo['phone']) || !isset($phoneInfo['country'])){
                return null;
            }
            // let's add the code
            $phoneUtil = PhoneNumberUtil::getInstance();
            $code = $phoneUtil->getCountryCodeForRegion($value['country']);
            $phoneInfo['code'] = $code;
        }

        if (is_string($value)) {
            $phoneInfo = json_decode($value, true);
        }
        // Always return array
        return $phoneInfo;
    }

    /**
     * SerializeValue renamed from Craft2 - prepValue
     *
     * @param mixed $value
     *
     * @return BaseModel|mixed
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        $phoneInfo = "";

        if (empty($value)) {
            return;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $phoneInfo = json_encode($value);
        }

        // let's save just the phone as json with the number and country
        return $phoneInfo;
    }

    public function getCountries()
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $regions = $phoneUtil->getSupportedRegions();
        $countries = [];

        foreach ($regions as $countryCode) {
            $code = $phoneUtil->getCountryCodeForRegion($countryCode);
            $countryRepository = new CountryRepository;
            $country = $countryRepository->get($countryCode);

            if ($country){
                $countries[$countryCode] = $country->getName().' +'.$code;
            }
        }

        asort($countries);

        return $countries;
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();
        $rules[] = 'validatePhone';

        return $rules;
    }

    /**
     * Validates our fields submitted value beyond the checks
     * that were assumed based on the content attribute.
     *
     *
     * @param ElementInterface $element
     *
     * @return void
     */
    public function validatePhone(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle);

        if (isset($value['country']) && isset($value['phone']) ) {
            if (!SproutBase::$app->phone->validate($value['phone'], $value['country'])) {
                $element->addError(
                    $this->handle,
                    SproutBase::$app->phone->getErrorMessage($this)
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        $html = '';

        if (isset($value['country']) && isset($value['code']) && isset($value['phone'])) {
            $code = $value['code'];
            $fullNumber = '+'.$code.$value['phone'];
            $html = '<a href="tel:'.$fullNumber.'" target="_blank">'.$fullNumber.'</a>';
        }

        return $html;
    }
}
