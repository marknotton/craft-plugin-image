<?php
namespace Craft;

class Image_ToneService extends BaseApplicationComponent {

  // Convert Hex to RGBA
	public function tone($filename, $focus=false, $samples=10) {

		$imageDirectory = craft()->image->imageDirectory;
		$systemPath = craft()->image->systemPath;

		if (isset($filename)) {
			if (file_exists($systemPath.$filename)) {
				$filepath = $systemPath.$filename;
				return $this->_get_image_brightness($filepath, $focus, $samples);
			} else if (strpos($filename, $imageDirectory) !== false && file_exists($systemPath.$imageDirectory.$filename)) {
				$filepath = $systemPath.$imageDirectory.$filename;
				return $this->_get_image_brightness($filepath, $focus, $samples);
			}
		} else {
			return null;
		}

	}

  // get average luminance, by sampling $samples times in both x,y directions
  // http://stackoverflow.com/a/5959461/843131

  private function _get_image_brightness($filename, $focus=false, $samples=10) {
    $image = null;
		$dev = false;

    if (exif_imagetype($filename) == IMAGETYPE_GIF) {
      $image = imagecreatefromgif($filename);
    } elseif (exif_imagetype($filename) == IMAGETYPE_JPEG) {
      $image = imagecreatefromjpeg($filename);
    } elseif (exif_imagetype($filename) == IMAGETYPE_PNG) {
      $image = imagecreatefrompng($filename);
    } else {
      return null;
    }

		// $focus = "top";


		switch ($focus) {
			case "top-left":
					$width  = imagesx($image)/2;
					$height = imagesy($image)/2;
					$stepX  = intval($width/$samples);
					$stepY  = intval($height/$samples);
				break;
			case "top":
					$width  = imagesx($image);
					$height = imagesy($image)/2;
					$stepX  = intval($width/$samples);
					$stepY  = intval($height/$samples);
				break;
			case "top-right":

				break;
			case "left":
				$width  = imagesx($image)/2;
				$height = imagesy($image);
				$stepX  = intval($width/$samples);
				$stepY  = intval($height/$samples);
				break;
			case "right":

				break;
			case "bottom-left":
				$width  = imagesx($image);
				$height = imagesy($image);
				$stepX  = intval($width/$samples*2);
				$stepY  = intval($height/$samples);
				break;
			case "bottom":

				break;
			case "bottom-right":

				break;
			default:
				$width  = imagesx($image);
				$height = imagesy($image);
				$stepX  = intval($width/$samples);
				$stepY  = intval($height/$samples);
		}


    $totalBrightness = 0;

    $sampleCount = 0;

		if ( $dev ) { $colours = array(0); }

    for ($x = 0; $x < $width; $x += $stepX) {
      for ($y = 0; $y < $height; $y += $stepY) {
        $sampleCount++;

        $rgba = imagecolorat($image, $x, $y);

        $red = ($rgba >> 16) & 0xFF;
        $green = ($rgba >> 8) & 0xFF;
        $blue = $rgba & 0xFF;
        $alpha = ($rgba >> 24) & 0x7F;

        // Ignore any transparent pixels that are below 50% transparency
        if ($alpha <= 127/2) {
          $brightness = ($red + $red + $blue + $green + $green + $green)/6;
          $totalBrightness += $brightness;
					if ( $dev ) { array_push($colours, implode(",",[$red, $green, $blue, 1])); }
        } else {
					if ( $dev ) { array_push($colours, implode(",",[$red, $green, $blue, 0.5])); }
				}

      }
    }
		if ( $dev ) {
			$y = $x = 0;
			for ($i = 1; $i <= $sampleCount; $i++) {
				$y = $y + $samples;
				echo "<div style='background-color:rgba(".$colours[$i]."); font-size:".$samples."px; text-align:center; left:".($x*3)."px; top:".($y*3)."px; line-height:".($samples*3)."px; overflow:hidden; width:".($samples*3)."px; height:".($samples*3)."px; display:block; position:absolute; '>$i</div>";
				if ($i % $samples == 0) {
					$y = 0;
					$x = $x + $samples;
				}
			}
			die;
		}

    imagedestroy($image);

    $finalCalculation = $totalBrightness/$sampleCount > 170;

    return $finalCalculation;

  }


}
