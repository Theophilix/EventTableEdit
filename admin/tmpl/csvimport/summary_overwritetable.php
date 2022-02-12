<?php

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
$app = Factory::getApplication();
?>

<legend><?php echo Text::_('COM_EVENTTABLEEDIT_OVERWRITE_TABLE'); ?></legend>

<?php if (!$app->getUserState('com_eventtableedit.csvError', true)) :?>
	<div id="summaryHead"><?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_SUCCESS'); ?></div>
	<p><?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_OVERWRITE'); ?></p>
<?php else: ?>
	<div id="summaryHeadFailed"><?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_FAILED'); ?></div>
	<p><?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_REPORT_OVERWRITE_FAILED'); ?></p>
<?php endif; ?>