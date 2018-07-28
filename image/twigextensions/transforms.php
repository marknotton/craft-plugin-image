<?php
namespace Craft;

use Twig_Extension;
use Twig_Filter_Method;

class transforms extends \Twig_Extension {

  public function getName() {
    return Craft::t('Transforms');
  }

  public function getFilters() {
    return array(
      'transforms' => new Twig_Filter_Method( $this, 'transforms', array('is_safe' => array('html'))),
    );
  }

  public function transforms($data, $transform) {
    if(is_object($data)) {
      $data = $data->getRawContent();
    }
    preg_match_all('/\{asset\:(\d+)\:url\}/', $data, $matches);
    $ecm = craft()->elements->getCriteria(ElementType::Asset);
    $ecm->id = $matches[1];
    $assets = $ecm->find();

    foreach($assets as $key => $asset) {
        $data = str_replace('{asset:'.$asset->id.':url}', $this->transform($asset, $transform), $data);
    }
    return craft()->elements->parseRefs($data);
  }

  private function transform()	{

    // Atleast one browser sting arugment should be passed
    if ( func_num_args() < 1 ){
      return false;
    }

    $valid = null;

    $arguments = func_get_args();

    $image = $arguments[0];

    $transform = isset($arguments[1]) ? $arguments[1] : null;

    $cloudinary = craft()->plugins->getPlugin('cloudinary', false);

    if ( $cloudinary->isInstalled && $cloudinary->isEnabled ) {

      return craft()->cloudinary->transform($image, $transform);

    } else {

  		if (is_null($transform) ) {
  			return $image;
  		}

  		// If an object was passed, assume this is asset and query the URL.
  		$url = gettype($image) === 'object' ? $image->getUrl($transform) : $image;

      return $url;
    }

	}

}
