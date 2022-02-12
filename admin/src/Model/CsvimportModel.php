<?php

namespace ETE\Component\EventTableEdit\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\CMS\Plugin\PluginHelper;

require_once JPATH_COMPONENT.'/src/Helper/csv.php';

/**
 * csvimport model.
 *
 * @since  1.6
 */
class CsvimportModel extends AdminModel
{
	
	use VersionableModelTrait;
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'com_eventtableedit_etetable';

	/**
	 * The type alias for this content type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = 'com_eventtableedit.etetable';

	/**
	 * Allowed batch commands
	 *
	 * @var  array
	 */
	protected $batch_commands = array(
		'client_id'   => 'batchClient',
		'language_id' => 'batchLanguage'
	);

	/**
	 * Batch client changes for a group of etetables.
	 *
	 * @param   string  $value     The new value matching a client.
	 * @param   array   $pks       An array of row IDs.
	 * @param   array   $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchClient($value, $pks, $contexts)
	{
		// Set the variables
		$user = Factory::getUser();

		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if (!$user->authorise('core.edit', $contexts[$pk]))
			{
				$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}

			$table->reset();
			$table->load($pk);
			$table->cid = (int) $value;

			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
	
		if (empty($record->id) || $record->published != -2)
		{
			return false;
		}
		return parent::canDelete($record);
	}

	/**
	 * A method to preprocess generating a new title in order to allow tables with alternative names
	 * for alias and title to use the batch move and copy methods
	 *
	 * @param   integer  $categoryId  The target category id
	 * @param   Table    $table       The JTable within which move or copy is taking place
	 *
	 * @return  void
	 *
	 * @since   3.8.12
	 */
	public function generateTitle($categoryId, $table)
	{
		// Alter the title & alias
		$data = $this->generateNewTitle($categoryId, $table->alias, $table->name);
		$table->name = $data['0'];
		$table->alias = $data['1'];
	}

	/**
	 * Method to test whether a record can have its state changed.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		// Default to component settings if category not known.
		return parent::canEditState($record);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form. [optional]
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not. [optional]
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		
		// Get the form.
		$form = $this->loadForm('com_eventtableedit.etetable', 'etetable', array('control' => 'jform', 'load_data' => $loadData));
		
		if (empty($form))
		{
			return false;
		}

		// Modify the form based on access controls.
		if (!$this->canEditState((object) $data))
		{
			// Disable fields for display.
			//$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('sticky', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			//$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
			$form->setFieldAttribute('sticky', 'filter', 'unset');
		}

		// Don't allow to change the created_by user if not allowed to access com_users.
		if (!Factory::getUser()->authorise('core.manage', 'com_users'))
		{
			$form->setFieldAttribute('created_by', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_eventtableedit.edit.etetable.data', array());

		if (empty($data))
		{
			
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('etetable.id') == 0)
			{
				
			}
		}

		$this->preprocessData('com_eventtableedit.etetable', $data);

		return $data;
	}

	/**
	 * Method to stick records.
	 *
	 * @param   array    $pks    The ids of the items to publish.
	 * @param   integer  $value  The value of the published state
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function stick(&$pks, $value = 1)
	{
		/** @var \Joomla\Component\etetables\Administrator\Table\etetableTable $table */
		$table = $this->getTable();
		$pks   = (array) $pks;

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->stick($pks, $value, Factory::getUser()->id))
		{
			$this->setError($table->getError());

			return false;
		}

		return true;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   Table  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since   1.6
	 */
	protected function getReorderConditions($table)
	{
		return [
			$this->_db->quoteName('state') . ' >= 0',
		];
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   Table  $table  A Table object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		$date = Factory::getDate();
		$user = Factory::getUser();

		if (empty($table->id))
		{
			// Set the values
			$table->created    = $date->toSql();
			$table->created_by = $user->id;
			
		}
		else
		{
			
			// Set the values
			$table->modified    = $date->toSql();
			$table->modified_by = $user->id;
		}

		
	}

	/**
	 * Allows preprocessing of the Form object.
	 *
	 * @param   Form    $form   The form object
	 * @param   array   $data   The data to be merged into the form object
	 * @param   string  $group  The plugin group to be executed
	 *
	 * @return  void
	 *
	 * @since    3.6.1
	 */
	protected function preprocessForm(Form $form, $data, $group = 'content')
	{

		parent::preprocessForm($form, $data, $group);
	}
	
	/**
     * Get all available tables.
     */
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
	
	
	function createTable($data){
		$id = $this->getState($this->getName() . '.id');
		$isNew = $this->getState($this->getName() . '.new');
		if($isNew){
			$this->createRowsTable($id);
			if ($data['col'] > 0) {
                $db = Factory::getDBO();
                for ($i = 1; $i <= $data['col']; ++$i) {
                    $nameofhead = 'Head'.$i;

                    $ins = sprintf("INSERT INTO #__eventtableedit_heads (`table_id`,`name`,`datatype`,`ordering`) VALUES (%s,'%s','text',%s)", $id, $nameofhead, $i);
					$db->setQuery($ins);
                    $db->execute();
                    $newId = $db->insertid();
                    $this->updateRowsTable('0', $newId, $id);
                }
            }
            $this->Insertemptyrow($id, $data['row']);
		}
	}

	private function createRowsTable($id)
    {
        $db = Factory::getDBO();
		
        $query = 'SHOW TABLE STATUS LIKE \' #__eventtableedit_rows_'.$id.'\'';
        $db->setQuery($query);

        if (count($db->loadObjectList()) > 0) {
            return false;
        }

        // A new table has to be created
        $query = 'CREATE TABLE #__eventtableedit_rows_'.$id.
                 ' (id INT NOT NULL AUTO_INCREMENT,'.
                 ' ordering INT(11) NULL default 0,'.
                 ' created_by INT(11) NULL default 0,'.

                 ' timestamp TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,'.

                 ' PRIMARY KEY (id))'.
                 ' ENGINE=MyISAM CHARACTER SET \'utf8\' COLLATE \'utf8_general_ci\'';
        $db->setQuery($query);
        $db->execute();
    }
	private function updateRowsTable($cid, $newId, $id)
    {
        $db = Factory::getDBO();
        $query = 'ALTER TABLE #__eventtableedit_rows_'.$id.' ';

        // If it's a existing column
        if (0 !== (int)$cid) {
            $query .= 'CHANGE head_'.$newId.' head_'.$newId.' text';
        } else {
            $query .= 'ADD head_'.$newId.' text';
        }

        $db->setQuery($query);
        $db->execute();
    }
	
	public function Insertemptyrow($id, $emptyrow)
    {
        $db = Factory::getDBO();
        $select = 'SELECT id FROM #__eventtableedit_heads WHERE table_id="'.$id.'"';

        $db->setQuery($select);
        $filedsname = $db->loadColumn();

        $headdefine = '';
        for ($x = 0; $x < count($filedsname); ++$x) {
            $headdefine .= '`head_'.$filedsname[$x].'`,';
        }

        $headdefine .= '`timestamp`,';

        $headdefine = rtrim($headdefine, ',');

        $aemptyda = count($filedsname);

        $nbspstring = '';
        for ($j = 0; $j < $aemptyda; ++$j) {
            $nbspstring .= "'&nbsp',";
        }

        $nbspstring .= "'obj_timestamp_obj'";
        $currentTime = new \DateTime();

        for ($z = 0; $z < $emptyrow; ++$z) {
            $nbspstring = rtrim($nbspstring, ',');
            $currentTime->modify('+1 second');
            $timestamp = $currentTime->format('Y-m-d H:i:s');
            $valueString = str_replace('obj_timestamp_obj', $timestamp, $nbspstring);

            $insert = 'INSERT INTO `#__eventtableedit_rows_'.$id."` ($headdefine) VALUES ($valueString)";

            $db->setQuery($insert);
            $db->execute();
        }
    }
	
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
		$this->app = Factory::getApplication();
        // Get the separator
        $this->separator = $this->app->getUserState('com_eventtableedit.separator', ';');
        $this->doubleqt = $this->app->getUserState('com_eventtableedit.doubleqt', 1);

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
		$this->db = Factory::getDBO();
        $query = 'SELECT ordering FROM #__eventtableedit_rows_'.$this->id.
                 ' ORDER BY ordering DESC'.
                 ' LIMIT 0, 1';
        $this->db->setQuery($query);
        return $this->db->loadResult();
    }

    protected function truncateTable()
    {
		$this->db = Factory::getDBO();
        $query = 'TRUNCATE TABLE #__eventtableedit_rows_'.$this->id;
        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function readCsvFile($startRow = 0)
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
        //unlink(JPATH_BASE.'/components/com_eventtableedit/tmpUpload.csv');

        return true;
    }

    /**
     * Get information about the column.
     */
    protected function getHeads()
    {
		
		$this->db = Factory::getDBO();
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
		$this->db = Factory::getDBO();
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
