<?php


class Image_WatermarkExtension extends DataExtension {
  
	public function Watermark($position = null, $transparency = null) {
		$config = SiteConfig::current_site_config();

		$watermark = $config->Watermark();
		if(!$transparency) $transparency = $config->WatermarkTransparency;
		if(!$position) $position = $config->WatermarkPosition;

		$img = $this->owner->getFormattedImage('WatermarkedImage', $watermark, $position, $transparency);

		return $img;
	}

	public function generateWatermarkedImage(Image_Backend $backend, $watermark, $position, $transparency) {
		return $backend->watermark($watermark, $position, $transparency);
	}
}
