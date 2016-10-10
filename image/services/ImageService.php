<?php
namespace Craft;

class ImageService extends BaseApplicationComponent {

	public $imageDirectory = null;
	public $systemPath = null;

	public function init() {

		// Image Directory
		$imageDirectorySettings = craft()->plugins->getPlugin('image')->getSettings()['imageDirectory'];

		if (!empty($imageDirectorySettings)) {
			$imageDirectory = $imageDirectorySettings;
		} else if (array_key_exists('images', craft()->config->get('environmentVariables'))) {
			$imageDirectory = craft()->config->get('environmentVariables')["images"];
		} else {
			$imageDirectory = "/assets/images";
		}

		$this->imageDirectory = rtrim($imageDirectory, '/');

		// System path
		if (array_key_exists('systemPath', craft()->config->get('environmentVariables'))) {
			$systemPath = craft()->config->get('environmentVariables')["systemPath"];
		} else {
			$systemPath = getcwd();
		}

		$this->systemPath = $systemPath;

	}


}
