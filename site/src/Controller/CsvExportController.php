<?php

namespace ETE\Component\EventTableEdit\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

class CsvExportController extends FormController {
	
	
	public function display($cachable = false, $urlparams = array()) {
		
		$document = Factory::getDocument();
        $viewName = $this->input->getCmd('view', 'login');
        $viewFormat = $document->getType();
        
        $view = $this->getView($viewName, $viewFormat);
        
        $view->document = $document;
        $view->display();
    }
	
	function export(){
		// ACL Check
        $user = Factory::getUser();
		

        // Initialize Variables
        $this->model = $this->getModel('csvexport');

        $input = Factory::getApplication()->input;
        $postget = $input->getArray();

        $this->id = $postget['tableList'];
		
		$asset = 'com_eventtableedit.etetable.'.$this->id;
        if (!$user->authorise('core.csv', $asset)) {
            Factory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(JRoute('index.php?option=com_eventtableedit'));
            return false;
        }
		
		
		
		
        $this->separator = $postget['separator'];
        $this->doubleqt = $postget['doubleqt'];
        $this->csvexporttimestamp = $postget['csvexporttimestamp'];

        //$input->set('com_eventtableedit.layout', 'summary');
        //$input->set('view', 'csvexport');

        $this->model->setVariables($this->id, $this->separator, $this->doubleqt, $this->csvexporttimestamp);
        $this->model->export();
		$this->download();
        //parent::display();
    }

    /* public function cancel()
    {
        $this->setRedirect(JRoute::_('index.php?option=com_eventtableedit'));
        return false;
    } */

    public function download()
    {
        $app = Factory::getApplication();
        $id = $app->input->get('tableList');
        $file = JPATH_ROOT.'/components/com_eventtableedit/template/tablexml/csv_'.$id.'.csv';

        header('Content-Description: File Transfer');
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.filesize($file));
        readfile($file);
        exit;
    }


}