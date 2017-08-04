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
      'transforms' => new Twig_Filter_Method( $this, 'transforms', array('is_safe' => array('html')))
    );
  }

  public function transforms($data, $transform)
  {
    if(is_object($data)) {
      $data = $data->getRawContent();
    }
    preg_match_all('/\{asset\:(\d+)\:url\}/', $data, $matches);

    $ecm = craft()->elements->getCriteria(ElementType::Asset);
    $ecm->id = $matches[1];
    $assets = $ecm->find();

    foreach($assets as $key => $asset) {
        $data = str_replace('{asset:'.$asset->id.':url}', craft()->assets->getUrlForFile($asset, $transform), $data);
    }
    return $data;
  }

}
