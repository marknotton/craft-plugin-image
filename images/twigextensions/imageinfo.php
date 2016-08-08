<?php
namespace Craft;

use Twig_Extension;
use Twig_Filter_Method;

class images extends \Twig_Extension {

  public function getName() {
    return Craft::t('Image Information');
  }

  public function getFilters() {
    return array(
      'ori'         => new Twig_Filter_Method( $this, 'orientation' ),
      'orientation' => new Twig_Filter_Method( $this, 'orientation' ),
      'imageinfo'   => new Twig_Filter_Method( $this, 'imageinfo'),
      'filesize'    => new Twig_Filter_Method( $this, 'filesize' ),
      'width'       => new Twig_Filter_Method( $this, 'width' ),
      'height'      => new Twig_Filter_Method( $this, 'height' ),
      'format'      => new Twig_Filter_Method( $this, 'format' )
    );
  }

  // Get local image information
  public function imageinfo() {
    // Atleast one string should be passed
    if ( func_num_args() < 1 ){
      return false;
    }

    // The first argument is the string that is automatically passed.
    $imageUrl = func_get_arg(0);

    $settingsArgs = array_slice(func_get_args(), 1);

    // Check if file exists as normal
    $newImageUrl = file_exists(getcwd().$imageUrl) ? getcwd().$imageUrl : null;

    // Also check if the files exists even if the image directory wasn't concatonated to the string
    $newImageUrl = file_exists(getcwd().$this->imageDirectory.'/'.$imageUrl) ? getcwd().$this->imageDirectory.'/'.$imageUrl : $newImageUrl;

    // And similar to the previous check, do the same thing but ommit the forward slash, just incase it wasn't added.
    $newImageUrl = file_exists(getcwd().$this->imageDirectory.$imageUrl) ? getcwd().$this->imageDirectory.$imageUrl : $newImageUrl;

    if (!is_null($newImageUrl)) {

      $imageInfo = getImageSize($newImageUrl);

      $width = $imageInfo[0];
      $height = $imageInfo[1];

      // Check file extension
      switch ($imageInfo['mime']) {
        case 'image/gif':
          $extension = 'gif';
        break;
        case 'image/jpeg':
          $extension = 'jpg';
        break;
        case 'image/png':
          $extension = 'png';
        break;
        default:
          $extension = end(explode('.', $name_of_file)) == "svg" ? 'svg' : null;
        break;
      }

      // Check filesize
      $filesize = filesize($newImageUrl);
      if ($filesize == 0) {
        return('n/a');
      } else {
        $filesize = (round($filesize/pow(1024, ($i = floor(log($filesize, 1024)))), 2) . array("bytes", "kb", "mb", "gb", "tb")[$i]);
      }

      // Check orientation
      if ($width == $height) {
        $orientation = "square";
      } else if ($width > $height) {
        $orientation = "landscape";
      } else {
        $orientation = "portrait";
      }

      // Create new image info array with dimensions, file format, filesize and orientation.
      $newImageInfo['width']       = $width;
      $newImageInfo['height']      = $height;
      $newImageInfo['format']      = $extension;
      $newImageInfo['filesize']    = $filesize;
      $newImageInfo['orientation'] = $orientation;

      return $newImageInfo;

    } else {
      $newImageInfo['width']       = "Image width not found.";
      $newImageInfo['height']      = "Image height not found.";
      $newImageInfo['format']      = "Image format not found.";
      $newImageInfo['filesize']    = "Image filesize not found.";
      $newImageInfo['orientation'] = "Image orientation not found.";

      return $newImageInfo;
    }
  }

  // {{ 'logo.png')|width }}
  public function width() {
    return $this->imageinfo(func_get_arg(0))['width'];
  }

  // {{ 'logo.png')|height }}
  public function height() {
    return $this->imageinfo(func_get_arg(0))['height'];
  }

  // {{ 'logo.png')|format }}
  public function format() {
    return $this->imageinfo(func_get_arg(0))['format'];
  }

  // {{ 'logo.png')|filesize }}
  public function filesize() {
    return $this->imageinfo(func_get_arg(0))['filesize'];
  }

  // {{ 'logo.png')|ori }} or // {{ 'logo.png')|orientation }}
  public function orientation() {
    $orientation = $this->imageinfo(func_get_arg(0))['orientation'];
    if (!empty(array_slice(func_get_args(), 1))) {
      return "data-orientation='".$orientation."'";
    } else {
      return $orientation;
    }
  }

  public $imageDirectory = null;
  public $allTransforms  = null;
  public $transformKeys  = null;

  public function __construct() {
    $this->transformKeys = array_flip(['mode', 'position', 'quality', 'format']);
    $this->allTransforms = craft()->assetTransforms->getAllTransforms();
    $this->imageDirectory = (array_key_exists('images' ,craft()->config->get('environmentVariables')) ? craft()->config->get('environmentVariables')["images"] : "/assets/images");
  }


}
