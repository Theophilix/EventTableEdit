<?php

namespace ETE\Component\EventTableEdit\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\String\StringHelper;

/**
 * Contact Table class.
 *
 * @since  1.0
 */
class EtetableTable extends Table implements VersionableTableInterface
{
	

	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;

	
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   1.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_eventtableedit.etetable';

		parent::__construct('#__eventtableedit_details', 'id', $db);

		$this->setColumnAlias('title', 'name');
	}

	/**
	 * Stores a contact.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function store($updateNulls = true)
	{
		
		$date   = Factory::getDate()->toSql();
		$userId = Factory::getUser()->id;

		
		// Verify that the alias is unique
		$table = Table::getInstance('EtetableTable', __NAMESPACE__ . '\\', array('dbo' => $this->getDbo()));

		if ($table->load(array('alias'=>$this->alias)) && ($table->id != $this->id || 0 === $this->id)) {
			
			$this->setError(Text::_('COM_EVENTTABLEEDIT_ERROR_UNIQUE_ALIAS'));

			return false;
		}
		
		return parent::store($updateNulls);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     \JTable::check
	 * @since   1.5
	 */
	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		

		// Check for valid name
		if (trim($this->name) == '')
		{
			$this->setError(Text::_('COM_EVENTTABLEEDIT_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		// Generate a valid alias
		$this->generateAlias();

		// Sanity check for user_id
		if (!$this->user_id)
		{
			$this->user_id = 0;
		}	

		if (!$this->id)
		{
			// Hits must be zero on a new item
			$this->hits = 0;
		}


		return true;
	}

	/**
	 * Generate a valid alias from title / date.
	 * Remains public to be able to check for duplicated alias before saving
	 *
	 * @return  string
	 */
	public function generateAlias()
	{
		if (empty($this->alias))
		{
			$this->alias = $this->name;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		return $this->alias;
	}


	/**
	 * Get the type alias for the history table
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   4.0.0
	 */
	public function getTypeAlias()
	{
		return $this->typeAlias;
	}
	
	public function delete($pk = null)
	{
		parent::delete($pk);
		
		// Delete heads
        
		$db = Factory::getDbo();
        $query = 'DELETE FROM #__eventtableedit_heads'.
                 ' WHERE table_id = '.$pk;
        $db->setQuery($query);
        $db->execute();

        // Delete _rows_$pk table
		
        $query = 'DROP TABLE IF EXISTS #__eventtableedit_rows_'.$pk;
        $db->setQuery($query);
        $db->execute();
		
		return true;
	}
}
