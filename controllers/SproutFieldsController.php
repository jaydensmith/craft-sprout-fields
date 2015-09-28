<?php
namespace Craft;

class SproutFieldsController extends BaseController
{
	public function actionValidate()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$value       = craft()->request->getRequiredPost('value');
		$fieldHandle = craft()->request->getRequiredPost('fieldHandle');
		$field       = craft()->fields->getFieldByHandle($fieldHandle);

		$settings = $field->getSettings();

		// Email Field customPattern setting
		$customPattern = $field->settings['customPattern'];

		// Check for Phone field mask
		if ($settings['mask'] == "")
		{
			$settings['mask'] = $this->default;
		}

		// craft()->sproutLinkField->validate($value, $field)
		// craft()->sproutEmailField->validateEmailAddress($value, $customPattern)
		if (!craft()->sproutPhoneField->validate($value, $settings))
		{
			$this->returnJson(false);
		}

		$this->returnJson(true);
	}

	public function actionSproutAddress()
	{

		$countryCode = craft()->request->getPost('countryCode');
		$sproutAddressName = craft()->request->getPost('sproutAddressName');
		$sproutAddress = craft()->request->getPost('sproutAddress');

		$addressField = craft()->sproutFields_addressField->getAddress($sproutAddress);
		craft()->sproutFields_addressFormField->setParams($countryCode, $sproutAddressName, $addressField);
		echo craft()->sproutFields_addressFormField->setForm(true);
	}
}