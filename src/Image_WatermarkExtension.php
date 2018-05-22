<?php

namespace samthejarvis\SilverStripe\Watermarking;

use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\SiteConfig\SiteConfig;
use Intervention\Image\ImageManagerStatic;

class Image_WatermarkExtension extends DataExtension {
  
    public function Watermark($position = null, $transparency = null) {
        $config = SiteConfig::current_site_config();
        $variant = $this->owner->variantName(__FUNCTION__, $position, $transparency);

        $watermark = $config->Watermark();
        if (!$transparency) {
            $transparency = $config->WatermarkTransparency;
        }
        if (!$position) {
            $position = $config->WatermarkPosition;
        }

        return $this->owner->manipulateImage($variant, function (Image_Backend $backend) use ($watermark, $position, $transparency) {
            $clone = clone $backend;
            $resource = clone $backend->getImageResource();

            if (empty($watermark) || !($watermark instanceof Image) || !$watermark->ID) {
                return $backend->resize($image_width, $image_height);
            }

            // original image
            $image_width = $backend->getWidth();
            $image_height = $backend->getHeight();
            
            // watermark should not cover more than 25% of original image
            $watermark_width = ceil($image_width / 2);
            $watermark_height = ceil($image_height / 2);

            $watermark = $watermark->Fit($watermark_width, $watermark_height);
            
            $transparency = ceil($transparency);
            if ($transparency > 100 || is_null($transparency)) {
              $transparency = 100;
            } else if ($transparency < 0) {
              $transparency = 0;
            }

            $opacity = 100 - $transparency;

            //$watermark->opacity($opacity);

            $watermark_path = $watermark->getAbsoluteURL();
            list($watermark_width, $watermark_height, $watermark_type) = getimagesize($watermark_path);

            $watermark_width = ceil($image_width / 2);
            $watermark_height = ceil($image_height / 2);
            /**
             * numbers represent the positions on the number pad of a keyboard
             */
            switch ($position) {
              case 9:
                $position = "top-right";
                break;
              case 8:
                $position = "top";
                break;
              case 7:
                $position = "top-left";
                break;
              case 6:
                $position = "right";
                break;
              case 5:
                $position = "center";
                break;
              case 4:
                $position = "left";
                break;
              case 3:
                $position = "bottom-right";
                break;
              case 2:
                $position = "bottom";
                break;
              case 1:
                $position = "bottom-left";
                break;
              default:
                $position = null;
                break;
            }

            switch ($watermark_type) {
              case 1:
                $watermark = imagecreatefromgif($watermark_path);
                break;
              case 2:
                $watermark = imagecreatefromjpeg($watermark_path);
                break;
              case 3:
                $watermark = imagecreatefrompng($watermark_path);
                break;
            }
            
            /** Old code for reference
             * 
             * $image_source = $backend->getImageResource();
             *
             * $tmp = imagecreatetruecolor($watermark_width, $watermark_height);
             * imagecopy($tmp, $image, 0, 0, $dest_x, $dest_y, $watermark_width, $watermark_height);
             * imagecopy($tmp, $watermark, 0, 0, 0, 0, $watermark_width, $watermark_height);
             * imagecopymerge($image, $tmp, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $transparency);
            */   
            
            $watermark = ImageManagerStatic::make($watermark_path)->opacity($opacity);

            if ($position) {
              $resource->insert($watermark, $position);
            } else {
              $watermark = $watermark->getCore();
              $temp = imagecreatetruecolor($image_width, $image_height);
              imagesavealpha($temp, true);
              imagesettile($temp, $watermark);
              imagefill($temp, 0, 0, IMG_COLOR_TILED);
              $watermark = ImageManagerStatic::make($temp);
              $resource->fill($watermark);
            }

            $clone->setImageResource($resource);
            return $clone->resize($image_width, $image_height);
        });
    }

}
