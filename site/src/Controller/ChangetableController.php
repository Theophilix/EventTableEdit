<?php

namespace ETE\Component\EventTableEdit\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
//require_once JPATH_SITE.'/components/com_eventtableedit/helper/etetable.php';

class ChangetableController extends FormController {
	
	
	public function display($cachable = false, $urlparams = array()) {
		
		$document = Factory::getDocument();
        $viewName = $this->input->getCmd('view', 'login');
        $viewFormat = $document->getType();
        
        $view = $this->getView($viewName, $viewFormat);
        
        $view->document = $document;
        $view->display();
    }


	public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries and acl
        Session::checkToken() or die('Invalid Token');
		
        // Get Variables
        $main = Factory::getApplication()->input;
		$Itemid = $main->getInt('Itemid', '');
		
		
        $id = $main->getInt('id', '');
        $cid = $main->post->get('cid', [], 'array');
		
        $name = $main->post->get('name', [], 'array');
        $datatype = $main->post->get('datatype', [], 'array');
        $defaultSorting = $main->post->get('defaultSorting', [], 'array');

        $model = $this->getModel('changetable');
        $model->save($cid, $name, $datatype, $defaultSorting);
		
        $normal = $model->getnormal_table($id);
		if (0 === (int)$normal) {			
            $this->setRedirect(Route::_('index.php?option=com_eventtableedit&view=etetable&id='.$id .'&Itemid=' . $Itemid, false), Text::_('COM_EVENTTABLEEDIT_SETTINGS_SAVED'));
        } else {
            $this->setRedirect(Route::_('index.php?option=com_eventtableedit&view=appointments&id='.$id .'&Itemid=' . $Itemid, false), Text::_('COM_EVENTTABLEEDIT_SETTINGS_SAVED'));
        }
    }

    private function aclCheck()
    {
        $user = Factory::getUser();
        $main = Factory::getApplication()->input;
        $id = $main->getInt('id', '-1');
        $asset = 'com_eventtableedit.etetable.'.$id;

        if (!$user->authorise('core.create_admin', $asset)) {
            return false;
        }
        return true;
    }	
}