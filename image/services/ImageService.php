<?php
namespace Craft;

class ImageService extends BaseApplicationComponent {

	public $imageDirectory = null;
	public $uploadsDirectory = null;
	public $systemPath = null;

	public function init() {

		// Image Directory
		$directorySettings = craft()->plugins->getPlugin('image')->getSettings();
		$imageDirectorySettings = $directorySettings['imageDirectory'];

		if (!empty($imageDirectorySettings)) {
			$imageDirectory = $imageDirectorySettings;
		} else if (array_key_exists('images', craft()->config->get('environmentVariables'))) {
			$imageDirectory = craft()->config->get('environmentVariables')["images"];
		} else {
			$imageDirectory = "/assets/images";
		}

		$this->imageDirectory = rtrim($imageDirectory, '/');

		// System path
		$systemPath = craft()->plugins->getPlugin('image')->getSettings()->relativeLocaleDirectories ? getcwd() : $_SERVER['DOCUMENT_ROOT'];

		$this->systemPath = $systemPath;

	}


}
