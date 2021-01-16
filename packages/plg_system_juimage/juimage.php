<?php
/**
 * @package        JUImage
 * @subpackage     Class
 *
 * @author         Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C)  2011-2021 by Denys D. Nosov (https://joomla-ua.org)
 * @license        GNU General Public License version 2 or later
 *
 * @since          4.0
 */

use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;

/**
 * JUImage plugin class.
 *
 * @package  JUImage plugin
 *
 * @since 4.0
 */
class plgSystemJUImage extends CMSPlugin
{
	/**
	 * @since 4.0
	 */
	public function onAfterInitialise()
	{
		require_once JPATH_LIBRARIES . '/juimage/JUImage.php';
	}
}