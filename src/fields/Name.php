<?php

namespace barrelstrength\sproutfields\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;

use barrelstrength\sproutfields\SproutFields;
use barrelstrength\sproutbase\models\sproutfields\Name as NameModel;

class Name extends Field implements PreviewableFieldInterface
{
    /**
     * @var bool
     */
    public $displayMultipleFields;

    /**
     * @var bool
     */
    public $displayMiddleName;

    /**
     * @var bool
     */
    public $displayPrefix;

    /**
     * @var bool
     */
    public $displaySuffix;

    public static function displayName(): string
    {
        return SproutFields::t('Name (Sprout)');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-fields/_fields/name/settings',
            [
                'field' => $this,
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = $this->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        return Craft::$app->getView()->renderTemplate('sprout-base/sproutfields/_fields/name/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'id' => $inputId,
                'name' => $name,
                'value' => $value,
                'field' => $this
            ]);
    }

    /**
     * Prepare our Name for use as an NameModel
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return NameModel|mixed
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        $nameModel = new NameModel();

        // String value when retrieved from db
        if (is_string($value)) {
            $nameArray = json_decode($value, true);
            $nameModel->setAttributes($nameArray, false);
        }

        // Array value from post data
        if (is_array($value) && isset($value['address'])) {

            $nameModel->setAttributes($value['address'], false);

            if ($fullNameShort = $value['address']['fullNameShort'] ?? null)
            {
                $nameArray = explode(' ',trim($fullNameShort));

                $nameModel->firstName = $nameArray[0] ?? $fullNameShort;
                unset($nameArray[0]);

                $nameModel->lastName = implode(' ', $nameArray);
            }
        }

        return $nameModel;
    }

    /**
     *
     * Prepare the field value for the database.
     *
     * We store the Name as JSON in the content column.
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return array|bool|mixed|null|string
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        if (empty($value)) {
            return false;
        }

        // Submitting an Element to be saved
        if (is_object($value) && get_class($value) == NameModel::class) {
            return json_encode($value->getAttributes());
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();
        $rules[] = 'validateName';

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
    public function validateName(ElementInterface $element)
    {
//        $value = $element->getFieldValue($this->handle);
//
//        $customPattern = $this->customPattern;
//        $checkPattern = $this->customPatternToggle;
//
//        if (!SproutBase::$app->email->validateEmailAddress($value, $customPattern, $checkPattern)) {
//            $element->addError($this->handle,
//                SproutBase::$app->email->getErrorMessage(
//                    $this->name, $this)
//            );
//        }
//
//        $uniqueEmail = $this->uniqueEmail;
//
//        if ($uniqueEmail && !SproutBase::$app->email->validateUniqueEmailAddress($value, $element, $this)) {
//            $element->addError($this->handle,
//                SproutFields::t($this->name.' must be a unique email.')
//            );
//        }
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
//        $html = '';
//
//        if ($value) {
//            $html = '<a href="mailto:'.$value.'" target="_blank">'.$value.'</a>';
//        }
//
//        return $html;
    }
}