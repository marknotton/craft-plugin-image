<?php
namespace Craft;

use Twig_Extension;
use Twig_Filter_Method;

class images extends \Twig_Extension {

  public function getName() {
    return Craft::t('Images');
  }

  public function getFilters() {
    return array(
      'image'       => new Twig_Filter_Method( $this, 'image',  array('is_safe' => array('html'))),
      'images'      => new Twig_Filter_Method( $this, 'images', array('is_safe' => array('html'))),
      'gallery'     => new Twig_Filter_Method( $this, 'images', array('is_safe' => array('html')))
    );
  }

  public function images() {

    // Atleast one symbol sting arugment should be passed
    if ( func_num_args() < 1 ){
      return false;
    }

    // The first argument is the entry that is automatically passed.
    $entry = func_get_arg(0);

    // Remove the first argument and set the arguments array
    $settingsArgs = array_slice(func_get_args(), 1);

    $transform       = null;
    $transformExists = null;
    $settings        = null;
    $field           = null;
    $order           = null;

    if ( !empty($settingsArgs) ) {

      foreach ($settingsArgs as &$setting) {
        if (is_array($setting)) {
          if ( count(array_intersect_key($this->transformKeys, $setting)) > 0 ) {
            // This array is a transform
            $transform = $setting;
          } else {
            // If the transform is declared in the settings, use that. Unless transform was already defined
            if ( array_key_exists('transform', $setting) && is_null($transform) && !isset($transform)) {
              $transform = $setting['transform'];
            }
            // Set the settings
            $settings = $setting;
          }
        }

        if (is_string($setting)) {
          // Compare this string with available asset transform types.
          if (count($this->allTransforms) >= 1 ) {
            foreach ($this->allTransforms as $trans) {
              if ( $trans->name == $setting ) {
                $transform = $setting;
                break;
              } else if ( is_null($field )) {
                $field = $setting;
              }
            }
          } else if ( is_null($field )) {
            $field = $setting;
          }
        }
      }
    }

    // Order
    if (isset($settings['shuffle']) && $settings['shuffle'] == true || isset($settings['order']) && $settings['order'] == 'random') {
      $order = 'RAND()';
    } else if (isset($settings['order'])) {
      $order = $settings['order'];
    } else {
      $order = 'sortOrder';
    }

    // Set the image variable dependant on the type of element that is being filtered
    switch (get_class($entry)) {
      case 'Craft\EntryModel':
        // Entry Type - When an entry is filtered with |images(..)
        if (isset($entry->$field)) {
          $images = $entry->$field->order($order);
        }
      break;
      case 'Craft\AssetFileModel':
      case 'Craft\ElementCriteriaModel':
        // Asset Type - When an idividual asset is filtered with |images(..)
        // Field Type - When field type is filtered with |images(..)
        $images = $entry->order($order);
      break;
    }

    // If the transform is a string, check that the transform type exists
    if (isset($transform) && is_string($transform)) {
      $transformExists = craft()->assetTransforms->getTransformByHandle($transform);
    }

    $limit = (isset($settings['limit']) && $settings['limit'] == true) ? $settings['limit'] : 0;

    $count = 1;

    $imagesFinal = array();


    if (count($images) == 0) {
      $images = array(false);
    }

    // Loop through each image
    foreach ($images as $image) {
      if ($count <= abs($limit) || abs($limit) == 0) {
        $settings['transform'] = $transform;
        array_push($imagesFinal, $this->format($image, $settings, $count++, $transformExists));
      }
    }

    if (isset($setting['url']) && $setting['url'] == true && count($imagesFinal) > 1) {
      // If an array of urls are returns, return an array.
      return $imagesFinal;
    } else {
      // Otherwise return a string
      return implode($imagesFinal);
    }

  }

  private function format($image, $settings=null, $count, $transformExists) {

    // If no settings are defined, img element will be used and no other settings will be applied.

    // If settings exists, extract the keys as variables, and the values as values
    if (is_array($settings)) {
      extract($settings);
    }

    // If an image could not be found, and fallback settings are true... image will have been passed as false.
    if ( !is_bool($image) && $image != false) {
      $imageUrl = $image->getUrl(isset($transform) || $transformExists ? $transform : null);
    } else {
      $imageUrl = null;
    }

    // Fallback
    if ( isset($fallback) && $fallback ) {

      if ($image == false || !file_exists($this->systemPath.$imageUrl) ) {
        // Fallback handle will be suffixed to the default image name
        if (!is_string($fallback)) {
          $fallback = isset($transform) && is_string($transform) ? $transform : 'default-image';
        }

        $fallbackExtensions = array('svg', 'png', 'jpg', 'jpeg', 'gif');

        if (preg_match('/(\.jpg|\.png|\.bmp)$/i', strtolower($fallback))) {
          // If fallback has an extension
          $imageUrl = $this->imageDirectory.'/'.$fallback;
        } else {
          // If fallback doesn't have an extension, find one
          // Loop through the most common file formats and return any image that prefixed with 'default-' (or the bespoke settings) and matches the transform type
          foreach ($fallbackExtensions as $format) {
            $fallbackUrl = $this->imageDirectory.'/'.$fallback.'.'.$format;
            if (file_exists($this->systemPath.$fallbackUrl)) {
              $imageUrl = $fallbackUrl;
              break;
            }
          }
        }
      }
    }

    if (isset($url) && $url == true) {
      return $imageUrl;
    }



    if (isset($svg) && $svg == true && (strlen($imageUrl) > 4 && substr($imageUrl, -4) == '.svg') && file_exists($this->systemPath.$imageUrl)) {
      // SVG's
      // TODO: Make it so, width, height, id, and class manipulate the inline SVG

      $svgUrl = $this->systemPath.$image->url;

      if (file_exists($svgUrl)) {

        $svgOutput = file_get_contents($svgUrl);

        if (isset($wrap)) {
          return $this->wrap($wrap, $svgOutput, $count);
        }

        return $svgOutput;
      }

      // echo "do something special for raw svg's";
    } else {
      // IMAGES's
      // Use 'img' as the default element if one is not defined
      if (!isset($element) || $element == 'image') {
        $element = 'img';
      }

      if (is_string($imageUrl)) {
        $output = '<'.$element;

        // ID
        if (isset($id)) {
          $output .= ' id="'.str_replace('%i', $count, $id).'"';
        }

        // Class
        if (isset($class)) {
          $output .= ' class="'.str_replace('%i', $count, $class).'"';
        }

        // Tone
        if (isset($tone)) {
          if ( is_string($tone)) {
            $imageTone = craft()->image_tone->tone($imageUrl, $tone);
          } else {
            $imageTone = craft()->image_tone->tone($imageUrl);
          }
          if (is_bool($imageTone)) {
            $output .= ' data-tone="'.($imageTone ? 'light' : 'dark').'"';
          }
        }

        // Data attribute
        if (isset($data) && is_array($data)) {
          $value = strpos($data[1], '%id') !== false ? str_replace('%id', $image['id'], $data[1]) : $data[1];

          if ( count($data) == 3 && strpos($data[1], '%url') !== false) {
            $trans = $data[2];
            $value = str_replace('%url', $image->getUrl($trans), $data[1]);
          } else if (strpos($data[1], '%url') !== false) {
            $value = str_replace('%url', $image['url'], $data[1]);
          }
          $output .= ' data-'.(str_replace('data-', '', $data[0])).'="'.$value.'"';
        }

        // Is size is 'true', attempt to add the appropraite width or height if otherwise not defined
        if (isset($size) && $size == true) {
          $imageSize = getimagesize($this->systemPath.$imageUrl);
          if (!isset($width)) {
            $width = $imageSize[0];
          }
          if (!isset($height)) {
            $height = $imageSize[0];
          }
        }

        // Width
        if (isset($width)) {
          $output .= ' width="'.$width.'"';
        }

        // Height
        if (isset($height)) {
          $output .= ' height="'.$height.'"';
        }

        // Image
        if ($element == 'img') {
          // IMG Element
          $output .= ' src="'.$imageUrl.'"';
          $output .= ' alt="'.(isset($image->title) ? $image->title : $imageUrl).'"';
        } else {
          // Background Image
          $output .= ' style="background-image:url('.$imageUrl.')"';
        }

        $output .= '>';

        // Close element unless element is an img singleton
        if ($element != 'img') {
          $output .= '</'.$element.'>';
        }

        // Wrap
        if (isset($wrap)) {
          return $this->wrap($wrap, $output, $count);
        }

        return $output;
      } else {
        return false;
      }
    }
  }

  // {{ entry|image('featured', thumb )}} - Define the fieldtype handle and transform type
  // {{ quick.content('gallery', 'home')|image(thumb) }} - No need to define the fieldtype handle, just the transform type
  public function image() {
    if ( func_num_args() < 1 ){
      return false;
    }

    $entry = func_get_arg(0);
    $settings = array_slice(func_get_args(), 1);

    $specialSettings = null;
    $transformSettings = null;

    $stringSettings = array();

    if ( !empty($settings) ) {
      foreach ($settings as &$setting) {
        if(is_string($setting)) {
          array_push($stringSettings, $setting);
        }
        if(is_array($setting)) {
          if ( is_null($specialSettings) && !count(array_intersect_key($this->transformKeys, $setting)) ) {
            // This array is not a transform
            if (!array_key_exists('url', $setting)) { $setting['url'] = true; }
            if (!array_key_exists('fallback', $setting)) { $setting['fallback'] = true; }
            $setting['limit'] = 1;
            $specialSettings = $setting;
          } else if (is_null($transformSettings)) {
            $transformSettings = $setting;
          }
        }
      }
      if (count($settings)==count($settings,COUNT_RECURSIVE)) {
        $settings = array_merge($settings, [['limit' => 1, 'url' => true, 'fallback' => true]]);
      }
    }

    $specialSettings = count($specialSettings) == 0 ? ['limit' => 1, 'url' => true, 'fallback' => true] : $specialSettings;

    $newSettings = array_merge([$entry], $stringSettings);

    if (!is_null($transformSettings)) {
      $newSettings = array_merge($newSettings, [$transformSettings]);
    }

    $newSettings = array_merge($newSettings, [$specialSettings]);

    return call_user_func_array(array($this, 'images'), $newSettings);
  }

  // Wrapper
  private function wrap($wrap, $output, $count) {
    // Check is wrapper plugin is installed.
    $wrapperPlugin = craft()->plugins->getPlugin('wrapper', false);

    if ($wrapperPlugin->isInstalled && $wrapperPlugin->isEnabled) {
      $wrapper = new wrapper();

      if (is_array($wrap) ) {

        // Set a new  array with the output values first
        $wrapperArguments = array($output);

        // Add the count
        array_push($wrapperArguments, $count);

        // If settings are applied, add this also
        if (count($wrap) >= 1) {
          $wrapperArguments = array_merge($wrapperArguments, $wrap);
        }

        // Call the wrapper function, along with an array that will pass over as paremeters
        return call_user_func_array(array($wrapper, 'wrapFilter'), $wrapperArguments);

      } else if (is_string($wrap)) {
        // If just a string, pass this over as the only paramter
        return $wrapper->wrapFilter($output, $wrap);
      }
    }
  }

  private $imageDirectory = null;
  private $systemPath = null;
  // private $allTransforms  = null;
  private $transformKeys  = null;

  public function __construct() {
    $this->transformKeys = array_flip(['mode', 'position', 'quality', 'format']);
    // $this->allTransforms = craft()->assetTransforms->getAllTransforms();
    $this->imageDirectory = craft()->image->imageDirectory;
    $this->systemPath = craft()->image->systemPath;


  }


}
