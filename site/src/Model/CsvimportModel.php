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

class CsvImportModel extends ItemModel
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
	/**
     * Pseudo constructor for setting the variables.
     */
    public function setVariables($id, $separator, $doubleqt, $checkfun)
    {
        $this->id = $id;
        $this->separator = $separator;
        $this->doubleqt = $doubleqt;
        $this->checkfun = $checkfun;
    }
	
	/**
     * Get the headline of a csv file.
     */
    public function getHeadLine()
    {
        // Get the separator
        

        $fp = fopen(JPATH_BASE.'/components/com_eventtableedit/tmpUpload.csv', 'r');

        if (!$fp) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_IMPORT_NO_FILE_FOUND'), 'error');
        }

        $row = fgets($fp, 1021024);

        $this->csvHeadLine = $this->readCsvLine($row);
		
        fclose($fp);

        return $this->csvHeadLine;
    }

    /**
     * Convert Encoding to UTF-8.
     */
    protected function processEncoding($row)
    {
        // Detect Encoding
        $encoding = mb_detect_encoding($row, 'UTF-8, ASCII', true);

        if ('UTF-8' !== $encoding) {
            $row = iconv('ISO-8859-1', 'UTF-8//IGNORE', $row);
        }

        return $row;
    }

    /**
     * Import all the csv data and overwrite a table.
     */
    public function importCsvOverwrite()
    {
        if (!$this->checkCSV()) {
            return false;
        }

        $this->truncateTable();

        if (!$this->readCsvFile()) {
            $this->app->setUserState('com_eventtableedit.csvError', true);
            return false;
        }
    }

    /**
     * Import all the csv data and append data to a table.
     */
    public function importCsvAppend()
    {
        if (!$this->checkCSV()) {
            return false;
        }

        $startOrder = $this->getBiggestOrdering();

        if (!$this->readCsvFile($startOrder)) {
            $this->app->setUserState('com_eventtableedit.csvError', true);
            return false;
        }
    }

    /**
     * Get the biggest order number, so that the rows could be appended correctly.
     */
    protected function getBiggestOrdering()
    {
        $query = 'SELECT ordering FROM #__eventtableedit_rows_'.$this->id.
                 ' ORDER BY ordering DESC'.
                 ' LIMIT 0, 1';
        $this->db->setQuery($query);
        return $this->db->loadResult();
    }

    protected function truncateTable()
    {
        $query = 'TRUNCATE TABLE #__eventtableedit_rows_'.$this->id;
        $this->db->setQuery($query);
        $this->db->execute();
    }

    function readCsvFile($startRow = 0)
    {
        $app = Factory::getApplication();
        $checkfun = $app->input->get('checkfun');
        $fp = fopen(JPATH_BASE.'/components/com_eventtableedit/tmpUpload.csv', 'r');

        if (!$fp) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_IMPORT_NO_FILE_FOUND'), 'error');
        }

        $this->getHeads();

        $lineCount = 0;

        $currentTime = new \DateTime();
        while (!feof($fp)) {
            $row = fgets($fp, 1021024);

            // Do not read headline
            if (0 === (int)$lineCount) {
                ++$lineCount;
                continue;
            }

            if (empty($row)) {
                continue;
            }

            $data = $this->readCsvLineRow($row);

            $this->insertRowToDb($data, $startRow + $lineCount, $checkfun, $currentTime);
            ++$lineCount;
        }
        fclose($fp);

        // Delete File
        unlink(JPATH_BASE.'/components/com_eventtableedit/tmpUpload.csv');

        return true;
    }

    /**
     * Get information about the column.
     */
    protected function getHeads()
    {
        $query = 'SELECT CONCAT(\'head_\', a.id) AS head, datatype FROM #__eventtableedit_heads AS a'.
                    ' WHERE a.table_id = '.$this->id.
                    ' ORDER BY a.ordering ASC';
        $this->db->setQuery($query);

        $rows = $this->db->loadObjectList();

        // If there are no colums in the table
        if (!count($rows)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_ERROR_NO_COLUMNS'), 'error');
            return false;
        }

        $this->heads = [];
        foreach ($rows as $row) {
            $this->heads['name'][] = $row->head;
            $this->heads['datatype'][] = $row->datatype;
        }
    }

    /**
     * Read a single line of the csv file.
     */
    protected function readCsvLine($row)
    {
        $row = $this->processEncoding($row);

        // Get the single values
        $values = \Csv::getValuesFromCsv($row, $this->separator);
		
        $this->cntr = count($values);
        if (isset($values[$this->cntr - 1]) && 'timestamp' === trim($values[$this->cntr - 1])) {
            $this->cntr = $this->cntr - 1;
        }
        $values2 = [];
        for ($h = 0; $h < $this->cntr; ++$h) {
            $values2[$h] = trim($values[$h]);

            // If there were "" in it
            if (('"' === substr($values2[$h], 0, 1)) && ('"' === substr($values2[$h], -1)) && !$this->doubleqt) {
                $values2[$h] = str_replace('""', '"', $values2[$h]);
            }

            // Remove Spaces
            if ($this->doubleqt) {
                $values2[$h] = trim($values2[$h]);
            }
            //$values[$h] = htmlentities($values[$h], ENT_COMPAT, 'UTF-8');
        }

        return $values2;
    }

    protected function readCsvLineRow($row)
    {
		
        $row = $this->processEncoding($row);
		

        // Get the single values
        $values = \Csv::getValuesFromCsv($row, $this->separator);
		
        $values2 = [];
        for ($h = 0; $h < $this->cntr; ++$h) {
            $values2[$h] = trim($values[$h]);

            // If there were "" in it
            if (('"' === substr($values2[$h], 0, 1)) && ('"' === substr($values2[$h], -1)) && !$this->doubleqt) {
                $values2[$h] = str_replace('""', '"', $values2[$h]);
            }

            // Remove Spaces
            if ($this->doubleqt) {
                $values2[$h] = trim($values2[$h]);
            }
            //$values[$h] = htmlentities($values[$h], ENT_COMPAT, 'UTF-8');
        }
        // echo "<pre>";print_r($values2);die;
        return $values2;
    }

    /**
     * Writes one csv data row to the db.
     */
    protected function insertRowToDb($data, $ordering, $checkfun, $currentTime = null)
    {
        $user = Factory::getUser();

        $data = $this->prepareDataForDb($data);

        if (null === $currentTime) {
            $currentTime = new \DateTime();
        }

        $newdata = '';
        $cntr = count($this->csvHeadLine) - 1;
        if (isset($this->csvHeadLine[$cntr]) && 'timestamp' === $this->csvHeadLine[$cntr]) {
            //convert to timestamp to sql format
            if (isset($data[$cntr]) && '' !== $data[$cntr]) {
                $data[$cntr] = str_replace("'", '', $data[$cntr]);
                $date = str_replace('.', '-', $data[$cntr]);
                $timestamp = date('Y-m-d H:i:s', strtotime($date));
            } else {
                $currentTime->modify('+1 second');
                $timestamp = $currentTime->format('Y-m-d H:i:s');
            }

            if ('1970-01-01 00:00:00' === $timestamp) {
                $currentTime->modify('+1 second');
                $timestamp = $currentTime->format('Y-m-d H:i:s');
            }

            $data[$cntr] = "'".$timestamp."'";
            if (1 === (int)$checkfun) {
                // NULL replace with free //
                $newdata .= str_replace('NULL', "'free'", implode(', ', $data));
            // END NULL replace with free //
            } else {
                $newdata .= implode(', ', $data);
            }

            $query = 'INSERT INTO #__eventtableedit_rows_'.$this->id.
                ' (created_by, ordering, '.implode(', ', $this->heads['name']).', timestamp)'.
                ' VALUES ('.$user->get('id').', '.$ordering.', '.$newdata.')';
        } else {
            $currentTime->modify('+1 second');
            $timestamp = $currentTime->format('Y-m-d H:i:s');

            if (1 === (int)$checkfun) {
                // NULL replace with free //
                $newdata .= str_replace('NULL', "'free'", implode(', ', $data));
            // END NULL replace with free //
            } else {
                $newdata .= implode(', ', $data);
            }

            $query = 'INSERT INTO #__eventtableedit_rows_'.$this->id.
                ' (created_by, ordering, timestamp, '.implode(', ', $this->heads['name']).')'.
                ' VALUES ('.$user->get('id').', '.$ordering.", '".$timestamp."', ".$newdata.')';
        }

        //echo $query;die;
        $this->db->setQuery($query);
        $this->db->execute();

        $selectallrecords = 'SELECT COUNT(id) AS row FROM #__eventtableedit_rows_'.$this->id;
        $this->db->setQuery($selectallrecords);
        $rwo = $this->db->loadResult();

        $updatecol = "UPDATE `#__eventtableedit_details` SET row='".$rwo."' WHERE id='".$this->id."'";
        $this->db->setQuery($updatecol);
        $this->db->execute();
    }

    /**
     * Prepare content before saving it in the database.
     */
    protected function prepareDataForDb($data)
    {
        for ($a = 0; $a < count($data); ++$a) {
            $data[$a] = trim($data[$a]);

            // If data is empty write a NULL and if datatype is not int, float, date or time
            $d = $this->heads['datatype'][$a];
            //if ($data[$a] != '' &&  $d != 'int' && $d != 'float') {
            if ('' !== $data[$a] && 'int' !== $d) {
                if ('float' === $d) {
                    $data[$a] = "'".str_replace(',', '.', $data[$a])."'";
                } elseif ('boolean' === $d) {
                    if ('ja' === $data[$a] || '0' === $data[$a]) {
                        $data[$a] = "'0'";
                    } else {
                        $data[$a] = "'1'";
                    }
                } elseif ('date' === $d) {
                    $data[$a] = "'".date('Y-m-d', strtotime(str_replace('.', '-', $data[$a])))."'";
                } else {
                    $data[$a] = "'".$data[$a]."'";
                }
            } elseif ('' === $data[$a]) {
                $data[$a] = 'NULL';
            }
        }

        return $data;
    }

    /**
     * Checks if the table in the csv file fits to the table in the db.
     */
    protected function checkCSV()
    {
        // Check if the number of rows is the same
        $app = Factory::getApplication();

        if (!$this->getHeadLine()) {
            $app->setUserState('com_eventtableedit.csvError', true);
            return false;
        }
		
        $this->getHeads();
        $nmbInDb = count($this->heads['name']);
        $nmbInCsv = count($this->csvHeadLine);
		
        if ($nmbInDb !== $nmbInCsv) {
            $app->setUserState('com_eventtableedit.csvError', true);
            return false;
        } else {
            $app->setUserState('com_eventtableedit.csvError', false);
            return true;
        }
    }
}
