<?php
/**
 * @since          5.0
 * @subpackage     Class
 *
 * @author         Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C)  2016-2023 by Denys D. Nosov (https://joomla-ua.org)
 * @license        GNU General Public License version 2 or later
 *
 * @package        JUImage
 */

namespace JUImage;

use FastImageSize\FastImageSize;
use phpthumb;

/**
 * JUImage library for render thumbs
 *
 * @since  5.0
 */
class Image
{
	/**
	 * @since 5.0
	 * @var mixed|string
	 */
	private $path;

	/**
	 * @since 5.0
	 * @var mixed|string
	 */
	private $img_blank;

	/**
	 * Image constructor.
	 *
	 * @param array $config
	 *
	 * @since 5.0
	 */
	public function __construct(array $config = [])
	{
		$this->path      = isset($config[ 'root_path' ]) ? $config[ 'root_path' ] : JPATH_BASE;
		$this->img_blank = isset($config[ 'img_blank' ]) ? $config[ 'img_blank' ] : 'libraries/juimage/noimage.png';
	}

	/**
	 * @param       $url
	 * @param array $attr
	 *
	 * @return object|string
	 * @return object|string
	 *
	 * @since 5.0
	 */
	public function render($url, array $attr = [])
	{
		$img = $this->thumb($url, $attr);

		$img_webp = [];
		if(isset($attr[ 'webp' ]) && $attr[ 'webp' ] === true)
		{
			$img_webp = [
				'webp' => $this->thumb($url, array_merge($attr, [ 'f' => 'webp' ]))
			];
		}

		$img_avif = [];
		if(isset($attr[ 'avif' ]) && $attr[ 'avif' ] === true)
		{
			$img_avif = [
				'avif' => $this->thumb($url, array_merge($attr, [ 'f' => 'avif' ]))
			];
		}

		if((isset($attr[ 'webp' ]) && $attr[ 'webp' ] === true) || (isset($attr[ 'avif' ]) && $attr[ 'avif' ] === true))
		{
			$img = (object) array_merge([ 'img' => $img ], $img_webp, $img_avif);
		}

		return $img;
	}

	/**
	 * @param $img_path
	 *
	 * @return object|bool
	 *
	 * @since 5.0
	 */
	public function size($img_path)
	{
		$size = (new FastImageSize)->getImageSize($img_path);

		if(isset($size) && $size)
		{
			return (object) [
				'width'  => $size[ 'width' ],
				'height' => $size[ 'height' ]
			];
		}

		return false;
	}

	/**
	 * @param       $url
	 * @param array $attr
	 *
	 * @return string
	 * @since 5.0
	 */
	private function thumb($url, array $attr = [])
	{
		$_error   = false;
		$img_name = implode($attr);
		$img_url  = 'cover';

		if($url !== 'cover')
		{
			$url = trim($url, '/');
			$url = trim($url);
			$url = rawurldecode($url);

			if(strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0)
			{
				if($this->createVideoThumb($url, true))
				{
					$url = $this->createVideoThumb($url);
				}

				$headers = get_headers($url, true);
				if(strpos($headers[ 0 ], '200') === false)
				{
					$_error = true;
				}
			}
			else
			{
				$url = $this->path . '/' . $url;
				if(!is_file($url))
				{
					$_error = true;
				}
			}

			$img_name = pathinfo($url)[ 'filename' ];
			$img_url  = strtolower($img_name);
			$img_url  = preg_replace('#[[:punct:]]#', '', $img_url);
			$img_url  = preg_replace('#[а-яёєїіЁЄЇІ]#iu', '', $img_url);
			$img_url  = str_replace([ ' +', ' ' ], [ '_', '' ], $img_url);
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

		// Error Image
		if($_error === true)
		{
			$error_image = implode($error_image);
			$url         = ($error_image === '' ? $this->path . '/' . $this->img_blank : $error_image);
		}

		// Image Size
		$img_size = implode('x', $img_size);
		$img_size = ($img_size === '' ? '0' : $img_size);

		// Image Name
		$img_name = hash('crc32b', $img_name);
		$img_name = substr($img_name, -1);
		$img_name = strtolower($img_name);

		// Path to Folder
		$subfolder = $img_cache . '/' . $img_size . '/' . $img_name;
		$uri_attr  = [];
		if(!empty($attr) && is_array($attr))
		{
			foreach($attr as $k => $v)
			{
				$f          = explode('_', $k);
				$k          = $f[ 0 ];
				$uri_attr[] = $k . $v;
			}
		}

		// Set Image name
		$img_url = substr($img_url, 0, 150);
		$img_url = strtolower($img_url);
		$img_url .= '-' . hash('crc32b', $url . implode('.', $uri_attr));
		$img_url .= $file_ext;

		// Image Path for target
		$target = $subfolder . '/' . $img_url;
		if(is_file($this->path . '/' . $target))
		{
			return $target;
		}

		$path = $this->path . '/' . $subfolder;
		if(!is_dir($path))
		{
			$this->makeDir($path);
		}

		return $this->createThumb($url, $img_cache, $target, $attr);
	}

	/**
	 * @param       $url
	 * @param       $img_cache
	 * @param       $target
	 * @param array $attr
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	private function createThumb($url, $img_cache, $target, array $attr = [])
	{
		$phpThumb = new phpthumb();

		$phpThumb->resetObject();

		$phpThumb->setParameter('config_allow_src_above_docroot', true);
		$phpThumb->setParameter('config_max_source_pixels', round(max((int) ini_get('memory_limit'), (int) get_cfg_var('memory_limit')) * 1048576 / 6)); // '0'
		$phpThumb->setParameter('config_temp_directory', $this->path . '/' . $img_cache . '/');
		$phpThumb->setParameter('config_cache_directory', $this->path . '/' . $img_cache . '/');

		$phpThumb->setCacheDirectory();

		$phpThumb->setParameter('config_cache_maxfiles', null);
		$phpThumb->setParameter('config_cache_maxsize', null);
		$phpThumb->setParameter('config_cache_maxage', null);
		$phpThumb->setParameter('config_cache_source_filemtime_ignore_local', true);
		$phpThumb->setParameter('config_error_die_on_error', true);
		$phpThumb->setParameter('config_error_die_on_source_failure', true);
		$phpThumb->setParameter('config_error_bgcolor', 'FAFAFA');
		$phpThumb->setParameter('config_error_textcolor', '770000');
		$phpThumb->setParameter('config_http_fopen_timeout', 600);

		$imagemagick = true;
		if(isset($attr[ 'imagemagick' ]) && $attr[ 'imagemagick' ] === true)
		{
			$imagemagick = $attr[ 'imagemagick' ];
		}

		$imagemagick_path = '';
		if(0 === stripos(PHP_OS, 'WIN'))
		{
			$imagemagick_path = 'C:/ImageMagick/convert.exe';
		}

		$phpThumb->setParameter('config_imagemagick_path', $imagemagick_path);
		$phpThumb->setParameter('config_prefer_imagemagick', $imagemagick);
		$phpThumb->setParameter('config_imagemagick_use_thumbnail', $imagemagick);

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

		if(isset($attr[ 'blur_bg' ]))
		{
			$loops = ($attr[ 'blur_bg' ] === '1' ? '20' : $attr[ 'blur_bg' ]);
			for($i = 1; $i <= $loops; $i++)
			{
				$phpThumb->setParameter('fltr', 'gblr');
			}
		}

		if(is_array($attr))
		{
			foreach($attr as $k => $v)
			{
				if($k === 'imagemagick')
				{
					continue;
				}

				$f = explode('_', $k);
				$k = $f[ 0 ];

				$phpThumb->setParameter($k, $v);
			}
		}

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
	 * @param      $url
	 *
	 * @param bool $video_detect
	 *
	 * @return bool|string
	 * @return bool|string
	 * @since 5.0
	 */
	private function createVideoThumb($url, $video_detect = false)
	{
		$urls = parse_url($url);
		if($video_detect === true)
		{
			return $urls[ 'host' ] === 'youtu.be' || $urls[ 'host' ] === 'youtube.com' || $urls[ 'host' ] === 'www.youtube.com' || $urls[ 'host' ] === 'vimeo.com';
		}

		if($urls[ 'host' ] === 'youtu.be')
		{
			$id = ltrim($urls[ 'path' ], '/');
			if(strpos($urls[ 'path' ], 'embed') == 1)
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

				$yid = $output[ 'v' ];
				if(isset($output[ 'feature' ]) || $output[ 'feature' ])
				{
					$cut_feature = explode('v=', $urls[ 'query' ]);
					$yid         = end($cut_feature);
					$arr         = explode('&', $yid);
					$yid         = $arr[ 0 ];
				}
			}

			if($yid)
			{
				return $this->youtube($id);
			}
		}

		if(($urls[ 'host' ] === 'vimeo.com') && $id = ltrim($urls[ 'path' ], '/'))
		{
			return $this->vimeo($id);
		}

		return false;
	}

	/**
	 * @param $id
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	private function youtube($id)
	{
		$yt_path = 'https://img.youtube.com/vi/' . $id . '/';

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

	/**
	 * @param $id
	 *
	 * @return false
	 *
	 * @since 5.0
	 */
	private function vimeo($id)
	{
		$vimeo = json_decode(file_get_contents('https://vimeo.com/api/v2/video/' . $id . '.json'), false);

		if(!empty($vimeo))
		{
			return $vimeo[ 0 ]->thumbnail_large;
		}

		return false;
	}

	/**
	 * @param     $dir
	 *
	 * @return bool
	 * @since 5.0
	 */
	private function makeDir($dir)
	{
		if(mkdir($dir, 0777, true) || is_dir($dir))
		{
			return true;
		}

		if(!$this->makeDir(dirname($dir)))
		{
			return false;
		}

		return mkdir($dir, 0777, true);
	}

	/**
	 * @param $url
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	private function http($url)
	{
		$header = get_headers($url, true);

		return substr($header[ 0 ], 9, 3);
	}
}