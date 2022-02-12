<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_eventtableedit
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ETE\Component\EventTableEdit\Administrator\View\Csvimport;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HtmlHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;


require_once JPATH_COMPONENT.'/src/Helper/ete.php';
require_once JPATH_COMPONENT.'/src/Helper/datatypes.php';


/**
 * View to edit a Csvimport.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$input = Factory::getApplication()->input;
        $layout = $input->get('com_eventtableedit.layout');
        // Switch the differnet datatypes
		
        switch ($layout) {
            case 'newTable':
                $this->headLine = $this->get('HeadLine');

                // Check for errors.
                if (count($errors = $this->get('Errors'))) {
                    foreach ($errors as $error) {
                        JFactory::getApplication()->enqueueMessage($error, 'error');
                    }
                    return false;
                }

                // Create the select list of datatypes
                $datatypes = new \Datatypes();
                $this->listDatatypes = $datatypes->createSelectList();              
                $this->tableName = $input->get('tableName');              
                $this->checkfun = $input->get('checkfun');              

                $this->addNewTableToolbar();
                break;
            case 'summary':
                // Check for errors.
                if (count($errors = $this->get('Errors'))) {
                    foreach ($errors as $error) {
                        Factory::getApplication()->enqueueMessage($error, 'error');
                    }
                    return false;
                }

				$this->headLine = $this->get('HeadLine');
				
                $this->addSummaryToolbar();
                break;
            default:
                // Get max upload size
                $max_upload = (int) (ini_get('upload_max_filesize'));
                $max_post = (int) (ini_get('post_max_size'));
                $memory_limit = (int) (ini_get('memory_limit'));
                $this->maxFileSize = min($max_upload, $max_post, $memory_limit);

                $this->tables = $this->createTableSelectList();
                $this->tables1 = $this->createTableSelectList1();

                $this->addDefaultToolbar();
        }
		
		$this->setLayout($layout);
		
		parent::display($tpl);
	}
	
	public function createTableSelectList()
    {
		$model = $this->getModel();
        $tables = $model->getTables();
		
        if (0 === (int)count($tables)) {
            return null;
        }

        $elem = [];
        $elem[] = \Joomla\CMS\HTML\HTMLHelper::_('select.option', '', Text::_('COM_EVENTTABLEEDIT_PLEASE_SELECT_TABLE'));

        foreach ($tables as $table) {
            $elem[] = \Joomla\CMS\HTML\HTMLHelper::_('select.option', $table->id, $table->id.' '.$table->name);
        }
        return \Joomla\CMS\HTML\HTMLHelper::_('select.genericlist', $elem, 'tableList', ' required="true"', 'value', 'text', 0);
    }

    public function createTableSelectList1()
    {
		$model = $this->getModel();
        $tables = $model->getTables();
        if (0 === (int)count($tables)) {
            return null;
        }

        $elem = [];
        $elem[] = \Joomla\CMS\HTML\HTMLHelper::_('select.option', '', Text::_('COM_EVENTTABLEEDIT_PLEASE_SELECT_TABLE'));

        foreach ($tables as $table) {
            $elem[] = \Joomla\CMS\HTML\HTMLHelper::_('select.option', $table->id, $table->id.' '.$table->name);
        }
        return \Joomla\CMS\HTML\HTMLHelper::_('select.genericlist', $elem, 'tableList1', '', 'value', 'text', 0);
    }
	
	protected function addDefaultToolbar()
    {
		Factory::getApplication()->input->set('hidemainmenu', true);
        $canDo = ContentHelper::getActions('com_eventtableedit');

        ToolBarHelper::title(Text::_('COM_EVENTTABLEEDIT_MANAGER_CSVIMPORT'), 'etetables');
        
        if ($canDo->get('core.csv')) {
            ToolBarHelper::custom('csvimport.upload', 'upload.png', '', 'COM_EVENTTABLEEDIT_UPLOAD', false);
        }
		ToolBarHelper::cancel('csvimport.cancel', 'JTOOLBAR_CLOSE');
    }

    /**
     * The Toolbar for importing a new table and selecting the datatypes.
     */
    protected function addNewTableToolbar()
    {
		Factory::getApplication()->input->set('hidemainmenu', true);
		$canDo = ContentHelper::getActions('com_eventtableedit');

        ToolBarHelper::title(Text::_('COM_EVENTTABLEEDIT_IMPORT_NEW_TABLE'), 'etetables');

        if ($canDo->get('core.csv')) {
            ToolBarHelper::custom('csvimport.newTable', 'apply.png', '', 'JTOOLBAR_APPLY', false);
        }
        ToolBarHelper::cancel('csvimport.cancel', 'JTOOLBAR_CLOSE');
    }

    /**
     * The Toolbar for showing the summary of the import.
     */
    protected function addSummaryToolbar()
    {
		Factory::getApplication()->input->set('hidemainmenu', true);
        ToolBarHelper::title(Text::_('COM_EVENTTABLEEDIT_IMPORT_SUMMARY'), 'etetables');
        ToolBarHelper::custom('csvimport.cancel', 'apply.png', '', 'COM_EVENTTABLEEDIT_OK', false);
    }
}
