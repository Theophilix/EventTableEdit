<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ETE\Component\EventTableEdit\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;

/**
 * Contact component helper.
 *
 * @since  1.6
 */
class EtetableHelper extends ContentHelper
{
	/**
     * Configure the Linkbar.
     *
     * @param string $vName the name of the active view
     *
     * @return void
     *
     * @since	1.6
     */
    /* public static function addSubmenu($vName)
    {
        JSubMenuHelper::addEntry(
            Text::_('COM_EVENTTABLEEDIT_SUBMENU_ETETABLES'),
            'index.php?option=com_eventtableedit&view=etetables',
            'etetables' === $vName
        );
        JSubMenuHelper::addEntry(
            Text::_('COM_EVENTTABLEEDIT_SUBMENU_APPOINTMENTTABLES'),
            'index.php?option=com_eventtableedit&view=appointmenttables',
            'appointmenttables' === $vName
        );
        JSubMenuHelper::addEntry(
            Text::_('COM_EVENTTABLEEDIT_SUBMENU_DROPDOWN'),
            'index.php?option=com_eventtableedit&view=dropdowns',
            'dropdowns' === $vName
        );

        // Add only if user has sufficient rights
        $user = JFactory::getUser();
        if ($user->authorise('core.csv', 'com_eventtableedit')) {
            JSubMenuHelper::addEntry(
                Text::_('COM_EVENTTABLEEDIT_SUBMENU_CSVIMPORT'),
                'index.php?option=com_eventtableedit&view=csvimport',
                'csvimport' === $vName
            );
            JSubMenuHelper::addEntry(
                Text::_('COM_EVENTTABLEEDIT_SUBMENU_CSVEXPORT'),
                'index.php?option=com_eventtableedit&view=csvexport',
                'csvexport' === $vName
            );
            JSubMenuHelper::addEntry(
                Text::_('COM_EVENTTABLEEDIT_SUBMENU_XMLIMPORT'),
                'index.php?option=com_eventtableedit&view=xmlimport',
                'xmlimport' === $vName
            );
            JSubMenuHelper::addEntry(
                Text::_('COM_EVENTTABLEEDIT_SUBMENU_XMLEXPORT'),
                'index.php?option=com_eventtableedit&view=xmlexport',
                'xmlexport' === $vName
            );
        }
    } */

    /**
     * Gets a list of the actions that can be performed.
     *
     * @return JObject
     */
    public static function getActions($component = '', $section = '', $id = 0)
    {
        $user = JFactory::getUser();
        $result = new JObject();

        $assetName = 'com_eventtableedit';

        $actions = [
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete', 'core.csv',
        ];

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }
}
