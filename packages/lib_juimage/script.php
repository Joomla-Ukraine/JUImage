<?php
/**
 * @since          5.0
 * @subpackage     Class
 *
 * @author         Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C)  2016-2022 by Denys D. Nosov (https://joomla-ua.org)
 * @license        GNU General Public License version 2 or later
 *
 * @package        JUImage
 */

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since    5.0
 * @package  JUImage
 *
 */
class JUImageInstallerScript
{
	/**
	 * @param $type
	 * @param $parent
	 *
	 * @return void
	 *
	 * @since 5.0
	 */
	public function preflight($type, $parent)
	{

	}

	/**
	 * @param $parent
	 *
	 *
	 * @since    5.0
	 */
	public function uninstall($parent)
	{

	}

	/**
	 * @param $parent
	 *
	 *
	 * @since    5.0
	 */
	public function update($parent)
	{

	}

	/**
	 * @return bool
	 *
	 * @since    5.0
	 */
	public function postflight()
	{
		$path = JPATH_SITE . '/libraries/juimage/';

		$files = [
			$path . 'Image.php',
			$path . 'index.html'
		];

		$folders = [
			$path . 'classes',
			$path . 'vendor/rosell-dk/webp-convert/src/Converters/Binaries'
		];

		foreach($files as $file)
		{
			if(file_exists($file))
			{
				unlink($file);
			}
		}

		foreach($folders as $folder)
		{
			if(is_dir($folder))
			{
				$this->unlinkRecursive($folder, 1);
			}
		}

		return true;
	}

	/**
	 * @param $dir
	 * @param $deleteRootToo
	 *
	 * @since    5.0
	 */
	public function unlinkRecursive($dir, $deleteRootToo)
	{
		if(!$dh = opendir($dir))
		{
			return true;
		}

		while(false !== ($obj = readdir($dh)))
		{
			if($obj === '.' || $obj === '..')
			{
				continue;
			}

			if(!unlink($dir . '/' . $obj))
			{
				$this->unlinkRecursive($dir . '/' . $obj, true);
			}
		}

		closedir($dh);

		if($deleteRootToo)
		{
			rmdir($dir);
		}

		return true;
	}
}