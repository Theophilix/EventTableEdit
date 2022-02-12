<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_eventtableedit
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ETE\Component\EventTableEdit\Administrator\View\Appointmenttable;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit a Appointmenttable.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		
		$this->fields = $this->get('Fields');
		
		$this->appointment_tables = $this->get('AppTables');
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// If we are forcing a language in modal (used for associations).
		if ($this->getLayout() === 'modal' && $forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'cmd'))
		{
			// Set the language field to the forcedLanguage and disable changing it.
			$this->form->setValue('language', null, $forcedLanguage);
			$this->form->setFieldAttribute('language', 'readonly', 'true');

			// Only allow to select categories with All language or with the forced language.
			$this->form->setFieldAttribute('catid', 'language', '*,' . $forcedLanguage);

			// Only allow to select tags with All language or with the forced language.
			$this->form->setFieldAttribute('tags', 'language', '*,' . $forcedLanguage);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user       = Factory::getUser();
		$userId     = $user->id;
		$isNew      = ($this->item->id == 0);
		$checkedOut = !(is_null($this->item->checked_out) || $this->item->checked_out == $userId);

		// Since we don't track these assets at the item level, use the category id.
		$canDo = ContentHelper::getActions('com_eventtableedit');

		ToolbarHelper::title($isNew ? Text::_('COM_EVENTTABLEEDIT_NEW_ETETABLE') : Text::_('COM_EVENTTABLEEDIT_MANAGER_ETETABLE'), '');

		// Build the actions for new and existing records.
		if ($isNew)
		{
			ToolbarHelper::apply('appointmenttable.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save('appointmenttable.save', 'JTOOLBAR_SAVE');
            ToolbarHelper::custom('appointmenttable.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			ToolbarHelper::cancel('appointmenttable.cancel');
		}
		else
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

			$toolbarButtons = [];

			// Can't save the record if it's checked out and editable
			if (!$checkedOut && $itemEditable)
			{
				ToolbarHelper::apply('appointmenttable.apply');

				$toolbarButtons[] = ['save', 'appointmenttable.save'];

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create'))
				{
					$toolbarButtons[] = ['save2new', 'appointmenttable.save2new'];
				}
			}

			// If checked out, we can still save
			if ($canDo->get('core.create'))
			{
				$toolbarButtons[] = ['save2copy', 'appointmenttable.save2copy'];
			}

			ToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
			);

			ToolbarHelper::cancel('appointmenttable.cancel', 'JTOOLBAR_CLOSE');

			if (ComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $itemEditable)
			{
				ToolbarHelper::versions('com_eventtableedit.appointmenttable', $this->item->id);
			}

		}
	}
}
