<?php

namespace samthejarvis\SilverStripe\Watermarking;

use SilverStripe\Dev\Debug;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\SiteConfig\SiteConfig;

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

        $image = $this->owner->manipulateImage($variant, function (Image_Backend $backend) use ($watermark, $position, $transparency) {
            
            if (!$backend->getImageResource()) {
                return;
            }

            if (empty($watermark) || !($watermark instanceof Image) || !$watermark->ID) {
                return false;
            }

            // original image
            $image_width = $backend->getWidth();
            $image_height = $backend->getHeight();
            
            // watermark should not cover more than 25% of original image
            $watermark_width = ceil($image_width / 2);
            $watermark_height = ceil($image_height / 2);
            if ($watermark->getWidth() > $watermark_width || $watermark->getHeight() > $watermark_height) {
              $watermark = $watermark->Fit($watermark_width, $watermark_height);
            }
            $watermark_path = $watermark->getAbsoluteURL();
            list($watermark_width, $watermark_height, $watermark_type) = getimagesize($watermark_path);
            
            /**
             * numbers represent the positions on the number pad of a keyboard
             */
            switch ($position) {
              case 9:
                $dest_x = $image_width - $watermark_width;
                $dest_y = 0;
                break;
              case 8:
                $dest_x =  ceil(($image_width / 2));
                $dest_x -= ceil(($watermark_width / 2));
                $dest_y = 0;
                break;
              case 7:
                $dest_x = 0;
                $dest_y = 0;
                break;
              case 6:
                $dest_x =  $image_width - $watermark_width;
                $dest_y =  ceil(($image_height / 2));
                $dest_y -= ceil(($watermark_height / 2));
                break;
              case 5:
                $dest_x =  ceil(($image_width / 2));
                $dest_x -= ceil(($watermark_width / 2));
                $dest_y =  ceil(($image_height / 2));
                $dest_y -= ceil(($watermark_height / 2));
                break;
              case 4:
                $dest_x =  0;
                $dest_y =  ceil(($image_height / 2));
                $dest_y -= ceil(($watermark_height / 2));
                break;
              case 3:
              default:
                $dest_x = $image_width - $watermark_width;
                $dest_y = $image_height - $watermark_height;
                break;
              case 2:
                $dest_x =  ceil(($image_width / 2));
                $dest_x -= ceil(($watermark_width / 2));
                $dest_y = $image_height - $watermark_height;
                break;
              case 1:
                $dest_x = 0;
                $dest_y = $image_height - $watermark_height;
                break;
            }
            
            $transparency = ceil($transparency);
            if ($transparency > 100 || is_null($transparency)) {
              $transparency = 100;
            } else if ($transparency < 0) {
              $transparency = 0;
            }
            
            $quality = Config::inst()->get(get_class($this->owner), 'default_quality');
            if (empty($quality)) {
              $quality = Config::inst()->get('GDBackend', 'default_quality');
            }
            if (empty($quality)) {
              $quality = 100;
            } else if ($quality > 100) {
              $quality = 100;
            } else if ($quality < 0) {
              $quality = 0;
            }
            
            switch ($watermark_type) {
              case 1:
                $watermark = imagecreatefromgif($watermark_path);
                break;
              case 2:
              default:
                $watermark = imagecreatefromjpeg($watermark_path);
                break;
              case 3:
                $watermark = imagecreatefrompng($watermark_path);
                break;
            }
            
            $image_source = $backend->getImageResource();
            $source_path = $image_source->basePath();

            list($source_width, $source_height, $source_type) = getimagesize($source_path);

            switch ($source_type) {
                case 1:
                  $image = imagecreatefromgif($source_path);
                  break;
                case 2:
                default:
                  $image = imagecreatefromjpeg($source_path);
                  break;
                case 3:
                  $image = imagecreatefrompng($source_path);
                  break;
              }

            $tmp = imagecreatetruecolor($watermark_width, $watermark_height);
            imagecopy($tmp, $image, 0, 0, $dest_x, $dest_y, $watermark_width, $watermark_height);
            imagecopy($tmp, $watermark, 0, 0, 0, 0, $watermark_width, $watermark_height);
            imagecopymerge($image, $tmp, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $transparency);

/** ###################################################################################
 *  #### THIS IS COMPLETE NONSENSE NOW!!! #####
 * 
 * I just need to re-set the resource to my new image.
 * but nothing works.
 * NOTHING!
 * 
 * $resource = $backend->getImageManager()->make($source_path);
 * $backend->setImageResource($resource);
 */ #####################################################################################

            return $backend->resize($image_width, $image_height);
        });

        return $image;
    }

}
