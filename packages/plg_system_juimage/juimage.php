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

use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;

/**
 * JUImage plugin class.
 *
 * @since    5.0
 * @package  JUImage plugin
 *
 */
class plgSystemJUImage extends CMSPlugin
{
	/**
	 * @since 5.0
	 */
	public function onAfterInitialise()
	{
		JLoader::registerPrefix('JUImage', JPATH_LIBRARIES . '/juimage/JUImage.php');
	}
}