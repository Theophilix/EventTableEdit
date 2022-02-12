<?php

namespace ETE\Component\EventTableEdit\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

class DisplayController extends BaseController {
    
    public function display($cachable = false, $urlparams = array()) {
		
		$document = Factory::getDocument();
        $viewName = $this->input->getCmd('view', 'login');
        $viewFormat = $document->getType();
		
        $view = $this->getView($viewName, $viewFormat);
		
        $view->setModel($this->getModel($viewName), true);
		
        $view->document = $document;
        $view->display();
    }
    
}