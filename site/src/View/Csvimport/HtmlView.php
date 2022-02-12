<?php

namespace ETE\Component\EventTableEdit\Site\View\Csvimport;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

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
		$this->id = $input->get('id');
		
		$jinput = Factory::getApplication()->input;
		$checkfun = $jinput->get('checkfun', 0);
		


		if (!$this->id) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            return false;
        }
		
		$user = Factory::getUser();
        $app = Factory::getApplication();
		
		
        if (!$user->authorise('core.csv', 'com_eventtableedit')) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            return false;
        }
		
        $layout = $input->get('com_eventtableedit.layout');
        // Switch the differnet datatypes
		
        switch ($layout) {
            case 'newTable':
				$postget = $input->getArray();
				
				$model = $this->getModel();
				$model->setVariables($this->id, $postget['separator'], $postget['doubleqt'], $postget['checkfun']);
				
                $headLine = $this->get('HeadLine');
				
                // Check for errors.
                if (count($errors = $this->get('Errors'))) {
                    foreach ($errors as $error) {
                        Factory::getApplication()->enqueueMessage($error, 'error');
                    }
                    return false;
                }

                // Create the select list of datatypes
                $datatypes = new \Datatypes();
                $listDatatypes = $datatypes->createSelectList();

                $this->headLine = $headLine;
                $this->listDatatypes = $listDatatypes;
                $this->separator = $postget['separator'];
                $this->doubleqt = $postget['doubleqt'];
				
                //$this->addNewTableToolbar();
                break;
            case 'summary':
                // Check for errors.
                if (count($errors = $this->get('Errors'))) {
                    foreach ($errors as $error) {
                        Factory::getApplication()->enqueueMessage($error, 'error');
                    }
                    return false;
                }

                $this->headLine = $headLine;
               
                break;
            default:
                // Get max upload size
                $max_upload = (int) (ini_get('upload_max_filesize'));
                $max_post = (int) (ini_get('post_max_size'));
                $memory_limit = (int) (ini_get('memory_limit'));
                $upload_mb = min($max_upload, $max_post, $memory_limit);

                $tableList = self::createTableSelectList();
				

                $this->tables = $tableList;
                $this->maxFileSize = $upload_mb;
        }

        
		
		$this->checkfun = $checkfun;

        $this->document->addStyleSheet($this->baseurl.'/components/com_eventtableedit/template/css/eventtablecss.css');
		$wa = $this->document->getWebAssetManager();
		$wa->useScript('core');
		$wa->useScript('jquery');

        $this->setLayout($layout);
		

		parent::display($template);
    }
	
	
	/**
     * Generates a select list, where all tables are listed
     * This function is also used in the export module.
     */
    public function createTableSelectList()
    {
        $tables = self::getTables();

        if (0 === (int)count($tables)) {
            return null;
        }

        $elem = [];
        //$elem[] = JHTML::_('select.option', '', Text::_('COM_EVENTTABLEEDIT_PLEASE_SELECT_TABLE'));

        foreach ($tables as $table) {
			if($this->id == $table->id)
				$elem[] = HTMLHelper::_('select.option', $table->id, $table->id.' '.$table->name);
        }
        return HTMLHelper::_('select.genericlist', $elem, 'tableList', ' required="true"', 'value', 'text', 0);
    }

    protected function addDefaultToolbar()
    {
        $canDo = eteHelper::getActions();

        //JToolBarHelper::title(Text::_('COM_EVENTTABLEEDIT_MANAGER_CSVIMPORT'), 'import');
        $xml = JFactory::getXML(JPATH_COMPONENT_ADMINISTRATOR.'/eventtableedit.xml');
        $currentversion = (string) $xml->version;
        JToolBarHelper::title(Text::_('Event Table Edit '.$currentversion).' - '.Text::_('COM_EVENTTABLEEDIT_MANAGER_CSVIMPORT'), 'etetables');
        // For uploading, check the create permission.
        if ($canDo->get('core.csv')) {
            JToolBarHelper::custom('csvimport.upload', 'upload.png', '', 'COM_EVENTTABLEEDIT_UPLOAD', true);
        }
    }

    /**
     * The Toolbar for importing a new table and selecting the datatypes.
     */
    protected function addNewTableToolbar()
    {
        $canDo = eteHelper::getActions();

        //JToolBarHelper::title(Text::_('COM_EVENTTABLEEDIT_IMPORT_NEW_TABLE'), 'import');
        $xml = JFactory::getXML(JPATH_COMPONENT_ADMINISTRATOR.'/eventtableedit.xml');
        $currentversion = (string) $xml->version;
        JToolBarHelper::title(Text::_('Event Table Edit '.$currentversion).' - '.Text::_('COM_EVENTTABLEEDIT_IMPORT_NEW_TABLE'), 'etetables');

        // For uploading, check the create permission.
        if ($canDo->get('core.csv')) {
            JToolBarHelper::custom('csvimport.newTable', 'apply.png', '', 'JTOOLBAR_APPLY', false);
        }
        JToolBarHelper::cancel('csvimport.cancel', 'JTOOLBAR_CLOSE');
    }
	
	public static function getTables()
    {
        $db = Factory::getDBO();
        $query = $db->getQuery(true);
        $query->select('a.id, a.name');
        $query->from('#__eventtableedit_details AS a');
        $query->where('a.published = 1');
        $query->order('a.name', 'ASC');

        $db->setQuery($query);

        return $db->loadObjectList();
    }
	
}