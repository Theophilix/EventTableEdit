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

require_once JPATH_COMPONENT.'/helper/csv.php';

class CsvExportModel extends ItemModel
{
	
	protected $_context = 'com_eventtableedit.csvexport';
	
	
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

        $this->db = $this->getDbo();
    }
	
	
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id');
		
		$this->setState('csvexport.id', $pk);

		$offset = $app->input->getUint('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$user = Factory::getUser();

		$asset = empty($pk) ? 'com_eventtableedit' : 'com_eventtableedit.csvexport.' . $pk;

		
	}
	
	
	
	public function getItem($pk = null)
	{
		
		if (!isset($this->_item))
		{
			
			$cache = Factory::getCache('com_eventtableedit', 'callback');

			$pk = (int) ($pk ?: $this->getState('csvexport.id'));

		
			$db = $this->getDbo();

			$loader = function ($pk) use ($db)
			{
				$query = $db->getQuery(true);

				$query->select('*')
					->from($db->quoteName('#__eventtableedit_heads', 'a'))
					->where($db->quoteName('a.table_id') . ' = :id')
					->bind(':id', $pk, ParameterType::INTEGER);
				$query->order('a.ordering', 'asc');
				$db->setQuery($query);

				$data = $db->loadObjectList();								
				
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
	
	public function setVariables($id, $separator, $doubleqt, $csvexporttimestamp = 0)
    {
        $this->id = $id;
        $this->separator = $separator;
        $this->doubleqt = $doubleqt;
        $this->csvexporttimestamp = $csvexporttimestamp;
    }
	public function export()
    {
		
		$this->db = Factory::getDbo();
		
        $this->getHeads();

        $this->getRows();
        $arraysss = [];
        $csvData = $this->csvData;
		
        for ($a = 0; $a < count($csvData); ++$a) {
            if (0 === (int)$a) { //echo '<pre>';print_r($csvData[$a]);exit;
                $loop = $csvData[$a];
                for ($b = 0; $b < count($loop); ++$b) {
                    $explode = explode('|~|', $loop[$b]);
                    if ('date' === $explode[1]) {
                        $arraysss[] = $b;
                        $csvData[$a][$b] = $explode[0];
                    } else {
                        $csvData[$a][$b] = $explode[0];
                    }
                }
            }
        }
        for ($c = 1; $c < count($csvData); ++$c) {
            $loop1 = $csvData[$c];
            for ($d = 0; $d < count($loop1); ++$d) {
                for ($e = 0; $e < count($arraysss); ++$e) {
                    if ($d === $arraysss[$e]) {
                        if ('0000-00-00' === $loop1[$d] || 'NULL' === $loop1[$d] || '' === $loop1[$d]) {// echo 'IF'.$loop1[$d];
                            $str = '00-00-0000';
                        } else { //echo 'ELSE'.$loop1[$d];
                            $str = date('d-m-Y', strtotime($loop1[$d]));
                        }

                        $csvData[$c][$d] = $str;
                        $str = '';
                    }
                }
            }
        }
        $this->csvData = $csvData;
		
        $data = \Csv::generateCsv($this->csvData, $this->separator, $this->doubleqt);
		
		
		
		//$id = $app->input->get('tableList');
		$file = 'csv_'.$this->id.'.csv';

		$this->csvFile = str_replace('csvcsv', '<br />', $data);
		
		
		
		$pf = fopen(JPATH_ROOT.'/components/com_eventtableedit/template/tablexml/'.$file, 'w');
		if (!$pf) {
			echo "Cannot create $file!".NL;
			return;
		}
		fwrite($pf, $this->csvFile);
		fclose($pf);
        $input = Factory::getApplication()->input;
        $input->set('csvFile', $data);
    }

    /**
     * Get information about the column.
     */
    protected function getHeads()
    {
        $query = 'SELECT CONCAT(\'head_\', a.id) AS head, a.name,a.datatype, a.defaultSorting FROM #__eventtableedit_heads AS a'.
                    ' WHERE a.table_id = '.$this->id.
                    ' ORDER BY a.ordering ASC';
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList();

        // If there are no colums in the table
        if (!count($rows)) {
            Factory::getApplication()->enqueueMessage(JText::_('COM_EVENTTABLEEDIT_ERROR_NO_COLUMNS'), 'error');
        }

        $this->heads = [];
        $defSort = [];
		$this->csvData = [];
        foreach ($rows as $row) {
            //if($row->datatype == 'date'){
            $this->csvData[0][] = $row->name.'|~|'.$row->datatype;
            //}
            $this->heads['name'][] = $row->head;

            // Prepare Default Sorting
            if ('' !== $row->defaultSorting && ':' !== $row->defaultSorting) {
                $split = explode(':', $row->defaultSorting);
				if(isset($split[0]) && isset($split[1])){
					$defSort[((int) ($split[0]) - 1)] = $row->head.' '.$split[1];
				}else{
					$defSort =[];
				}
            }
        }

        if ($this->csvexporttimestamp) {
            $this->csvData[0][] = 'timestamp|~|timestamp';
            $this->heads['name'][] = 'timestamp';
        }

        if (!count($defSort)) {
            $this->heads['defaultSorting'] = 'ordering ASC';
        } else {
            $this->heads['defaultSorting'] = implode(', ', $defSort);
        }
    }

    protected function getRows()
    {
        $query = 'SELECT * FROM #__eventtableedit_rows_'.$this->id.
                 ' ORDER BY '.$this->heads['defaultSorting'];
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList();

        if (!count($rows)) {
            return false;
        }

        $a = 1;
        foreach ($rows as $row) {
            for ($b = 0; $b < count($this->heads['name']); ++$b) {
                $field = $this->heads['name'][$b];
                //$breaks = array("<br />","<br>","<br/>","<br /> ","<br> ","<br/> ");
                //$row->$field = str_ireplace($breaks, "\n", $row->$field);
                $this->csvData[$a][$b] = $row->$field;
            }

            ++$a;
        }
    }
}
