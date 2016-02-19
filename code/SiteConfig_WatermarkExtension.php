<?php

class SiteConfig_WatermarkExtension extends DataExtension {
	static $db = array(
		"WatermarkTransparency" => "Int",
		"WatermarkPosition" => "Enum('1, 2, 3, 4, 5, 6, 7, 8, 9')"
	);

	static $has_one = array(
		"Watermark" => "Image"
	);

	function updateCMSFields(FieldList $fields) {

		$transparency_options = array_combine(range(10, 100, 10), range(10, 100, 10));
		array_walk($transparency_options, function (&$value, $key) {
			$value.="%";
		});


		$position_names = array(
			"Bottom left",
			"Bottom",
			"Bottom right",
			"Left",
			"Center",
			"Right",
			"Top left",
			"Top",
			"Top right"
		);
		$position_options = array_combine($this->owner->dbObject('WatermarkPosition')->enumValues(), $position_names);

		$fields->addFieldsToTab("Root.Watermarking", array(
			UploadField::create("Watermark")
				->setFolderName("watermarks"),
			DropdownField::create("WatermarkTransparency", "Watermark transparency", $transparency_options),
			DropdownField::create("WatermarkPosition", "Watermark position", $position_options)	
		));
	}
}