<?php
namespace ETE\Component\EventTableEdit\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Fields tableNumber
 *
 * @since  3.7.0
 */
class tableNumberField extends ListField
{
	/**
	 * @var    string
	 */
	public $type = 'tableNumber';

	protected function getOptions()
	{
		
		$db = Factory::getDBO();

        $query = 'SELECT id as value, name as text '.
                                 'FROM #__eventtableedit_details '.
                                 'WHERE published = 1 AND normalorappointment=0 '.
                                 'ORDER BY name ASC';
        $db->setQuery($query);

        $options = $db->loadObjectList();		
        
		return array_merge(parent::getOptions(), $options);
	}
}
