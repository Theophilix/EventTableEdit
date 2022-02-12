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
use Joomla\CMS\Access\Rules;

require_once JPATH_COMPONENT.'/src/Helper/csv.php';

/**
 * csvimport model.
 *
 * @since  1.6
 */
class XmlimportModel extends AdminModel
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
	
	
	public function saveXml($data)
    {
		if(!empty($data['adminemailsubject']))
			$data['adminemailsubject'] = html_entity_decode($data['adminemailsubject']);
		else
			$data['adminemailsubject'] = '';
        if(!empty($data['useremailsubject']))
			$data['useremailsubject'] = html_entity_decode($data['useremailsubject']);
		else
			$data['useremailsubject'] = '';
		if(!empty($data['useremailtext']))
			$data['useremailtext'] = html_entity_decode($data['useremailtext']);
		else
			$data['useremailtext'] = '';
		if(!empty($data['adminemailtext']))
			$data['adminemailtext'] = html_entity_decode($data['adminemailtext']);
		else
			$data['adminemailtext'] = '';
		if(!empty($data['pretext']))
			$data['pretext'] = html_entity_decode($data['pretext']);
		else
			$data['pretext'] = '';
		if(!empty($data['aftertext']))
			$data['aftertext'] = html_entity_decode($data['aftertext']);
		else
			$data['aftertext'] = '';

	
        // Initialise variables;
        
		$table = Table::getInstance('EtetableTable','ETE\Component\EventTableEdit\Administrator\Table\\');
        
		
        $pk = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName().'.id');
        $isNew = true;
        $db = Factory::getDBO();
        // Include the content plugins for the on save events.
        PluginHelper::importPlugin('content');
		

        // Load the row if saving an existing category.
        if ($pk > 0) {
            $table->load($pk);
            $isNew = false;
        }

        // Alter the title for save as copy
        if (!$isNew && 0 === (int)$data['id']) {
            $m = null;
            $data['alias'] = '';
            if (preg_match('#\((\d+)\)$#', $table->name, $m)) {
                $data['name'] = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $table->name);
            } else {
                $data['name'] .= ' (2)';
            }
        }
		
        // Bind the data.
        if (!$table->bind($data)) {
            $this->setError($table->getError());
            return false;
        }

        $data['rules'] = json_decode($data['rules'], true);
        // Bind the rules.
        if (isset($data['rules'])) {
            $rules = new Rules($data['rules']);
            $table->setRules($rules);
        }

        // Check the data.
        /*if (!$table->check()) {
            $this->setError($table->getError());
            return false;
        }*/

        // Trigger the onContentBeforeSave event.
        $result = Factory::getApplication()->triggerEvent($this->event_before_save, [$this->option.'.'.$this->name, $table, $isNew, $data]);
		
        if (in_array(false, $result, true)) {
            $this->setError($table->getError());
            return false;
        }
		
        // Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());
            return false;
        }
		
        if (0 === (int)$data['id'] && 1 === (int)$data['temps']) {
            $this->createRowsTable($table->id);
            if ($data['col'] > 0) {
                for ($i = 1; $i <= $data['col']; ++$i) {
                    $nameofhead = 'Head'.$i;
						
                    $ins = sprintf("INSERT INTO #__eventtableedit_heads (`table_id`,`name`,`datatype`,`ordering`) VALUES (%s,'%s','text',%s)", $table->id, $nameofhead, $i);
					
                    $db->setQuery($ins);
                    $db->execute();
                    $newId = $db->insertid();
                    $this->updateRowsTable('0', $newId, $table->id);
                }
            }
            $this->Insertemptyrow($table->id, $data['row']);
        } else {
            $this->createRowsTable($table->id);
            if (count($data['headdata']['linehead']) > 0) {
                for ($i = 0; $i <= count($data['headdata']['linehead']) - 1; ++$i) {
                    $temp = $data['headdata']['linehead'][$i];
                    $nameofhead = $temp['name'];
                    if ('timestamp' === strtolower($nameofhead) || 'timestamp' === strtolower($temp['headtable'])) {
                        continue;
                    }
                    $datatype = $temp['datatype'];
                    $ins = sprintf("INSERT INTO #__eventtableedit_heads (`table_id`,`name`,`datatype`,`ordering`) VALUES (%s,'%s','%s',%s)", $table->id, $nameofhead, $datatype, $i);
					
                    $db->setQuery($ins);
                    $db->execute();
                    $newId = $db->insertid();
                    $this->updateRowsTablefromxml('0', $newId, $table->id, $datatype);
                }
            }
            $this->Insertrowfromxml($table->id, $data['rowdata']['linerow'], $data['checkfun']);
        }

        return $table->id;
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
	
	private function updateRowsTablefromxml($cid, $newId, $id, $datatype)
    {
        $db = Factory::getDBO();
        if ('boolean' === $datatype || 'link' === $datatype || 'mail' === $datatype || 'four_state' === $datatype) {
            $datatype = 'text';
        }
        $query = 'ALTER TABLE #__eventtableedit_rows_'.$id.' ';

        // If it's a existing column
        if (0 !== (int)$cid) {
            $query .= 'CHANGE head_'.$newId.' head_'.$newId.' '.$datatype;
        } else {
            $query .= 'ADD head_'.$newId.' '.$datatype;
        }

        $db->setQuery($query);
        $db->execute();
    }
	
	public function Insertrowfromxml($id, $prerow, $checkfun)
    {
        $db = Factory::getDBO();
        $select = 'SELECT id FROM #__eventtableedit_heads WHERE table_id="'.$id.'" ORDER BY id ASC';
        $db->setQuery($select);
        $filedsname = $db->loadColumn();

        $headdefine = '`ordering`,`created_by`,';

        $beginCol = 2;
        $haveTimestamp = false;
        if (isset($prerow[0]) && $prerow[0]['timestamp']) {
            $beginCol = 2;
            $haveTimestamp = true;
        }

        for ($x = 0; $x < count($filedsname); ++$x) {
            $headdefine .= '`head_'.$filedsname[$x].'`,';
        }
        $headdefine .= '`timestamp`,';
        $headdefine = rtrim($headdefine, ',');
        $aemptyda = count($filedsname);
        $currentTime = new \DateTime();

        for ($z = 0; $z < count($prerow); ++$z) {
            $reocrddata = array_values($prerow[$z]);
            $nbspstring = '';

            $final_record = count($reocrddata);
            if ($haveTimestamp) {
                $final_record = count($reocrddata) - 1;
            }
            for ($p = $beginCol; $p < $final_record; ++$p) {
                $checkstring = str_replace("'", "\'", $reocrddata[$p]);
                if (is_array($checkstring)) {
                    if (1 === (int)$checkfun) {
                        $checkstring = 'free';
                    } else {
                        $checkstring = '';
                    }
                }
                $nbspstring .= '"'.$checkstring.'",';
            }
            $nbspstring .= "'obj_timestamp_obj'";
            $nbspstring = rtrim($nbspstring, ',');

            if (true === $haveTimestamp) {
                if (isset($reocrddata[count($reocrddata) - 1]) && '' !== $reocrddata[count($reocrddata) - 1]) {
                    $reocrddata[count($reocrddata) - 1] = str_replace("'", '', $reocrddata[count($reocrddata) - 1]);
                    $date = str_replace('.', '-', $reocrddata[count($reocrddata) - 1]);
                    $timestamp = date('Y-m-d H:i:s', strtotime($date));
                } else {
                    $currentTime->modify('+1 second');
                    $timestamp = $currentTime->format('Y-m-d H:i:s');
                }
            } else {
                $currentTime->modify('+1 second');
                $timestamp = $currentTime->format('Y-m-d H:i:s');
            }
            if ('1970-01-01 00:00:00' === $timestamp) {
                $currentTime->modify('+1 second');
                $timestamp = $currentTime->format('Y-m-d H:i:s');
            }
            $valueString = str_replace('obj_timestamp_obj', $timestamp, $nbspstring);
            //$nbspstring = rtrim($nbspstring,',');

            $insert = 'INSERT INTO `#__eventtableedit_rows_'.$id."` ($headdefine) VALUES ($valueString)";

            $db->setQuery($insert);
            $db->execute();
        }
    }
}
