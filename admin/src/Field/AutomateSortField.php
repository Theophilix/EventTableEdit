<?php

namespace ETE\Component\EventTableEdit\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Fields automateSort
 *
 * @since  3.7.0
 */
class automateSortField extends ListField
{
	/**
	 * @var    string
	 */
	public $type = 'automateSort';

	protected function getOptions()
	{
		
		
		$jinput = Factory::getApplication()->input;
        $id = $jinput->get('id');
		
        if (!$id) {
            $options = [];
        } else {
            $db = Factory::getDBO();
            $query = $db->setQuery("select * from #__eventtableedit_heads where table_id = $id");
            $values = $db->loadObjectList();
			$options =[];
			foreach($values as $value){
				$options['head_' . $value->id . ',' . 'asc'] = $value->name . " | ASC";
				$options['head_' . $value->id . ',' . 'desc'] = $value->name . " | DESC";
			}
        }
	
		return array_merge(parent::getOptions(), $options);
	}
}
