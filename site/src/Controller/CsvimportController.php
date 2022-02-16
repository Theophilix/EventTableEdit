<?php

namespace ETE\Component\EventTableEdit\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
require_once JPATH_COMPONENT.'/helper/datatypes.php';
class CsvImportController extends FormController {
	
	
	public function display($cachable = false, $urlparams = array()) {
		
		$document = Factory::getDocument();
        $viewName = $this->input->getCmd('view', 'login');
        $viewFormat = $document->getType();
        $this->db = Factory::getDBO();
        $view = $this->getView($viewName, $viewFormat);
        
        $view->document = $document;
        $view->display();
    }
	
	public function upload()
    {
        // ACL Check
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

        $this->returnURL = base64_decode($postget['returnURL']);
        $this->returnURLsame = $postget['returnURLsame'];
        $this->separator = $postget['separator'];
        $this->doubleqt = $postget['doubleqt'];
        $this->importaction = $postget['importaction'];
        $this->checkfun = $postget['checkfun'] ? $postget['checkfun'] : 0;

        $input->set('view', 'csvimport');

		$this->id = $input->get('tableList');

        $this->checkForErrors();
        $this->moveFile();
        $this->model->setVariables($this->id, $this->separator, $this->doubleqt, $this->checkfun);
		
        if ($this->checkfun) {
			
			
            $return = $this->switchUploadTypes();		
			
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
                case 'overwriteTableWithHeader':
					
					$this->newTable();
                    $msg = Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_NEW');
                    break;
            }

            if ($this->checkfun) {               
				Factory::getApplication()->enqueueMessage($msg, 'success');
				$this->setRedirect($this->returnURL);
				$this->redirect();
				die;
            } else {
                Factory::getApplication()->enqueueMessage($msg, 'success');
				$this->setRedirect($this->returnURL);
				$this->redirect();
				die;
            }
        } else {
            
			
			$input->set('tableName', $input->get('table_name'));
            $this->switchUploadTypes();
            parent::display();
			
			
        }
    }

    /**
     * Task that is called when saving a new table.
     */
    public function newTable()
    {
		
		$this->db = Factory::getDBO();
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
		$this->returnURL = base64_decode($postget['returnURL']);
		
		$this->id = $postget['id'];
		
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

        
        $detailsModel = $this->getModel('etetable');
		
        if (!$this->importCsvNew($detailsModel, $name, $datatype, $this->id)) {
            $this->setRedirect(Route::_('index.php?option=com_eventtableedit&view=csvimport&Itemid='. $postget['Itemid']));
        }
		
		
		
		Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_NEW'), 'success');
        $this->setRedirect($this->returnURL);
        $this->redirect();
		die;
    }

    
    /**
     * Checks the uploaded file.
     */
    private function checkForErrors()
    {
        //$redirectUrl = 'index.php?option=com_eventtableedit&view=csvimport';
        $redirectUrl = $this->returnURL;

        // No file specified
        if (null == $this->file['name']) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_NO_FILE_SPECIFIED'), 'warning');			
			
			$this->setRedirect($this->returnURLsame);
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
        if ('overwriteTableWithHeader' === $this->importaction || 'overwriteTableWithoutHeader' === $this->importaction || 'appendTable' === $this->importaction) {
            if (null === $this->id || '' === $this->id) {
                Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_NO_TABLE'), 'warning');
                $this->setRedirect($redirectUrl);
                $this->redirect();
            }
        }
    }

    /**
     * Move the uploaded file to the server.
     */
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
            case 'overwriteTableWithoutHeader':
				$this->model->importCsvOverwrite();
				Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_NEW'), 'success');
				$this->setRedirect($this->returnURL);
				$this->redirect();
				die;                     
            case 'appendTable':
                $this->model->importCsvAppend();
				Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_NEW'), 'success');
				$this->setRedirect($this->returnURL);
				$this->redirect();
				die;               
            case 'overwriteTableWithHeader':
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
	
	public function importCsvNew($detailsModel, $name, $datatype, $id)
    {
		$this->id = $id;
        $input = Factory::getApplication()->input;
        $checkfun = $input->get('checkfun');
		
		$postget = $input->getArray();
		
        $separator = $postget['separator'];
        $doubleqt = $postget['doubleqt'];
		
		
        $this->detailsModel = $detailsModel;
        $this->checkfun = $checkfun;
        $this->separator = $separator;
        $this->doubleqt = $doubleqt;
        //$this->tableName = $name;
        $this->heads['datatype'] = $datatype;
		$this->model = $this->getModel('csvimport');
		
		
		
		$this->model->setVariables($this->id, $this->separator, $this->doubleqt, $this->checkfun);
		
		$this->csvHeadLine = $this->model->getHeadLine();
	
		
		$query = 'DROP TABLE #__eventtableedit_rows_'.$this->id;
        $this->db->setQuery($query);
		$this->db->execute();
		$query = 'DELETE FROM #__eventtableedit_heads where table_id = "'.$this->id . '"';
        $this->db->setQuery($query);
		$this->db->execute();
		
		
		
		
        $this->createRowsTable();
        $this->createHeadsTable();

        $this->model->readCsvFile();
        $this->app->setUserState('com_eventtableedit.csvError', false);
		
		$query = 'UPDATE `#__eventtableedit_details` SET `automate_sort_column` = "" WHERE `id` = "' . $this->id . '"';
        $this->db->setQuery($query);
		$this->db->execute();
		
		return true;
		
    }    

    private function createHeadsTable()
    {
        $this->csvHeadLine = $this->model->getHeadLine();
        $db = Factory::getDBO();
		
        $col_count = count($this->csvHeadLine);
		
        $cntr = $col_count - 1;
        if (isset($this->csvHeadLine[$cntr]) && 'timestamp' === $this->csvHeadLine[$cntr]) {
            $col_count = $col_count - 1;
        }
        $updatecol = "UPDATE `#__eventtableedit_details` SET col='".$col_count."' WHERE id='".$this->id."'";
        $db->setQuery($updatecol);
        $db->execute();
        $len = count($this->csvHeadLine);
        if (isset($this->csvHeadLine[$cntr]) && 'timestamp' === $this->csvHeadLine[$cntr]) {
            $len = $len - 1;
        }

        for ($a = 0; $a < $len; ++$a) {
            $table = Table::getInstance('HeadsTable','ETE\Component\EventTableEdit\Administrator\Table\\');

            $data = [];
            $data['table_id'] = $this->id;
            $data['name'] = $this->csvHeadLine[$a];
            $data['datatype'] = $this->heads['datatype'][$a];
            $data['ordering'] = $a;

            if (!$table->bind($data)) {
                echo $table->getError();
            }
            if (!$table->store()) {
                echo $table->getError();
            }

            $newId = $table->id;

            // Adjust the db rows table to save the entries
            $this->updateRowsTable($newId, $this->heads['datatype'][$a]);
        }
    }

    /**
     * A new table has to be created, insert into rows table.
     */
    private function createRowsTable()
    {
        // A new table has to be created
        $query = 'CREATE TABLE #__eventtableedit_rows_'.$this->id.
                 ' (id INT NOT NULL AUTO_INCREMENT,'.
                 ' ordering INT(11) NOT NULL default 0,'.
                 ' created_by INT(11) NOT NULL default 0,'.
                 ' timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,'.
                 ' PRIMARY KEY (id)
				 )'.
                 ' ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;';
        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Alters the _rows_$id table that it fits to the table heads.
     */
    private function updateRowsTable($newId, $datatype)
    {
        $query = 'ALTER TABLE #__eventtableedit_rows_'.$this->id;
        $query .= ' ADD head_'.$newId.' '.\Datatypes::mapDatatypes($datatype);

        $this->db->setQuery($query);
        $this->db->execute();
    }
	
}