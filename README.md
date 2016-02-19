Silverstripe Watermarking
=========================

This module adds a watermarking method for images. Chainable and available on the Image class.


### Installation

```
composer require samthejarvis/silverstripe-watermarking
```

Adds a Watermarking tab to /admin/settings. Here you can specify a watermark image as well as transparency and position defaults.

### Requirements
- SilverStripe Framework 3.1


### Usage

```html
$Image.Watermark

$Image.CroppedImage(300, 300).Watermark

$Image.CroppedImage(300, 300).Watermark(5, 50);
```

#### Position
The position parameter takes an integer from 1 to 9 and sets the watermark to appear to that number's corresponding position on a keypad.

E.g. 5 will set the watermark to appear in the center.

```
789
456
123
```

#### Transparency
The transparency parameter takes an integer from 0 to 100.


### Credit notes
Configurable GD watermarking based on [memdev/silverstripe-watermarkable](http://github.com/memdev/silverstripe-watermarkable).
