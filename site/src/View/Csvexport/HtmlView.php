<?php

namespace ETE\Component\EventTableEdit\Site\View\Csvexport;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;

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
		
		$input = Factory::getApplication()->input;
		$id = $input->get('id');
		
		if (!$id) {
            Factory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
            return false;
        }
		
        $user = Factory::getUser();
        $app = Factory::getApplication();

        if (!$user->authorise('core.csv', 'com_eventtableedit')) {
            Factory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
            return false;
        }
        $input = Factory::getApplication()->input;
        $layout = $input->get('com_eventtableedit.layout');
        // Switch the differnet datatypes

        switch ($layout) {
            case 'summary':
                // Check for errors.
                if (count($errors = $this->get('Errors'))) {
                    foreach ($errors as $error) {
                        Factory::getApplication()->enqueueMessage($error, 'error');
                    }
                    return false;
                }
                $postget = $input->getArray();

                $this->csvFile = $postget['csvFile'];

                break;
            default:                
				$this->id = $id;

               
        }

        $this->document->addStyleSheet($this->baseurl.'/components/com_eventtableedit/template/css/eventtablecss.css');
		$wa = $this->document->getWebAssetManager();
		$wa->useScript('core');
		$wa->useScript('jquery');

        $this->setLayout($layout);
		

		parent::display($template);
    }
	
}