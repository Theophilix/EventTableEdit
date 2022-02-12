<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_eventtableedit
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ETE\Component\EventTableEdit\Administrator\View\Xmlexport;

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

                $this->xmlexporttimestamp = $input->get('xmlexporttimestamp');
                $this->orderxml = $this->getXML();
				$this->id		=	$input->get('tableList');
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
		$canDo = ContentHelper::getActions('com_eventtableedit');
		
		
		ToolBarHelper::title(Text::_('COM_EVENTTABLEEDIT_MANAGER_XMLIMPORT'), 'etetables');
		
        if ($canDo->get('core.csv')) {
            ToolBarHelper::custom('xmlexport.export', 'apply.png', '', 'COM_EVENTTABLEEDIT_EXPORT', false);
        }
		
        ToolBarHelper::cancel('xmlexport.cancel', 'JTOOLBAR_CLOSE');
    }
	
	/**
     * The Toolbar for showing the summary of the export.
     */
    protected function addSummaryToolbar()
    {
		Factory::getApplication()->input->set('hidemainmenu', true);
		$canDo = ContentHelper::getActions('com_eventtableedit');
		ToolBarHelper::title(Text::_('COM_EVENTTABLEEDIT_EXPORT_SUMMARY'), 'etetables');
		if ($canDo->get('core.csv')) {
			ToolBarHelper::custom('xmlexport.download', 'apply.png', '', 'COM_EVENTTABLEEDIT_DOWNLOAD_FILE', false);
		}
		ToolBarHelper::cancel('xmlexport.cancel', 'JTOOLBAR_CLOSE');
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
	
	public function getXML()
    {
        
		$xml = simplexml_load_file(JPATH_COMPONENT_ADMINISTRATOR.'/eventtableedit.xml');
		
        $version = (string) $xml->version;

        $this->model = $this->getModel('xmlexport');
        $app = Factory::getApplication();
        $input = Factory::getApplication()->input;
        $postget = $input->getArray();
        $this->xmlexporttimestamp = $postget['xmlexporttimestamp'];
        $this->id = $postget['tableList'];
        if (empty($this->id)) {
            $msg = JTEXT::_('COM_EVENTTABLEEDIT_PLEASE_SELECT_TABLE');
            $app->redirect('index.php?option=com_eventtableedit&view=xmlexport', $msg);
        }
        $table = $this->model->getTabledata($this->id);

        $db = Factory::GetDBO();
        $query = 'SELECT CONCAT(\'head_\', a.id) AS head, a.name,a.datatype, a.defaultSorting FROM #__eventtableedit_heads AS a'.
                    ' WHERE a.table_id = '.$this->id.
                    ' ORDER BY a.ordering ASC';
        $db->setQuery($query);
        $heads = $db->loadObjectList();

        $query = 'SELECT * FROM #__eventtableedit_rows_'.$this->id;
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $orderxml = '<?xml version="1.0" encoding="utf-8"?> 
		<Event_Table_Edit_XML_file>
		<ETE_version>'.$version.'</ETE_version>
		<id>'.$table->id.'</id>
		<name>'.$table->name.'</name>
		<alias>'.$table->alias.'</alias>
		<user_id>'.$table->user_id.'</user_id>
		<access>'.$table->access.'</access>
		<checked_out>'.$table->checked_out.'</checked_out>
		<checked_out_time>'.$table->checked_out_time.'</checked_out_time>

		<language>'.$table->language.'</language>
		<show_filter>'.$table->show_filter.'</show_filter>
		<show_first_row>'.$table->show_first_row.'</show_first_row>
		<show_print_view>'.$table->show_print_view.'</show_print_view>
		<rowsort>'.$table->rowsort.'</rowsort>
		<show_pagination>'.$table->show_pagination.'</show_pagination>
		<bbcode>'.$table->bbcode.'</bbcode>
		<bbcode_img>'.$table->bbcode_img.'</bbcode_img>
		<pretext>'.str_replace('&', '&amp;', htmlentities($table->pretext)).'</pretext>
		<aftertext>'.str_replace('&', '&amp;', htmlentities($table->aftertext)).'</aftertext>
		<metakey>'.$table->metakey.'</metakey>
		<metadesc>'.$table->metadesc.'</metadesc>
		<metadata>'.$table->metadata.'</metadata>
		<edit_own_rows>'.$table->edit_own_rows.'</edit_own_rows>
		<dateformat>'.$table->dateformat.'</dateformat>
		<timeformat>'.$table->timeformat.'</timeformat>
		<cellspacing>'.$table->cellspacing.'</cellspacing>
		<cellpadding>'.$table->cellpadding.'</cellpadding>
		<tablecolor1>'.$table->tablecolor1.'</tablecolor1>
		<tablecolor2>'.$table->tablecolor2.'</tablecolor2>
		<float_separator>'.$table->float_separator.'</float_separator>
		<link_target>'.$table->link_target.'</link_target>
		<cellbreak>'.$table->cellbreak.'</cellbreak>
		<pagebreak>'.$table->pagebreak.'</pagebreak>
		<asset_id>'.$table->asset_id.'</asset_id>
		<lft>'.$table->lft.'</lft>
		<rgt>'.$table->rgt.'</rgt>
		<published>'.$table->published.'</published>
		<normalorappointment>'.$table->normalorappointment.'</normalorappointment>
		<addtitle>'.$table->addtitle.'</addtitle>
		<location>'.$table->location.'</location>
		<summary>'.$table->summary.'</summary>
		<email>'.$table->email.'</email>
		<adminemailsubject>'.str_replace('&', '&amp;', htmlentities($table->adminemailsubject)).'</adminemailsubject>
		<useremailsubject>'.str_replace('&', '&amp;', htmlentities($table->useremailsubject)).'</useremailsubject>
		<useremailtext>'.str_replace('&', '&amp;', htmlentities($table->useremailtext)).'</useremailtext>
		<adminemailtext>'.str_replace('&', '&amp;', htmlentities($table->adminemailtext)).'</adminemailtext>
		<displayname>'.$table->displayname.'</displayname>
		<icsfilename>'.$table->icsfilename.'</icsfilename>
		<sorting>'.$table->sorting.'</sorting>
		<switcher>'.$table->switcher.'</switcher>
		<standardlayout>'.$table->standardlayout.'</standardlayout>
		<row>'.$table->row.'</row>
		<col>'.$table->col.'</col>
		<hours>'.$table->hours.'</hours>
		<showdayname>'.$table->showdayname.'</showdayname>
		<showusernametoadmin>'.$table->showusernametoadmin.'</showusernametoadmin>
		<showusernametouser>'.$table->showusernametouser.'</showusernametouser>
		<rules>'.$table->rules.'</rules>';

        $orderxml .= '<headdata>';
        $a = 1;
        foreach ($heads as $value) {
            $orderxml .= '<linehead>
							<no>'.$a.'</no>
							<headtable>'.$value->head.'</headtable>
							<name>'.$value->name.'</name>
							<datatype>'.$value->datatype.'</datatype>
						</linehead>';
            ++$a;
        }
        if ($this->xmlexporttimestamp) {
            $orderxml .= '<linehead>
							<no>'.$a.'</no>
							<headtable>timestamp</headtable>
							<name>timestamp</name>
							<datatype>timestamp</datatype>
						</linehead>';
        }
        $orderxml .= '</headdata>';

        $orderxml .= '<rowdata>';
        $b = 1;

        foreach ($rows as $row) {
            $orderxml .= '<linerow>
							<no>'.$b.'</no>
							<id>'.$row->id.'</id>
							<ordering>'.$row->ordering.'</ordering>
							<created_by>'.$row->created_by.'</created_by>';
            for ($h = 0; $h < count($heads); ++$h) {
                $findrowval = $heads[$h]->head;
                $orderxml .= '<'.$findrowval.'>'.htmlspecialchars($row->$findrowval).'</'.$findrowval.'>';
            }
            if ($this->xmlexporttimestamp) {
                $orderxml .= '<timestamp>'.htmlspecialchars($row->timestamp).'</timestamp>';
            }
            $orderxml .= '</linerow>';
            ++$b;
        }

        $orderxml .= '</rowdata>';
        $orderxml .= '</Event_Table_Edit_XML_file>';
        return $orderxml;
    }
}
