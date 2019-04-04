<?php

namespace barrelstrength\sproutfields\fields;

use barrelstrength\sproutbasefields\SproutBaseFields;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Json;
use libphonenumber\PhoneNumberUtil;
use yii\db\Schema;

use CommerceGuys\Intl\Country\CountryRepository;
use barrelstrength\sproutbasefields\models\Phone as PhoneModel;

/**
 *
 * @property array  $elementValidationRules
 * @property string $contentColumnType
 * @property mixed  $settingsHtml
 * @property array  $countries
 */
class Phone extends Field implements PreviewableFieldInterface
{
    /**
     * @var string|null
     */
    public $customPatternErrorMessage;

    /**
     * @var bool|null
     */
    public $limitToSingleCountry;

    /**
     * @var string|null
     */
    public $country;

    /**
     * @var string|null
     */
    public $placeholder;

    /**
     * @var string|null
     */
    public $customPatternToggle;
    public $mask;
    public $inputMask;

    public static function displayName(): string
    {
        return Craft::t('sprout-fields', 'Phone (Sprout Fields)');
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
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/phone/settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = $this->handle;
        $countryId = Craft::$app->getView()->formatInputId($name.'-country');
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);
        $namespaceCountryId = Craft::$app->getView()->namespaceInputId($countryId);
        $countries = $this->getCountries();

        $country = $value['country'] ?? $this->country;
        $val = $value['phone'] ?? null;

        return Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/phone/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'namespaceCountryId' => $namespaceCountryId,
                'id' => $inputId,
                'countryId' => $countryId,
                'name' => $this->handle,
                'value' => $val,
                'placeholder' => $this->placeholder,
                'countries' => $countries,
                'country' => $country,
                'limitToSingleCountry' => $this->limitToSingleCountry
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        $phoneInfo = [];

        if (is_array($value)) {
            $namespace = $element->getFieldParamNamespace();
            $namespace = $namespace.'.'.$this->handle;
            $phoneInfo = Craft::$app->getRequest()->getBodyParam($namespace);
            // bad phone or empty phone
        }

        if (is_string($value)) {
            $phoneInfo = json_decode($value, true);
        }

        if (!isset($phoneInfo['phone'], $phoneInfo['country'])) {
            return $value;
        }

        // Always return array
        return new PhoneModel($phoneInfo['phone'], $phoneInfo['country']);
    }

    /**
     * @param mixed                 $value
     * @param ElementInterface|null $element
     *
     * @return array|mixed|null|string
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        // Submitting an Element to be saved
        if (is_object($value) && get_class($value) == PhoneModel::class) {
            return $value->getAsJson();
        }

        // Save the phone as json with the number and country
        return $value;
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

            if ($country) {
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
     * @param Element|ElementInterface $element
     *
     * @return void
     */
    public function validatePhone(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle);

        if ($this->required && !$value->phone) {
            $element->addError(
                $this->handle,
                Craft::t('sprout-fields', '{field} cannot be blank', [
                    'field' => $this->name
                ])
            );
        }

        if ($value->country && $value->phone && !SproutBaseFields::$app->phoneField->validate($value->phone, $value->country)) {
            $element->addError(
                $this->handle,
                SproutBaseFields::$app->phoneField->getErrorMessage($this, $value->country)
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        $html = '';

        if ($value->international) {
            $fullNumber = $value->international;
            $html = '<a href="tel:'.$fullNumber.'" target="_blank">'.$fullNumber.'</a>';
        }

        return $html;
    }
}
