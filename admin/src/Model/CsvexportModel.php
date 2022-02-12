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
class CsvexportModel extends AdminModel
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
	
	
	public function setVariables($id, $separator, $doubleqt, $csvexporttimestamp = 0)
    {
        $this->id = $id;
        $this->separator = $separator;
        $this->doubleqt = $doubleqt;
        $this->csvexporttimestamp = $csvexporttimestamp;
    }

    public function export()
    {
		$this->db = Factory::getDBO();
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
            Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_ERROR_NO_COLUMNS'), 'error');
        }

        $this->heads = [];
        $defSort = [];

        foreach ($rows as $row) {
			
            //if($row->datatype == 'date'){
            $this->csvData[0][] = $row->name.'|~|'.$row->datatype;
            //}
            $this->heads['name'][] = $row->head;

            // Prepare Default Sorting
            if ('' !== $row->defaultSorting && ':' !== $row->defaultSorting) {
				$split = array();
				if($row->defaultSorting)
					$split = explode(':', $row->defaultSorting);
				
				if(!empty($split)){
					$defSort[((int) ($split[0]) - 1)] = $row->head.' '.$split[1];
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
