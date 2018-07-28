<?php
namespace Craft;

use Twig_Extension;
use Twig_Filter_Method;

class imageinfo extends \Twig_Extension {

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
      'tone'        => new Twig_Filter_Method( $this, 'tone' ),
      'format'      => new Twig_Filter_Method( $this, 'format' )
    );
  }

  // Get local image information
  public function imageinfo($imageUrl) {
    // Atleast one string should be passed
    if ( func_num_args() < 1 ){
      return false;
    }

    // Check if file exists as normal
    $newImageUrl = file_exists($this->systemPath.$imageUrl) ? $this->systemPath.$imageUrl : null;

    // Also check if the files exists even if the image directory wasn't concatonated to the string
    $newImageUrl = file_exists($this->systemPath.$this->imageDirectory.'/'.$imageUrl) ? $this->systemPath.$this->imageDirectory.'/'.$imageUrl : $newImageUrl;

    // And similar to the previous check, do the same thing but ommit the forward slash, just incase it wasn't added.
    $newImageUrl = file_exists($this->systemPath.$this->imageDirectory.$imageUrl) ? $this->systemPath.$this->imageDirectory.$imageUrl : $newImageUrl;

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
        $units = array("bytes", "kb", "mb", "gb", "tb");
        $size = floor(log($filesize, 1024));
        $exactSize = $filesize/pow(1024, $size);
        $roundedSize = round($exactSize, 2);
        $filesize = $roundedSize.$units[$size];
        // $filesize = (round($filesize/pow(1024, ($i = floor(log($filesize, 1024)))), 2) . array("bytes", "kb", "mb", "gb", "tb")[$i]);
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
  public function width($file) {
    return $this->imageinfo($file)['width'];
  }

  // {{ 'logo.png')|height }}
  public function height($file) {
    return $this->imageinfo($file)['height'];
  }

  // {{ 'logo.png')|format }}
  public function format($file) {
    return $this->imageinfo($file)['format'];
  }

  // {{ 'logo.png')|filesize }}
  public function filesize($file) {
    return $this->imageinfo($file)['filesize'];
  }

  // {{ 'logo.png')|filesize }}
  public function tone($file, $focus=false, $samples=10) {
    $imageTone = craft()->image_tone->tone($file, $focus, $samples);
    if (is_bool($imageTone)) {
      return $imageTone ? 'light' : 'dark';
    }
  }

  // {{ 'logo.png')|ori }} or // {{ 'logo.png')|orientation }}
  public function orientation($file) {
    $orientation = $this->imageinfo($file)['orientation'];
    if (!empty($file)) {
      return "data-orientation='".$orientation."'";
    } else {
      return $orientation;
    }
  }

  private $imageDirectory = null;
  private $systemPath = null;

  public function init() {
    $this->imageDirectory = craft()->image->imageDirectory;
    $this->systemPath = craft()->image->systemPath;
  }


}
