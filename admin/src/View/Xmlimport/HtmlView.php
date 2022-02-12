<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_eventtableedit
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ETE\Component\EventTableEdit\Administrator\View\Xmlimport;

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
 * View to edit a Xmlimport.
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
        $app = Factory::getApplication();

        if (!$user->authorise('core.csv', 'com_eventtableedit')) {
            JFactory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            return false;
        }
        $input = Factory::getApplication()->input;
        $layout = $input->get('com_eventtableedit.layout');
        $this->addDefaultToolbar();

        $this->setLayout($layout);
		
		parent::display($tpl);
	}
	
    protected function addDefaultToolbar()
    {
		Factory::getApplication()->input->set('hidemainmenu', true);
		$canDo = ContentHelper::getActions('com_eventtableedit');
		
		
		ToolBarHelper::title(Text::_('COM_EVENTTABLEEDIT_MANAGER_XMLIMPORT'), 'etetables');
		
        if ($canDo->get('core.csv')) {
            ToolBarHelper::custom('xmlimport.upload', 'upload.png', '', 'COM_EVENTTABLEEDIT_UPLOAD', false);
        }
		
        ToolBarHelper::cancel('xmlimport.cancel', 'JTOOLBAR_CLOSE');
    }
}
