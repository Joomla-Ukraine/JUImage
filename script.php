<?php
/**
 * @since          5.0
 * @subpackage     Class
 *
 * @author         Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C)  2011-2023 by Denys D. Nosov (https://joomla-ua.org)
 * @license        GNU General Public License version 2 or later
 *
 * @package        JUImage
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

defined('_JEXEC') or die;

class pkg_juimageInstallerScript
{
	/**
	 * @return bool
	 * @since    5.0
	 */
	public function postflight()
	{
		$path = JPATH_SITE . '/libraries/juimage/';

		$files = [
			$path . 'src/classes/phpthumb/phpThumb.php',
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
			return;
		}

		while(($obj = readdir($dh)) !== false)
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
	}
}