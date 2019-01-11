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

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @package  JUImage
 *
 * @since    3.0
 */
class JUImageInstallerScript
{
	/**
	 * @param $type
	 * @param $parent
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function preflight($type, $parent)
	{

	}

	/**
	 * @param $parent
	 *
	 *
	 * @since    3.0
	 */
	public function uninstall($parent)
	{

	}

	/**
	 * @param $parent
	 *
	 *
	 * @since    3.0
	 */
	public function update($parent)
	{

	}

	/**
	 * @return bool
	 *
	 * @since    3.0
	 */
	public function postflight()
	{
		$path = JPATH_SITE . '/libraries/juimage/';

		$files = [
			$path . 'Image.php',
			$path . 'index.html'
		];

		$folders = [
			$path . 'classes'
		];

		foreach( $files AS $file )
		{
			if( file_exists($file) )
			{
				unlink($file);
			}
		}

		foreach( $folders AS $folder )
		{
			if( is_dir($folder) )
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
	 * @since    3.0
	 */
	public function unlinkRecursive($dir, $deleteRootToo)
	{
		if( !$dh = @opendir($dir) )
		{
			return;
		}

		while( false !== ($obj = readdir($dh)) )
		{
			if( $obj === '.' || $obj === '..' )
			{
				continue;
			}

			if( !@unlink($dir . '/' . $obj) )
			{
				$this->unlinkRecursive($dir . '/' . $obj, true);
			}
		}

		closedir($dh);

		if( $deleteRootToo )
		{
			@rmdir($dir);
		}
	}
}