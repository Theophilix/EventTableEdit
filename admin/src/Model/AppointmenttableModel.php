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

/**
 * Appointmenttable model.
 *
 * @since  1.6
 */
class AppointmenttableModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'com_eventtableedit_appointmenttable';

	/**
	 * The type alias for this content type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = 'com_eventtableedit.appointmenttable';

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
	 * Batch client changes for a group of appointmenttable.
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
		$form = $this->loadForm('com_eventtableedit.appointmenttable', 'appointmenttable', array('control' => 'jform', 'load_data' => $loadData));
		
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
		$data = $app->getUserState('com_eventtableedit.edit.appointmenttable.data', array());

		if (empty($data))
		{
			
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('appointmenttable.id') == 0)
			{
				$filters     = (array) $app->getUserState('com_eventtableedit.appointmenttables.filter');
			}
		}

		$this->preprocessData('com_eventtableedit.appointmenttable', $data);

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

		// Increment the content version number.
		//$table->version++;
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
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$input = Factory::getApplication()->input;


		// Alter the name for save as copy
		if ($input->get('task') == 'save2copy')
		{
			/** @var \Joomla\Component\etetables\Administrator\Table\etetableTable $origTable */
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['name'] == $origTable->name)
			{
				list($name, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['name']);
				$data['name']       = $name;
				$data['alias']      = $alias;
			}
			else
			{
				if ($data['alias'] == $origTable->alias)
				{
					$data['alias'] = '';
				}
			}

			$data['state'] = 0;
		}
		$date = Factory::getDate();
		$user = Factory::getUser();
		
		//$data['checked_out'] = $user->id;
		//$data['checked_out_time'] = $date->toSql();
		
		//return parent::save($data);
		
		$table      = $this->getTable();
		$context    = $this->option . '.' . $this->name;
		$app        = Factory::getApplication();

		if (\array_key_exists('tags', $data) && \is_array($data['tags']))
		{
			$table->newTags = $data['tags'];
		}

		$key = $table->getKeyName();
		
		$pk = (isset($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;
		
		$jinput = Factory::getApplication()->input;
        $global_options = $jinput->get('global_options', '', 'STRING');
        $corresponding_table = $jinput->get('corresponding_table');
        if (!empty($global_options)) {
            $corresptable = [];
            foreach ($global_options as $keya => $global_option) {
                $corresptable[$global_option] = $corresponding_table[$keya];
            }
            $data['corresptable'] = json_encode($corresptable);
        } else {
            $data['corresptable'] = '';
        }
		

		// Include the plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}

			// Bind the data.
			if (!$table->bind($data))
			{
				$this->setError($table->getError());

				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Trigger the before save event.
			$result = $app->triggerEvent($this->event_before_save, array($context, $table, $isNew, $data));

			if (\in_array(false, $result, true))
			{
				$this->setError($table->getError());

				return false;
			}

			// Store the data.
			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}

			// Clean the cache.
			$this->cleanCache();

			// Trigger the after save event.
			$app->triggerEvent($this->event_after_save, array($context, $table, $isNew, $data));
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
		
		if (isset($table->$key))
		{
			$this->setState($this->getName() . '.id', $table->$key);
		}

		$this->setState($this->getName() . '.new', $isNew);
				
		$this->createTable($data);
		
		return true;
	}

	function createTable($data)
	{
		$id = $this->getState($this->getName() . '.id');
		$isNew = $this->getState($this->getName() . '.new');
		if($isNew){
			$this->createRowsTable($id);
			if ($data['col'] > 0) {
                $db = Factory::getDBO();
                for ($i = 0; $i <= $data['col']; ++$i) {
                    if (0 === (int)$i) {
                        $nameofhead = 'Time';
                    } else {
                        $temp = date('d.m.Y');
                        $next_day = $i - 1;
                        if (0 === (int)$next_day) {
                            $nameofhead = date('d.m.Y');
                        } else {
                            $nameofhead = date('d.m.Y', strtotime($temp." +$next_day day"));
                        }
                    }

                    $ins = "INSERT INTO #__eventtableedit_heads (`table_id`,`name`,`datatype`,`ordering`) VALUES (".$id.",'".$nameofhead."','text',".$i.')';
					
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
        // Need to use getPrefix because of a Joomla Bug
        // within quotes #__ is not replaced
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
        $select = 'SELECT id FROM #__eventtableedit_heads WHERE table_id="'.$id.'" order by id';

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
        /* $time = 8; */
        for ($j = 1; $j <= $aemptyda; ++$j) {
            if (1 === (int)$j) {
                $nbspstring .= "'obj_time_obj',";
            } else {
                $nbspstring .= "'free',";
            }
        }

        $nbspstring .= "'obj_timestamp_obj'";
        $currentTime = new \DateTime();
        $time = 8;
        for ($z = 0; $z < $emptyrow; ++$z) {
            $nbspstring = rtrim($nbspstring, ',');
            $currentTime->modify('+1 second');
            $timestamp = $currentTime->format('Y-m-d H:i:s');
            $valueString = str_replace('obj_timestamp_obj', $timestamp, $nbspstring);

            if (1 === (int)strlen($time)) {
                $time_obj = "0$time:00";
            } else {
                $time_obj = "$time:00";
            }
            ++$time;
            $valueString = str_replace('obj_time_obj', $time_obj, $valueString);

            $insert = 'INSERT INTO `#__eventtableedit_rows_'.$id."` ($headdefine) VALUES ($valueString)";

            $db->setQuery($insert);
            $db->execute();
        }
    }
	
	
	public function getAppTables()
    {
        $db = Factory::getDBO();
        $db->setQuery('SELECT * FROM #__eventtableedit_details WHERE normalorappointment = 1 AND published = 1');
        $tables = $db->loadObjectList();
        return $tables;
    }
}
