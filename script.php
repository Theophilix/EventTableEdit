<?php
/**
 * $Id:$.
 *
 * @copyright (C) 2007 - 2020 Manuel Kaspar and Theophilix
 * @license GNU/GPL, see LICENSE.php in the installation package
 * This file is part of Event Table Edit
 *
 * Event Table Edit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * Event Table Edit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with Event Table Edit. If not, see <http://www.gnu.org/licenses/>.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class com_eventtableeditInstallerScript
{
    public function install($parent)
    {
        echo '<p>'.Text::_('COM_EVENTTABLEEDIT_POSTFLIGHT_INSTALL_TEXT').'</p>';
        $parent->getParent()->setRedirectURL('index.php?option=com_eventtableedit');
    }

    public function uninstall($parent)
    {
        // Uninstall the _rows tables
        $db = Factory::getDBO();
        $query = 'SELECT id FROM #__eventtableedit_details';
        $db->setQuery($query);
        $rows = $db->loadColumn();

        for ($a = 0; $a < count($rows); ++$a) {
            $query = 'DROP TABLE IF EXISTS #__eventtableedit_rows_'.$rows[$a];
            $db->setQuery($query);
            $db->execute();
        }

        $extensions = [
                ['type' => 'plugin', 'name' => 'loadete'],
            ];

        foreach ($extensions as $key => $extension) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select($db->quoteName(['extension_id']));
            $query->from($db->quoteName('#__extensions'));
            $query->where($db->quoteName('type').' = '.$db->quote($extension['type']));
            $query->where($db->quoteName('element').' = '.$db->quote($extension['name']));
            $db->setQuery($query);
            $id = $db->loadResult();

            if (isset($id) && $id) {
                $installer = new JInstaller();
                $result = $installer->uninstall($extension['type'], $id);
            }
        }

        echo '<p>'.Text::_('COM_EVENTTABLEEDIT_UNINSTALL_TEXT').'</p>';
    }

    public function update($parent)
    {
        $db = Factory::getDBO();
        $query = 'SELECT id FROM #__eventtableedit_details';
        $db->setQuery($query);
        $rows = $db->loadColumn();

        if (!empty($rows)) {
            for ($a = 0; $a < count($rows); ++$a) {
                $query = 'SELECT * FROM #__eventtableedit_rows_'.$rows[$a];

                $db->setQuery($query);
                $data = $db->loadObject();

                if (!isset($data->timestamp)) {
                    $query = 'ALTER TABLE `#__eventtableedit_rows_'.$rows[$a].'` ADD `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_by`, COMMENT=""';
                    $db->setQuery($query);
                    $db->execute();
                }
            }
        }

        $query = "SHOW COLUMNS FROM `#__eventtableedit_details` LIKE 'rowdelete'";
        $db->setQuery($query);
        $data = $db->loadObject();
        if (empty($data)) {
            $query = 'ALTER TABLE `#__eventtableedit_details` ADD `rowdelete` tinyint(4) NOT NULL AFTER `rowsort`, COMMENT=""';
            $db->setQuery($query);
            $db->execute();
        }

        $app = Factory::getApplication();
        $prefix = $app->getCfg('dbprefix');

        $query = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$prefix."eventtableedit_details' AND COLUMN_NAME = 'scroll_table' ";
        $db->setQuery($query);
        $data = $db->loadObject();
        if (empty($data)) {
            $query = 'ALTER TABLE `#__eventtableedit_details`
				ADD `scroll_table` varchar(255) COLLATE "utf8_general_ci" NOT NULL,
				COMMENT=""';
            $db->setQuery($query);
            $db->execute();
        }

        $query = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$prefix."eventtableedit_details' AND COLUMN_NAME = 'scroll_table_height'";
        $db->setQuery($query);
        $data = $db->loadObject();
        if (empty($data)) {
            $query = 'ALTER TABLE `#__eventtableedit_details`
				ADD `scroll_table_height` varchar(255) COLLATE "utf8_general_ci" NOT NULL AFTER `scroll_table`,
				COMMENT=""';
            $db->setQuery($query);
            $db->execute();
        }
        $query = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$prefix."eventtableedit_details' AND COLUMN_NAME = 'add_option_list'";
        $db->setQuery($query);
        $data = $db->loadObject();
        if (empty($data)) {
            $query = 'ALTER TABLE `#__eventtableedit_details`
				ADD `add_option_list` tinyint(1) NOT NULL,
				COMMENT=""';
            $db->setQuery($query);
            $db->execute();
        }
        $query = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$prefix."eventtableedit_details' AND COLUMN_NAME = 'corresptable'";
        $db->setQuery($query);
        $data = $db->loadObject();
        if (empty($data)) {
            $query = 'ALTER TABLE `#__eventtableedit_details`
				ADD `corresptable` text COLLATE "utf8_general_ci" NOT NULL AFTER `add_option_list`,
				COMMENT=""';
            $db->setQuery($query);
            $db->execute();
        }
        $query = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$prefix."eventtableedit_details' AND COLUMN_NAME = 'standardlayout'";
        $db->setQuery($query);
        $data = $db->loadObject();
        if (empty($data)) {
            $query = 'ALTER TABLE `#__eventtableedit_details` ADD `standardlayout` varchar(255) NOT NULL;';
            $db->setQuery($query);
            $db->execute();
        }
        echo '<p>'.Text::_('COM_EVENTTABLEEDIT_UPDATE_TEXT').'</p>';
    }

    /**
     * method to run before an install/update/uninstall method.
     */
    public function preflight($type, $parent)
    {
        // $type is the type of change (install, update or discover_install)
        echo '<p>'.Text::_('COM_EVENTTABLEEDIT_PREFLIGHT_'.$type.'_TEXT').'</p>';
    }

    /**
     * method to run after an install/update/uninstall method.
     */
    public function postflight($type, $parent)
    {
        $extensions = [
                ['type' => 'plugin', 'name' => 'loadete', 'group' => 'content'],
            ];

        foreach ($extensions as $key => $extension) {
            $ext = $parent->getParent()->getPath('source').'/'.$extension['type'].'s/'.$extension['group'].'/'.$extension['name'];
            $installer = new JInstaller();
            $installer->install($ext);

            if ('plugin' === $extension['type']) {
                $db = Factory::getDbo();
                $query = $db->getQuery(true);

                $fields = [$db->quoteName('enabled').' = 1'];
                $conditions = [
                        $db->quoteName('type').' = '.$db->quote($extension['type']),
                        $db->quoteName('element').' = '.$db->quote($extension['name']),
                        $db->quoteName('folder').' = '.$db->quote($extension['group']),
                        ];

                $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $db->execute();
            }
        }
        // $type is the type of change (install, update or discover_install)
        echo '<p>'.Text::_('COM_EVENTTABLEEDIT_POSTFLIGHT_'.$type.'_TEXT').'</p>';
    }
}
