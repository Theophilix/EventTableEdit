<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_eventtableedit
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ETE\Component\EventTableEdit\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\Utilities\ArrayHelper;

/**
 * Controller for a single contact
 *
 * @since  1.6
 */
class XmlimportController extends FormController
{
	use VersionableControllerTrait;

	
	/**
     * Task that is called when uploading a csv file.
     */
    public function upload()
    {
        // ACL Check

        $app = Factory::getApplication();

        $input = Factory::getApplication()->input;
        $postget = $input->getArray();

       /*  $xml = Factory::getXML(JPATH_COMPONENT_ADMINISTRATOR.'/eventtableedit.xml');
        $currentversion = (string) $xml->version; */

        // Initialize Variables
        $this->model = $this->getModel();
		
        $this->file = $input->files->get('fupload');
        $this->checkfun = @$postget['checkfun'];
        $info = pathinfo(basename($this->file['name']));
        $ext = strtolower($info['extension']);

        if (!$this->file['name']) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_UPLOAD_XMLFILE_VALID'), 'error');
            $app->redirect('index.php?option=com_eventtableedit&view=xmlimport');
        }
        if ('xml' !== $ext) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_UPLOAD_XMLFILE_VALID'), 'error');
            $app->redirect('index.php?option=com_eventtableedit&view=xmlimport');
        }

        $xml = simplexml_load_file($this->file['tmp_name']);
        if (empty($xml)) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_FILE_IS_NOT_CORRECT'), 'error');
            $app->redirect('index.php?option=com_eventtableedit&view=xmlimport');
        } elseif ('Event_Table_Edit_XML_file' !== $xml->getName()) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_FILE_IS_NOT_CORRECT'), 'error');
            $app->redirect('index.php?option=com_eventtableedit&view=xmlimport');
        }
        $xml = json_encode($xml);
        $xml = json_decode($xml, true);

        $xml['id'] = 0;
        if (count($xml['rowdata']['linerow']) > 0) {
            $xml['temps'] = 0;
        } else {
            $xml['temps'] = 1;
        }
        $xml['alias'] = substr(md5(rand()), 0, 7);
        $xml['checkfun'] = $this->checkfun ? $this->checkfun : '0';
		
		
        //$model = $this->getModel('Etetable', 'EventtableeditModel');
		
        $tablesave = $this->model->saveXml($xml);
		
        //exit;
        if ($tablesave > 0) {
            $url = 'index.php?option=com_eventtableedit&view=etetables';
            if ($xml['normalorappointment']) {
                $url = 'index.php?option=com_eventtableedit&view=appointmenttables';
            }
			Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_SUCCESSFULLY_TABLES_AND_DATA_CREATED'), 'message');
            $app->redirect($url);
        }

        parent::display();
    }
}
