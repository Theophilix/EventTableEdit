<?php

namespace ETE\Component\EventTableEdit\Site\View\Changetable;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
/**
 * @package     Joomla.Site
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2020 John Smith. All rights reserved.
 * @license     GNU General Public License version 3; see LICENSE
 */
require_once JPATH_COMPONENT.'/helper/datatypes.php';
/**
 * View for the user identity validation form
 */
class HtmlView extends BaseHtmlView {
    

    /**
     * Display the view
     *
     * @param   string  $template  The name of the layout file to parse.
     * @return  void
     */
    public function display($template = null) {
		
		$app = Factory::getApplication();
		
        $this->item = $this->get('Item');
        $this->tableInfo = $this->get('Info');
		

        if (null === $this->tableInfo) {
            Factory::getApplication()->enqueueMessage(JText::_('COM_EVENTTABLEEDIT_ERROR_ETETABLE_NOT_FOUND'), 'warning');
            return false;
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            foreach ($errors as $error) {
                Factory::getApplication()->enqueueMessage($error, 'warning');
            }
            return false;
        }

        // Get the parameters of the active menu item
        $this->params = $app->getParams();
        $main = $app->input;
        $id = $main->getInt('id');

        $additional = [];

        // Get Datatypes
        $datatypes = new \Datatypes();
        $additional['datatypes'] = $datatypes->getDatatypes();
        $additional['datatypes_desc'] = $datatypes->getDatatypesDesc();
		
        
        $this->info = $this->tableInfo;
        
        $this->additional = $additional;
        $this->id = $id;

        $this->_prepareDocument();

		parent::display($template);
    }
	
	protected function _prepareDocument()
    {
		
		$wa = $this->document->getWebAssetManager();
		$wa->useScript('core');
		$wa->useScript('jquery');
        
        // Add Scripts and Stylesheets
        require_once JPATH_COMPONENT.'/helper/changetable.js.php';
        $this->document->addStyleSheet($this->baseurl.'/components/com_eventtableedit/template/css/eventtablecss.css');
        $this->document->addCustomTag($this->getBrowserStyles());
    }

    /**
     * Especially for IE that the calendar is on the right position.
     */
    private function getBrowserStyles()
    {
        $ie = '<!--[if lte IE 7]>'."\n";
        $ie .= '<link rel="stylesheet" href="'.$this->baseurl.'/components/com_eventtableedit/template/css/ie7.css" />'."\n";
        $ie .= '<![endif]-->'."\n";

        return $ie;
    }
}