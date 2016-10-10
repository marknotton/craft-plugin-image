<?php
namespace Craft;

class ImagePlugin extends BasePlugin {
  public function getName() {
    return Craft::t('Image');
  }

  public function getVersion() {
    return '0.1';
  }

  public function getSchemaVersion() {
    return '0.1';
  }

  public function getDescription() {
    return 'Image adds a small collection of filters to help manage reoccurring image queries.';
  }

  public function getDeveloper() {
    return 'Yello Studio';
  }

  public function getDeveloperUrl() {
    return 'http://yellostudio.co.uk';
  }

  public function getDocumentationUrl() {
    return 'https://github.com/marknotton/craft-plugin-image';
  }

  public function getReleaseFeedUrl() {
    return 'https://raw.githubusercontent.com/marknotton/craft-plugin-image/master/image/releases.json';
  }

  public function getSettingsHtml() {
    return craft()->templates->render('image/settings', array(
      'settings' => $this->getSettings()
    ));
  }

  protected function defineSettings() {
    return array(
      'imageDirectory' => array(AttributeType::String, 'default' => ''),
    );
  }

  public function addTwigExtension() {
    Craft::import('plugins.image.twigextensions.images');
    Craft::import('plugins.image.twigextensions.imageinfo');

    return array(
      new images(),
      new imageinfo()
    );
  }

  public function onAfterInstall() {

    // Create Asset source
    if (array_key_exists('systemPath' ,craft()->config->get('environmentVariables')) && array_key_exists('uploads', craft()->config->get('environmentVariables'))) {
      ImagePlugin::log('Creating the General Asset Source.');

      $systemPath = craft()->config->get('environmentVariables')['systemPath'];
      $uploadsPath = craft()->config->get('environmentVariables')['uploads'];

      // Create the uploads directory if it doesn't exist
      if (!file_exists($systemPath.$uploadsPath)) {
        ImagePlugin::log('Uploads directory created');
        mkdir($systemPath.$uploadsPath, 0777, true);
      }
    }

    $sourceExists = false;
    foreach(craft()->assetSources->getViewableSources() as $source) {
      if ($source->handle === 'general') {
        $sourceExists = true;
        break;
      };
    }

    // Create Asset Model
    if ( $sourceExists === false ) {
      $source = new AssetSourceModel();
      $source->name   = 'General';
      $source->handle = 'general';
      $source->type   = 'Local';
      $source->settings = array('path' => '{systemPath}/{uploads}/','url' => '{uploads}/','publicURLs' => "1");

      $fieldLayout = craft()->fields->getLayoutById(1);
      $fieldLayout->type = ElementType::Asset;

      $source->setFieldLayout($fieldLayout);

      if (craft()->assetSources->saveSource($source)) {
        ImagePlugin::log('General asset source created');
      } else {
        ImagePlugin::log('General asset source failed to be created', LogLevel::Warning);
      }
    }


    // Create an assets field type for "featured images"
    if (is_null(craft()->fields->getFieldByHandle('featured'))) {
      ImagePlugin::log('Creating the Featured Image Field.');

      $featuredImage = new FieldModel();
      $featuredImage->groupId      = 1;
      $featuredImage->name         = Craft::t('Featured Image');
      $featuredImage->handle       = 'featured';
      $featuredImage->translatable = false;
      $featuredImage->type         = 'Assets';
      $featuredImage->instructions = 'Add a featured image';
      $featuredImage->settings     = array(
                                  "useSingleFolder" => "",
                                  "sources" => "*",
                                  "defaultUploadLocationSource" => "1",
                                  "defaultUploadLocationSubpath" => "",
                                  "singleUploadLocationSource" => "1",
                                  "singleUploadLocationSubpath" => "",
                                  "restrictFiles" => "",
                                  "allowedKinds" => ["image"],
                                  "limit" => "1",
                                  "viewMode" => "large",
                                  "selectionLabel" => "Add an image"
                                );

      if (craft()->fields->saveField($featuredImage)) {
        ImagePlugin::log('Featured Image field created successfully.');
      } else {
        ImagePlugin::log('Could not save the Featured Image field.', LogLevel::Warning);
      }
    }
  }
}
