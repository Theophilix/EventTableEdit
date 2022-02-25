<?php

namespace ETE\Component\EventTableEdit\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\IpHelper;
require_once JPATH_SITE.'/components/com_eventtableedit/helper/etetable.php';

class AppointmentsModel extends ItemModel
{
	
	protected $_context = 'com_eventtableedit.appointmenttable';
	
	public function __construct()
    {
        parent::__construct();

        // Load the parameters
        $app = Factory::getApplication('site');
        $params = $app->getParams();

        $this->setState('params', $params);
        $this->params = $params;
        $main = $app->input;
        $this->id = $main->getInt('id', '');
        $this->option_id = $main->getInt('id', '');
        $session = Factory::getSession();
        $corresponding_table = $session->get('corresponding_table');
        if ($corresponding_table) {
            $this->option_id = $corresponding_table;
        } else {
            $this->option_id = '';
        }

        $this->setState('is_module', 0);

        $this->db = $this->getDbo();
    }

	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id');
		
		$this->setState('appointments.id', $pk);

		$offset = $app->input->getUint('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		
		$this->setState('params', $params);

		$user = Factory::getUser();

		// If $pk is set then authorise on complete asset, else on component only
		$asset = empty($pk) ? 'com_eventtableedit' : 'com_eventtableedit.appointmenttable.' . $pk;
		
	}
	
	
	public function getItem($pk = null)
	{
		
		if (!isset($this->_item))
		{
			
			$cache = Factory::getCache('com_eventtableedit', 'callback');

			$pk = (int) ($pk ?: $this->getState('appointments.id'));
			
			$db = $this->getDbo();

			$loader = function ($pk) use ($db)
			{
				$query = $db->getQuery(true);

				$query->select('*, CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug')
					->from($db->quoteName('#__eventtableedit_details', 'a'))
					->where($db->quoteName('a.id') . ' = :id')
					->bind(':id', $pk, ParameterType::INTEGER);
				
				$db->setQuery($query);

				$data = $db->loadObject();
				
				
				
		
		
				if($this->getState('params'))
					$data->params = clone $this->getState('params');
				else
					$data->params = new Registry();
				
				$registry = new Registry();
				$registry->loadString($data->metadata);
				$data->metadata = $registry;
				
				$this->getACL($data);
				
				return $data;
				
			};

			try
			{
				$this->_item = $cache->get($loader, array($pk), md5(__METHOD__ . $pk));
			}
			catch (CacheExceptionInterface $e)
			{
				$this->_item = $loader($pk);
			}
		}
		

		return $this->_item;
	}
	
	private function getACL(&$data)
    {
        $user = Factory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        $asset = 'com_eventtableedit.appointmenttable.'.$data->id;

        $data->params->set('access-view', in_array($data->access, $groups));

        $data->params->set('access-edit', false);
        $data->params->set('access-add', false);
        $data->params->set('access-delete', false);
        $data->params->set('access-reorder', false);
        $data->params->set('access-create_admin', false);
        $data->params->set('access-ownRows', false);
		$data->params->set('access-csv', false);

        if ($user->authorise('core.edit', $asset)) {
            $data->params->set('access-edit', true);
        }
        if ($user->authorise('core.add', $asset)) {
            $data->params->set('access-add', true);
        }
        if ($user->authorise('core.delete', $asset)) {
            $data->params->set('access-delete', true);
        }
        if ($user->authorise('core.reorder', $asset)) {
            $data->params->set('access-reorder', true);
        }
        if ($user->authorise('core.create_admin', $asset)) {
            $data->params->set('access-create_admin', true);
        }
		
		if ($user->authorise('core.csv', $asset)) {
            $data->params->set('access-csv', true);
        }

        // See if edit_own_rows is set to yes and if a user is logged in
        if ($data->edit_own_rows && 0 !== (int)$user->get('id')) {
            $data->params->set('access-ownRows', true);
        }
    }
	
	public function getOptionID()
    {
        return $this->option_id;
    }
	
	public function getHeads()
    {
		
        if (!isset($this->heads)) {
            try {
                $query = $this->db->getQuery(true);

                $query->select($this->getState('item.select', 'a.*, CONCAT(\'head_\', a.id) AS head'));
                $query->from('#__eventtableedit_heads AS a');
                $query->where('a.table_id = '.(($this->option_id) ? $this->option_id : $this->id));
                $query->order('a.ordering asc');

                $this->db->setQuery($query);
                $this->heads = $this->db->loadObjectList();
				
                if (empty($this->heads)) {
                    return null;
                }

                return $this->heads;
            } catch (JException $e) {
                $this->setError($e);
                return false;
            }
        }
    }
	
	public function getRows()
    {
        try {
            $data = [];

            $query = $this->getRowsQuery();
			
            $data['rows'] = $this->_getList($query, $this->getState('list.start'), $this->getState('list.limit'));
			
            if (empty($data['rows'])) {
                $data['rows'] = null;
                $data['additional']['createdRows'] = null;
                $data['additional']['ordering'] = null;

                return $data;
            }

            $data['additional'] = $this->prepareData($data['rows']);
			
            $data['rows'] = $this->parseRows($data['rows']);
			

            return $data;
        } catch (JException $e) {
            $this->setError($e);
            return false;
        }
    }
	
	private function prepareData($rows)
    {
        $user = Factory::getUser();
        $ret = [];
        foreach ($rows as $row) {
            $ret['ordering'][] = $row->ordering;

            // See if the user created the row
            $uid = $user->get('id');

            if ($uid === $row->created_by) {
                $ret['createdRows'][] = $row->id;
            } else {
                $ret['createdRows'][] = null;
            }
        }
        $ret['ordering'] = implode('|', $ret['ordering']);

        if (0 === (int)count($ret['createdRows'])) {
            $ret['createdRows'] = '';
        } else {
            $ret['createdRows'] = implode('|', $ret['createdRows']);
        }

        return $ret;
    }
	
	private function parseRows($rows)
    {
        $rowCount = 0;
        $ret = [];

        foreach ($rows as $row) {
            // Iterate over the columns
            $colCount = 0;
            $ret[$rowCount]['id'] = $row->id;

            foreach ($this->heads as $head) {
                //Get the column name
                $colName = 'head_'.$head->id;

                //Get the content of a cell
                $ret[$rowCount][$colCount] = trim($row->$colName);
                $ret[$rowCount][$colCount] = $this->parseCell($ret[$rowCount][$colCount], $colCount);

                //Insert a space character that the table doesn't collapse
                if ('' === $ret[$rowCount][$colCount]) {
                    $ret[$rowCount][$colCount] = '&nbsp;';
                }

                ++$colCount;
            }

            ++$rowCount;
        }
		

        return $ret;
    }
	
	private function parseCell($cell, $colCount)
    {
        $this->getItem();
		
        $this->getHeads();
		
        $dt = $this->heads[$colCount]->datatype;

        // Translating mySQL Date
        if ('date' === $dt) {
            $cell = \eteHelper::date_mysql_to_german($cell, $this->_item->dateformat);
        }
        // Translate Time
        elseif ('time' === $dt) {
            $cell = \eteHelper::format_time($cell, $this->_item->timeformat);
        }
        //Handle Booleans
        elseif ('boolean' === $dt) {
            $cell = \eteHelper::parseBoolean($cell);
        }
        // Handle Links
        elseif ('link' === $dt) {
            $cell = \eteHelper::parseLink($cell, $this->_item->link_target, $this->_item->cellbreak);
        }
        // Handle Mails
        elseif ('mail' === $dt) {
            $cell = \eteHelper::parseMail($cell, $this->_item->cellbreak);
        }
        // Handle Floats
        elseif ('float' === $dt) {
            $cell = \eteHelper::parseFloat($cell, $this->_item->float_separator);
        }
        // Text and BBCODE Parsing
        else {
            // Don't show images in the module
            if ($this->getState('is_module', 0)) {
                $this->_item->bbcode_img = 0;
            }

            $cell = \eteHelper::parseText($cell, $this->_item->bbcode, $this->_item->bbcode_img,
                                         $this->_item->link_target, $this->_item->cellbreak);
        }
		
		

        // Highlighting search strings
        // Not used, because it destroys bb and html codes

        $cell = htmlspecialchars_decode($cell, ENT_NOQUOTES);

        return $cell;
    }
	
	protected function getRowsQuery()
    {
		
        $tid = (($this->option_id) ? $this->option_id : $this->id);

        $query = $this->db->getQuery(true);
        $query->select($this->getState('item.select', 'a.*'));
        $query->from('#__eventtableedit_rows_'.$tid.' AS a');

        return $query;
    }
	
}
