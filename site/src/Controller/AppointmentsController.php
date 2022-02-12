<?php

namespace ETE\Component\EventTableEdit\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
require_once JPATH_SITE.'/components/com_eventtableedit/helper/etetable.php';

class AppointmentsController extends FormController {
	
	
	public function display($cachable = false, $urlparams = array()) {
		
		$document = Factory::getDocument();
        $viewName = $this->input->getCmd('view', 'login');
        $viewFormat = $document->getType();
        
        $view = $this->getView($viewName, $viewFormat);
        
        $view->document = $document;
        $view->display();
    }   
}