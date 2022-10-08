<?php
/**
 * @since          5.0
 * @subpackage     Class
 *
 * @author         Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C)  2011-2022 by Denys D. Nosov (https://joomla-ua.org)
 * @license        GNU General Public License version 2 or later
 *
 * @package        JUImage
 */

include_once __DIR__ . '/vendor/autoload.php';

use JUImage\Image;

/**
 * JUImage Class for JLoader::register
 *
 * @since  5.0
 */
class JUImage extends Image
{
	protected $path;

	protected $img_blank;

	/**
	 * JUImage constructor.
	 *
	 * @param array $config
	 *
	 * @since  5.0
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);
	}
}