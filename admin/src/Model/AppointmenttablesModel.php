<?php

namespace ETE\Component\EventTableEdit\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of contact records.
 *
 * @since  1.6
 */
class AppointmenttablesModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name',
				'col', 'a.col',
				'row', 'a.row',
				'language', 'a.language',
				'published', 'a.published',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'access_level'
			);

		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'a.name', $direction = 'asc')
	{
		$app = Factory::getApplication();

		$forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		// Adjust the context to support forced languages.
		if ($forcedLanguage)
		{
			$this->context .= '.' . $forcedLanguage;
		}

		// List state information.
		parent::populateState($ordering, $direction);

		// Force a language.
		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . $this->getState('filter.level');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  \JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = Factory::getUser();
		$input = Factory::getApplication()->input;
		// Select the required fields from the table.
		$query->select(
            $this->getState(
                'list.select',
                'a.id, a.*'
            )
        );
        $query->from('#__eventtableedit_details AS a');

        // Join over the language
        $query->select('l.title AS language_title');
        $query->join('LEFT', '`#__languages` AS l ON l.lang_code = a.language');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('a.access = '.(int) $access);
        }

        // Filter by published state
        $published = $this->getState('filter.published');
		
        if (is_numeric($published)) {
            $query->where('a.published = '.(int) $published);
        } else{
            $query->where('(a.published = 0 OR a.published = 1)');
        }

        // Filter by search in name.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$query->where('(a.name LIKE '.$search.' OR a.alias LIKE '.$search.')');
		}
        $search = $input->get('filter_search');
		if ('' !== $search) {
            $search = $db->Quote('%'.$db->escape($search, true).'%');
            $query->where('(a.name LIKE '.$search.')');
        }
        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->where('a.language = '.$db->quote($language));
        }

        // condition to check table is normal or apppointment
        $query->where('a.normalorappointment = 1');

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering', '');
        $orderDirn = $this->state->get('list.direction', '');

        //$query->order($db->escape($orderCol.' '.$orderDirn));
        $query = $query.' ORDER BY '.$orderCol.' '.$orderDirn;
		
		
		
		return $query;
	}
}
