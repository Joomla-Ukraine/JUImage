# JUImage
JUImage - library for render thumbs.

Create thumbs for Joomla! extension or stand-alone use.

## Demo (All thumbs)

* [Bad Android](https://bad-android.com)
* [Високий замок](https://wz.lviv.ua)
* [Львівська міська рада](http://city-adm.lviv.ua)

## Use in Joomla! Extension

* [JUNewsUltra](https://github.com/Joomla-Ukraine/JUNewsUltra)
* [JUMultiThumb](https://github.com/Joomla-Ukraine/JUMultiThumb)
* JURSSPublisher

## Usage

### Stand-alone

#### Composer Install

`composer require joomla-ua/juimage`

You can then later update Guzzle using composer:

`composer update`

#### Code example

After installing, you need to require Composer's autoloader:

```
require_once('vendor/autoload.php');

$root_path = $_SERVER['DOCUMENT_ROOT'];
$juImg     = new JUImage\Image( $root_path );

$thumb = $juImg->render('images/sampledata/fruitshop/apple.jpg', [
	'w'     => '300',
	'h'     => '100',
	'q'     => '77',
	'cache' => 'img'
]);

echo '<img src=". $thumb ."' alt="Apple" width="300" height="100">';
```

### Joomla! Integration

#### Install
Install extention library (lib_juimage_v3.x.x.zip) using Joomla! Extension Manager. 

#### Code example
Code for use in your extension.

```
JLoader::register('JUImage',  JPATH_LIBRARIES . '/juimage/JUImage.php');

$juImg = new JUImage();

$thumb = $juImg->render('images/sampledata/fruitshop/apple.jpg', [
	'w'     => '300',
	'h'     => '100',
	'q'     => '77',
	'cache' => 'img'
]);

echo '<img src="'. $thumb .'" alt="Apple" width="300" height="100">';
```

or

```
require_once(JPATH_SITE . '/libraries/juimage/vendor/autoload.php');

$juImg = new JUImage\Image();

$thumb = $juImg->render('images/sampledata/fruitshop/apple.jpg', [
	'w'     => '300',
	'h'     => '100',
	'q'     => '77',
	'cache' => 'img'
]);

echo '<img src="'. $thumb .'" alt="Apple" width="300" height="100">';
```
	 
### WebP support

```
<?php

$thumb = $juImg->render('images/sampledata/fruitshop/apple.jpg', [
	'w'     	=> '300',
	'h'     	=> '100',
	'q'         => '95',
	'webp'      => true,
	'webp_q'    => '80',
	'webp_maxq' => '85',
]);
?>

<picture>
	<source srcset="<?php echo $thumb->webp; ?>" type="image/webp">
	<img src="<?php echo $thumb->img; ?>" alt="Apple" width="300" height="100">
</picture>
```

Display as:

```
<picture>
	<source srcset="img/apple.jpg.webp" type="image/webp">
	<img src="img/apple.jpg" alt="Apple" width="300" height="100">
</picture>
```

| WebP command | Type | Default | Description
| --- | --- | --- | --- |
| webp | Boolean | false | If `true` add support WebP image. For this option use tag `<picture>`, see in example. |
| webp_q | An integer between 0-100 | auto | Only relevant, when quality is set to "auto". |
| webp_maxq | An integer between 0-100 | 85 | Only relevant, when quality is set to "auto". |

### Image size support

```
<?php

$thumb = $juImg->render('images/sampledata/fruitshop/apple.jpg', [
	'w' => '300'
]);

// Image size for thumb
$size = $juImg->size($thumb);
	
echo '<img src="'. $thumb .'" alt="Apple" width="'. $size->width .'" height="'. $size->height .'">';	
```

## Options

Add option to this array:

```
[
  	'w'     => '300',
  	'h'     => '100',
  	'q'     => '77',
  	'cache' => 'img'
]
```

| Command | Description |
| --- | --- |
| cache | folder for thumbnails|
|   w | max width of output thumbnail in pixels|
|   h | max height of output thumbnail in pixels|
|  wp | max width for portrait images|
|  hp | max height for portrait images|
|  wl | max width for landscape images|
|  hl | max height for landscape images|
|  ws | max width for square images|
|  hs | max height for square images|
|   f | output image format ("jpeg", "png", or "gif")|
|   q | JPEG compression (1=worst, 95=best, 75=default)|
|  sx | left side of source rectangle (default \| 0) (values 0 < sx < 1 represent percentage)|
|  sy | top side of source rectangle (default \| 0) (values 0 < sy < 1 represent percentage)|
|  sw | width of source rectangle (default \| fullwidth) (values 0 < sw < 1 represent percentage)|
|  sh | height of source rectangle (default \| fullheight) (values 0 < sh < 1 represent percentage)|
|  zc | zoom-crop. Will auto-crop off the larger dimension so that the image will fill the smaller dimension (requires both "w" and "h", overrides "iar", "far"). Set to "1" or "C" to zoom-crop towards the center, or set to "T", "B", "L", "R", "TL", "TR", "BL", "BR" to gravitate towards top/left/bottom/right directions (requies ImageMagick for values other than "C" or "1")|
|  bg | background hex color (default | FFFFFF)|
|  bc | border hex color (default | 000000)|
| xto | EXIF Thumbnail Only - set to only extract EXIF thumbnail and not do any additional processing|
|  ra | Rotate by Angle: angle of rotation in degrees positive \| counterclockwise, negative \| clockwise|
|  ar | Auto Rotate: set to "x" to use EXIF orientation stored by camera. Can also be set to "l" or "L" for landscape, or "p" or "P" for portrait. "\l" and "P" rotate the image clockwise, "L" and "p" rotate the image counter-clockwise.|
| sfn | Source Frame Number - use this frame/page number for multi-frame/multi-page source images (GIF, TIFF, etc)|
| aoe | Output Allow Enlarging - 1=on, 0=off. "far" and "iar" both override this and allow output larger than input)|
| iar | Ignore Aspect Ratio - disable proportional resizing and stretch image to fit "h" & "w" (which must both be set).  (1=on, 0=off)  (overrides "far")|
| far | Force Aspect Ratio - image will be created at size specified by "w" and "h" (which must both be set). Alignment: L=left,R=right,T=top,B=bottom,C=center. BL,BR,TL,TR use the appropriate direction if the image is landscape or portrait.|
| dpi | Dots Per Inch - input DPI setting when importing from vector image format such as PDF, WMF, etc
| sia | Save Image As - default filename to save generated image as. Specify the base filename, the extension (eg: ".png") will be automatically added|
|maxb | MAXimum Byte size - output quality is auto-set to fit thumbnail into "maxb" bytes  (compression quality is adjusted for JPEG, bit depth is adjusted for PNG and GIF)|

## License

GNU General Public License version 3 or later; see [LICENSE.md](LICENSE.md)

## Software used 

JUImage is based on the [phpThumb() Class ](https://github.com/JamesHeinrich/phpThumb) (James Heinrich), [webp-convert
](https://github.com/rosell-dk/webp-convert) (Bjørn Rosell) and [fast-image-size library](https://github.com/marc1706/fast-image-size) (Marc Alexander).

## Sponsors

[![JetBrains](https://avatars0.githubusercontent.com/u/878437?s=200&v=4)](https://www.jetbrains.com/)

Thanks to [JetBrains](https://www.jetbrains.com/) for supporting the project through sponsoring some [All Products Packs](https://www.jetbrains.com/products.html) within their [Free Open Source License](https://www.jetbrains.com/buy/opensource/) program.