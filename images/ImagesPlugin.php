<?php
namespace Craft;

class ImagesPlugin extends BasePlugin {
  public function getName() {
    return Craft::t('Images');
  }

  public function getVersion() {
    return '0.0.2';
  }

  public function getDeveloper() {
    return 'Yello Studio';
  }

  public function getDeveloperUrl() {
    return 'http://yellostudio.co.uk';
  }

  public function getDocumentationUrl() {
    return 'https://github.com/marknotton/craft-plugin-images';
  }

  public function getReleaseFeedUrl() {
    return 'https://raw.githubusercontent.com/marknotton/craft-plugin-images/master/images/releases.json';
  }

  public function addTwigExtension() {
    Craft::import('plugins.images.twigextensions.images');
    Craft::import('plugins.images.twigextensions.imageinfo');

    return array(
      new images(),
      new imageinfo()
    );
  }

  public function onAfterInstall() {

    // Create Asset source
    if (array_key_exists('systemPath' ,craft()->config->get('environmentVariables')) && array_key_exists('uploads', craft()->config->get('environmentVariables'))) {
      ImagesPlugin::log('Creating the General Asset Source.');

      $systemPath = craft()->config->get('environmentVariables')['systemPath'];
      $uploadsPath = craft()->config->get('environmentVariables')['uploads'];

      // Create the uploads directory if it doesn't exist
      if (!file_exists($systemPath.$uploadsPath)) {
        ImagesPlugin::log('Uploads directory created');
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
        ImagesPlugin::log('General asset source created');
      } else {
        ImagesPlugin::log('General asset source failed to be created', LogLevel::Warning);
      }
    }


    // Create an assets field type for "featured images"
    if (is_null(craft()->fields->getFieldByHandle('featured'))) {
      ImagesPlugin::log('Creating the Featured Image Field.');

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
        ImagesPlugin::log('Featured Image field created successfully.');
      } else {
        ImagesPlugin::log('Could not save the Featured Image field.', LogLevel::Warning);
      }
    }
  }



}
