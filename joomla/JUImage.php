<?php
/**
 * @package        JUImage
 * @subpackage     Class
 *
 * @author         Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C)  2011-2019 by Denys D. Nosov (https://joomla-ua.org)
 * @license        GNU General Public License version 2 or later
 *
 * @since          3.0
 */

include_once __DIR__ . '/vendor/autoload.php';

use JUImage\Image;

/**
 * JUImage Class for JLoader::register
 *
 * @since  3.0
 */
class JUImage extends Image
{
	protected $path;

	protected $img_blank;

	/**
	 * JUImage constructor.
	 *
	 * @param        $path
	 * @param string $img_blank
	 *
	 * @since  3.0
	 */
	public function __construct($path = JPATH_BASE, $img_blank = 'libraries/juimage/')
	{
		parent::__construct($path, $img_blank);
	}
}