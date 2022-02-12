<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_eventtableedit
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ETE\Component\EventTableEdit\Administrator\View\Csvexport;

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
		$user = Factory::getUser();
		$input = Factory::getApplication()->input;
        $layout = $input->get('com_eventtableedit.layout');
		if (!$user->authorise('core.csv', 'com_eventtableedit')) {
            JFactory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            return false;
        }
		
		//echo $layout;die;
		
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

                $this->csvFile =  $postget['csvFile'];
				
				$this->addSummaryToolbar();
                break;
            default:
                
                $this->tables = $this->createTableSelectList();

                $this->addDefaultToolbar();
        }
	   
		$this->setLayout($layout);
		
		parent::display($tpl);
	}
	
	protected function addDefaultToolbar()
    {
		Factory::getApplication()->input->set('hidemainmenu', true);
        ToolBarHelper::title(Text::_('COM_EVENTTABLEEDIT_MANAGER_CSVEXPORT'), 'etetables');
        ToolBarHelper::custom('csvexport.export', 'apply.png', '', 'COM_EVENTTABLEEDIT_EXPORT', false);
		ToolBarHelper::cancel('csvimport.cancel', 'JTOOLBAR_CLOSE');
    }
	
	protected function addSummaryToolbar()
    {
		Factory::getApplication()->input->set('hidemainmenu', true);
        ToolBarHelper::title(Text::_('COM_EVENTTABLEEDIT_EXPORT_SUMMARY'), 'etetables');
        ToolBarHelper::custom('csvexport.cancel', 'apply.png', '', 'JTOOLBAR_CLOSE', false);
        ToolBarHelper::custom('csvexport.download', 'apply.png', '', 'COM_EVENTTABLEEDIT_DOWNLOAD_FILE', false);
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
}
