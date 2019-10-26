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
use phpthumb;
use WebPConvert\WebPConvert;

/**
 * JUImage library for render thumbs
 *
 * @since  3.0
 */
class Image
{
	protected $config;
	private $path;
	private $img_blank;

	/**
	 * Image constructor.
	 *
	 * @param array $config
	 */
	public function __construct(array $config = [])
	{
		$this->path      = isset($config[ 'root_path' ]) ? $config[ 'root_path' ] : JPATH_BASE;
		$this->img_blank = isset($config[ 'img_blank' ]) ? $config[ 'img_blank' ] : 'libraries/juimage/';
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
		if($url !== 'cover')
		{
			$url = trim($url, '/');
			$url = trim($url);
			$url = rawurldecode($url);

			$_error = false;
			if(strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0)
			{
				if($this->createVideoThumb($url, true))
				{
					$url = $this->createVideoThumb($url);
				}

				$headers = get_headers($url);
				if(strpos($headers[ 0 ], '200') === false)
				{
					$_error = true;
				}
			}
			else
			{
				$url = $this->path . '/' . $url;
				if(!file_exists($url))
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

		if(!empty($attr) && is_array($attr))
		{
			foreach($attr as $key => $value)
			{
				if($key === 'f')
				{
					$file_ext[] = $value;
				}

				if($key === 'w' || $key === 'h')
				{
					$img_size[] = $value;
				}

				if($key === 'cache')
				{
					$img_cache[] = $value;
				}

				if($key === 'error_image')
				{
					$error_image[] = $value;
				}
			}
		}

		$file_ext  = implode($file_ext);
		$file_ext  = '.' . ($file_ext === '' ? 'jpg' : $file_ext);
		$img_cache = implode($img_cache);
		$img_cache = $img_cache === '' ? 'cache' : $img_cache;

		if($_error === true)
		{
			$error_image = implode($error_image);
			$url         = $error_image === '' ? $this->path . '/' . $this->img_blank . 'noimage.png' : $error_image;
		}

		$img_size  = implode('x', $img_size);
		$img_size  = ($img_size === '' ? '0' : $img_size);
		$subfolder = $img_cache . '/' . $img_size . '/' . strtolower(substr(hash('crc32b', $img_name), -1));

		$uri_attr = [];
		if(!empty($attr) && is_array($attr))
		{
			foreach($attr as $k => $v)
			{
				$f          = explode('_', $k);
				$k          = $f[ 0 ];
				$uri_attr[] = $k . $v;
			}
		}

		$target = $subfolder . '/' . strtolower(substr($img_url, 0, 150)) . '-' . hash('crc32b', $url . implode('.', $uri_attr)) . $file_ext;

		$this->makeDir($this->path . '/' . $subfolder);

		if(file_exists($this->path . '/' . $target))
		{
			$output = $target;
		}
		else
		{
			$output = $this->createThumb($url, $img_cache, $target, $attr);
		}

		if(isset($attr[ 'webp' ]) === true)
		{
			$this->createWebPThumb($this->path . '/' . $output, [
				'q'         => isset($attr[ 'q' ]) ? $attr[ 'q' ] : 'auto',
				'webp_q'    => isset($attr[ 'webp_q' ]) ? $attr[ 'webp_q' ] : 'auto',
				'webp_maxq' => isset($attr[ 'webp_maxq' ]) ? $attr[ 'webp_maxq' ] : '85',
			]);

			$output = (object) [
				'img'  => $output,
				'webp' => $output . '.webp'
			];
		}

		return $output;
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
	 * @param      $url
	 *
	 * @param bool $video_detect
	 *
	 * @return bool|string
	 *
	 * @since 3.0
	 */
	private function createVideoThumb($url, $video_detect = false)
	{
		$urls = parse_url($url);

		if($video_detect === true)
		{
			return $urls[ 'host' ] === 'youtu.be' || $urls[ 'host' ] === 'youtube.com' || $urls[ 'host' ] === 'www.youtube.com' || $urls[ 'host' ] === 'vimeo.com';
		}

		$yid = '';
		$vid = '';
		if($urls[ 'host' ] === 'youtu.be')
		{
			$yid = ltrim($urls[ 'path' ], '/');
		}
		elseif($urls[ 'host' ] === 'vimeo.com')
		{
			$vid = ltrim($urls[ 'path' ], '/');
		}
		elseif(strpos($urls[ 'path' ], 'embed') == 1)
		{
			$cut_embed = explode('/', $urls[ 'path' ]);
			$yid       = end($cut_embed);
		}
		elseif(strpos($url, '/') === false)
		{
			$yid = $url;
		}
		else
		{
			parse_str($urls[ 'query' ], $output);

			$yid     = $output[ 'v' ];
			$feature = '';
			if(!empty($feature))
			{
				$cut_feature = explode('v=', $urls[ 'query' ]);
				$yid         = end($cut_feature);
				$arr         = explode('&', $yid);
				$yid         = $arr[ 0 ];
			}
		}

		if($yid)
		{
			$yt_path = 'https://img.youtube.com/vi/' . $yid . '/';

			if($this->http($yt_path . 'maxresdefault.jpg') == 200)
			{
				return $yt_path . 'maxresdefault.jpg';
			}

			if($this->http($yt_path . 'hqdefault.jpg') == 200)
			{
				return $yt_path . 'hqdefault.jpg';
			}

			return $yt_path . 'default.jpg';
		}

		if($vid)
		{
			$vimeo = json_decode(file_get_contents('https://vimeo.com/api/v2/video/' . $vid . '.json'));

			if(!empty($vimeo))
			{
				return $vimeo[ 0 ]->thumbnail_large;
			}
		}

		return false;
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
		if(!file_exists($destination = $source . '.webp'))
		{
			$webp_maxq = [
				'max-quality' => ($options[ 'webp_maxq' ] > 90) ? 90 : ($options[ 'webp_maxq' ] + 10)
			];

			$webp_options = [
				'quality'    => $options[ 'q' ],
				'metadata'   => 'none',
				'converters' => [ 'imagick', 'gd' ]
			];

			$params = array_merge($webp_maxq, $webp_options);

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
		$phpThumb = new phpthumb();

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

		if($url === 'cover')
		{
			$cover = [];
			if(!empty($attr) && is_array($attr))
			{
				foreach($attr as $whk => $whv)
				{
					if($whk === 'cover')
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

		if(is_array($attr))
		{
			foreach($attr as $k => $v)
			{
				$f = explode('_', $k);
				$k = $f[ 0 ];

				$phpThumb->setParameter($k, $v);
			}
		}

		$imagemagick = '';
		if(0 === stripos(PHP_OS, 'WIN'))
		{
			$imagemagick = 'C:/ImageMagick/convert.exe';
		}

		$phpThumb->setParameter('config_imagemagick_path', $imagemagick);
		$phpThumb->setParameter('config_prefer_imagemagick', true);
		$phpThumb->setParameter('config_imagemagick_use_thumbnail', true);

		$output = '';
		if($phpThumb->GenerateThumbnail())
		{
			if($phpThumb->RenderToFile($this->path . '/' . $target))
			{
				$output = $target;
			}

			$phpThumb->purgeTempFiles();
		}

		return $output;
	}

	/**
	 * @param     $dir
	 * @param int $mode
	 *
	 * @return bool
	 * @since 3.0
	 */
	private function makeDir($dir, $mode = 0777)
	{
		if(@mkdir($dir, $mode, true) || is_dir($dir))
		{
			return true;
		}

		if(!$this->makeDir(dirname($dir)))
		{
			return false;
		}

		return @mkdir($dir, $mode, true);
	}

	/**
	 * @param $url
	 *
	 * @return bool|string
	 *
	 * @since 3.0
	 */
	private function http($url)
	{
		$header = get_headers($url);

		return substr($header[ 0 ], 9, 3);
	}
}