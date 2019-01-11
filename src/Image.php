<?php
/**
 * @package        JUImage
 * @subpackage     Class
 *
 * @author         Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C)  2016-2019 by Denys D. Nosov (https://joomla-ua.org)
 * @license        GNU General Public License version 2 or later
 *
 * @since          3.0
 */

namespace JUImage;

use FastImageSize\FastImageSize;
use WebPConvert\WebPConvert;

/**
 * JUImage library for render thumbs
 *
 * @since  3.0
 */
class Image
{
	protected $path;

	protected $img_blank;

	/**
	 * Image constructor.
	 *
	 * @param        $path
	 * @param string $img_blank
	 */
	public function __construct($path = JPATH_BASE, $img_blank = 'libraries/juimage/')
	{
		$this->path      = $path;
		$this->img_blank = $img_blank;
	}

	/**
	 * @param       $url
	 * @param array $attr
	 *
	 * @return object|string
	 *
	 * @since 3.0
	 */
	public function render($url, array $attr = [])
	{
		if( $url !== 'cover' )
		{
			$url = trim($url, '/');
			$url = trim($url);
			$url = rawurldecode($url);

			$_error = false;
			if( strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 )
			{
				$headers = @get_headers($url);
				if( strpos($headers[ 0 ], '200') === false )
				{
					$_error = true;
				}
			}
			else
			{
				$url = $this->path . '/' . $url;
				if( !file_exists($url) )
				{
					$_error = true;
				}
			}

			$img_file = pathinfo($url);
			$img_name = $img_file[ 'filename' ];
			$img_url  = strtolower($img_name);
			$img_url  = preg_replace('#[[:punct:]]#', '', $img_url);
			$img_url  = preg_replace('#[а-яёєїіА-ЯЁЄЇІ]#iu', '', $img_url);
			$img_url  = str_replace([ ' +', ' ' ], [ '_', '' ], $img_url);
		}
		else
		{
			$_error   = false;
			$img_name = implode($attr);
			$img_url  = 'cover';
		}

		$file_ext    = [];
		$img_size    = [];
		$img_cache   = [];
		$error_image = [];

		if( !empty($attr) && is_array($attr) )
		{
			foreach( $attr as $key => $value )
			{
				if( $key === 'f' )
				{
					$file_ext[] = $value;
				}

				if( $key === 'w' || $key === 'h' )
				{
					$img_size[] = $value;
				}

				if( $key === 'cache' )
				{
					$img_cache[] = $value;
				}

				if( $key === 'error_image' )
				{
					$error_image[] = $value;
				}
			}
		}

		$file_ext  = implode($file_ext);
		$file_ext  = '.' . ($file_ext === '' ? 'jpg' : $file_ext);
		$img_cache = implode($img_cache);
		$img_cache = $img_cache === '' ? 'cache' : $img_cache;

		if( $_error === true )
		{
			$error_image = implode($error_image);
			$url         = $error_image === '' ? $this->path . '/' . $this->img_blank . 'noimage.png' : $error_image;
		}

		$img_size  = implode('x', $img_size);
		$img_size  = ($img_size === '' ? '0' : $img_size);
		$subfolder = $img_cache . '/' . $img_size . '/' . strtolower(substr(md5($img_name), -1));

		$md5 = [];
		if( !empty($attr) && is_array($attr) )
		{
			foreach( $attr as $k => $v )
			{
				$f     = explode('_', $k);
				$k     = $f[ 0 ];
				$md5[] = $k . $v;
			}
		}

		$target = $subfolder . '/' . strtolower(substr($img_url, 0, 150)) . '-' . md5($url . implode('.', $md5)) . $file_ext;

		$this->makeDir($this->path . '/' . $subfolder);

		if( file_exists($this->path . '/' . $target) )
		{
			$outpute = $target;
		}
		else
		{
			$outpute = $this->createThumb($url, $img_cache, $target, $attr);
		}

		if( isset($attr[ 'webp' ]) === true )
		{
			$this->createWebPThumb($this->path . '/' . $outpute, [
				'q'         => isset($attr[ 'q' ]) ? $attr[ 'q' ] : 'auto',
				'webp_q'    => isset($attr[ 'webp_q' ]) ? $attr[ 'webp_q' ] : 'auto',
				'webp_maxq' => isset($attr[ 'webp_maxq' ]) ? $attr[ 'webp_maxq' ] : '85',
			]);

			$outpute = (object) [
				'img'  => $outpute,
				'webp' => $outpute . '.webp'
			];
		}

		return $outpute;
	}

	/**
	 * @param $img_path
	 *
	 * @return object
	 *
	 * @since 3.0
	 */
	public function size($img_path)
	{
		$size = (new FastImageSize)->getImageSize($img_path);

		return (object) [
			'width'  => $size[ 'width' ],
			'height' => $size[ 'height' ]
		];
	}

	/**
	 * @param       $source
	 * @param array $options
	 *
	 * @return bool
	 *
	 * @since 3.0
	 */
	private function createWebPThumb($source, array $options = [])
	{
		if( !file_exists($destination = $source . '.webp') )
		{
			$webp_maxq    = [ 'max-quality' => ($options[ 'webp_maxq' ] > 90) ? 90 : ($options[ 'webp_maxq' ] + 10) ];
			$webp_options = [
				'quality'    => $options[ 'q' ],
				'metadata'   => 'none',
				'converters' => [ 'imagick', 'gd' ]
			];
			$params       = array_merge($webp_maxq, $webp_options);

			return WebPConvert::convert($source, $destination, $params);
		}

		return false;
	}

	/**
	 * @param       $url
	 * @param       $img_cache
	 * @param       $target
	 * @param array $attr
	 *
	 * @return string
	 *
	 * @since 3.0
	 */
	private function createThumb($url, $img_cache, $target, array $attr = [])
	{
		$phpThumb = new \phpthumb();

		$phpThumb->resetObject();

		$phpThumb->setParameter('config_max_source_pixels', '0');
		$phpThumb->setParameter('config_temp_directory', $this->path . '/' . $img_cache . '/');
		$phpThumb->setParameter('config_cache_directory', $this->path . '/' . $img_cache . '/');

		$phpThumb->setCacheDirectory();

		$phpThumb->setParameter('config_cache_maxfiles', '0');
		$phpThumb->setParameter('config_cache_maxsize', '0');
		$phpThumb->setParameter('config_cache_maxage', '0');
		$phpThumb->setParameter('config_error_bgcolor', 'FAFAFA');
		$phpThumb->setParameter('config_error_textcolor', '770000');
		$phpThumb->setParameter('config_nohotlink_enabled', false);

		if( $url === 'cover' )
		{
			$cover = [];
			if( !empty($attr) && is_array($attr) )
			{
				foreach( $attr as $whk => $whv )
				{
					if( $whk === 'cover' )
					{
						$cover[] = $whv;
					}
				}
			}

			$phpThumb->setSourceFilename($this->path . '/' . $this->img_blank . 'blank.png');
			$phpThumb->setParameter('fltr', 'clr|' . implode($cover));
		}
		else
		{
			$phpThumb->setSourceFilename($url);
		}

		$phpThumb->setParameter('q', '82');
		$phpThumb->setParameter('aoe', '1');
		$phpThumb->setParameter('f', 'jpg');

		if( is_array($attr) )
		{
			foreach( $attr as $k => $v )
			{
				$f = explode('_', $k);
				$k = $f[ 0 ];

				$phpThumb->setParameter($k, $v);
			}
		}

		$imagemagick = '';
		if( 0 === stripos(PHP_OS, 'WIN') )
		{
			$imagemagick = 'C:/ImageMagick/convert.exe';
		}

		$phpThumb->setParameter('config_imagemagick_path', $imagemagick);
		$phpThumb->setParameter('config_prefer_imagemagick', true);
		$phpThumb->setParameter('config_imagemagick_use_thumbnail', true);

		$outpute = '';
		if( $phpThumb->GenerateThumbnail() )
		{
			if( $phpThumb->RenderToFile($this->path . '/' . $target) )
			{
				$outpute = $target;
			}

			$phpThumb->purgeTempFiles();
		}

		return $outpute;
	}

	/**
	 * @param     $dir
	 * @param int $mode
	 *
	 * @return bool
	 *
	 * @since 3.0
	 */
	private function makeDir($dir, $mode = 0777)
	{
		if( @mkdir($dir, $mode) || is_dir($dir) )
		{
			if( !file_exists($indexfile = $dir . '/index.html') )
			{
				$indexcontent = '<!DOCTYPE html><title></title>';
				$file         = fopen($indexfile, 'wb');

				fwrite($file, $indexcontent);
				fclose($file);
			}

			return true;
		}

		if( !$this->makeDir(dirname($dir)) )
		{
			return false;
		}

		return @mkdir($dir, $mode);
	}
}