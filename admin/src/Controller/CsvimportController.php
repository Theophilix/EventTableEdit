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
class CsvimportController extends FormController
{
	use VersionableControllerTrait;

	
	public function upload()
    {
        // ACL Check
		
		$this->app = Factory::getApplication();
		
        $user = Factory::getUser();
        if (!$user->authorise('core.csv', 'com_eventtableedit')) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(Route('index.php?option=com_eventtableedit'));
            return false;
        }
        $input = Factory::getApplication()->input;
        $postget = $input->getArray();
		
		

        // Initialize Variables
        $this->model = $this->getModel('csvimport');
        $this->file = $input->files->get('fupload');

        $this->separator = $postget['separator'];
        $this->doubleqt = $postget['doubleqt'];
        $this->importaction = $postget['importaction'];
        $this->checkfun = $postget['checkfun'] ? $postget['checkfun'] : 0;

        $input->set('view', 'csvimport');

        if ('overwriteTable' === $this->importaction) {
            $this->id = $input->get('tableList');
        } elseif ('appendTable' === $this->importaction) {
            $this->id = $input->get('tableList1');
        } else {
            $this->id = 0;
        }

        $this->checkForErrors();
        $this->moveFile();
        $this->model->setVariables($this->id, $this->separator, $this->doubleqt, $this->checkfun);
		
        if ($this->checkfun) {
            $return = $this->switchUploadTypes();
            if ('newTable' === $return) {
                $this->newTable();
            }

            switch ($this->importaction) {
                case 'overwriteTable':
                    if (!$this->app->getUserState('com_eventtableedit.csvError', true)) :
                        $msg = Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_OVERWRITE');
                    else:
                        $msg = Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_OVERWRITE_FAILED');
                    endif;
                    break;
                case 'appendTable':
                    if (!$this->app->getUserState('com_eventtableedit.csvError', true)) :
                        $msg = Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_APPEND');
                    else:
                        $msg = Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_APPEND_FAILED');
                    endif;
                    break;
                case 'newTable':
                    $msg = Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_NEW');
                    break;
            }
			
            if ($this->checkfun) {
				if($msg){
					Factory::getApplication()->enqueueMessage($msg);
				}
                $this->app->redirect('index.php?option=com_eventtableedit&view=appointmenttables');
            } else {
				if($msg){
					Factory::getApplication()->enqueueMessage($msg);
				}
                $this->app->redirect('index.php?option=com_eventtableedit&view=etetables');
            }
        } else {
            $input->set('tableName', $input->get('table_name'));
            $this->switchUploadTypes();
            parent::display();
        }

        //parent::display();
    }
	
	private function checkForErrors()
    {
        $redirectUrl = 'index.php?option=com_eventtableedit&view=csvimport';

        // No file specified
        if (null === $this->file['name']) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_NO_FILE_SPECIFIED'), 'warning');
            $this->setRedirect($redirectUrl);
            $this->redirect();
        }
        // Check file type
        $ending = substr($this->file['name'], -3);
        $ending = strtolower($ending);
        if ('txt' !== $ending && 'csv' !== $ending) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_WRONG_FILE_TYPE'), 'warning');
            $this->setRedirect($redirectUrl);
            $this->redirect();
        }
        // If a table is choosen
        if ('overwriteTable' === $this->importaction || 'appendTable' === $this->importaction) {
            if (null === $this->id || '' === $this->id) {
                Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_NO_TABLE'), 'warning');
                $this->setRedirect($redirectUrl);
                $this->redirect();
            }
        }
    }
	
	private function moveFile()
    {
        $res = move_uploaded_file($this->file['tmp_name'], JPATH_BASE.'/components/com_eventtableedit/tmpUpload.csv');

        if (!$res) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_ERROR_MOVING_FILE'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_eventtableedit&view=csvimport'));
        }
    }
	
	private function switchUploadTypes()
    {
        // Save vars to session
        $this->storeVarsInSession();
        $input = Factory::getApplication()->input;
        switch ($this->importaction) {
            case 'overwriteTable':
                $this->model->importCsvOverwrite();
                $input->set('com_eventtableedit.layout', 'summary');
                return 'summary';
                break;
            case 'appendTable':
                $this->model->importCsvAppend();
                $input->set('com_eventtableedit.layout', 'summary');
                return 'summary';
                break;
            case 'newTable':

                $input->set('com_eventtableedit.layout', 'newTable');
                return 'newTable';
                break;
        }
    }
	
	private function storeVarsInSession()
    {
        $this->app->setUserState('com_eventtableedit.id', $this->id);
        $this->app->setUserState('com_eventtableedit.importAction', $this->importaction);
        $this->app->setUserState('com_eventtableedit.separator', $this->separator);
        $this->app->setUserState('com_eventtableedit.doubleqt', $this->doubleqt);
        $this->app->setUserState('com_eventtableedit.checkfun', $this->checkfun);
    }
	
	/**
     * Task that is called when saving a new table.
     */
    public function newTable()
    {
        // ACL Check
        $user = Factory::getUser();
        $input = Factory::getApplication()->input;
        $checkfun = $input->get('checkfun');
        $input->set('checkfun', $checkfun);
        if (!$user->authorise('core.csv', 'com_eventtableedit')) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_eventtableedit'));
            return false;
        }
        $postget = $input->getArray();

        // Get Variables

        if ($checkfun) {
            $name = $input->get('table_name');
            $datatype = [];
            $datatype[] = 'text';
            $datatype[] = 'text';
            $datatype[] = 'text';
            $datatype[] = 'text';
            $datatype[] = 'text';
            $datatype[] = 'text';
            $datatype[] = 'text';
            $datatype[] = 'text';
            $datatype[] = 'text';
        } else {
            $name = $postget['tableName'];
            $datatype = $postget['datatypesList'];
        }

        $this->model = $this->getModel('csvimportnewtable');
		$detailsModel = $this->getModel('etetable');
		
        if (!$this->model->importCsvNew($detailsModel, $name, $datatype)) {
            $this->setRedirect(Route::_('index.php?option=com_eventtableedit&view=csvimport'));
        }

        return;
    }
	
}
