<?php

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
// no direct access
defined('_JEXEC') or die;

$app = Factory::getApplication();

$params = $app->getParams();
$main = Factory::getApplication()->input;
$tablenumber = $main->getInt('id', '');
$Itemid = $main->getInt('Itemid', '');

?>

<div class="eventtableedit<?php echo $this->params->get('pageclass_sfx'); ?>">

<h2 class="etetable-title">
	<?php echo JText::_('COM_EVENTTABLEEDIT_ETETABLE_ADMIN').' '.$this->info['tablename']; ?>
</h2>

<div id="changetable-toolbar">
	<ul>
		<li id="changetable-newrow" class="etetable-toolbutton">
			<span id="icon-32-new"></span>
			<?php echo JText::_('COM_EVENTTABLEEDIT_NEW'); ?>
		</li>

		<li id="changetable-save" class="etetable-toolbutton">
			<span id="icon-32-save"></span>
			<?php echo JText::_('COM_EVENTTABLEEDIT_SAVE'); ?>
		</li>

		<li id="changetable-cancel" class="etetable-toolbutton">
			<span id="icon-32-cancel"></span>
			<?php echo JText::_('COM_EVENTTABLEEDIT_CANCEL'); ?>
		</li>
	</ul>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_eventtableedit'); ?>" method="post" name="adminForm" id="adminForm">
	<table class="adminlist" id="changetable-table">
		<thead>
			<tr>
				<th>
					<?php echo JText::_('COM_EVENTTABLEEDIT_NAME'); ?>
				</th>
				<th width="25%">
					<?php echo JText::_('COM_EVENTTABLEEDIT_DATATYPE'); ?>
				</th>
				<th width="9%">
					<?php echo JText::_('COM_EVENTTABLEEDIT_ORDERING'); ?>
				</th>
				<!--<th width="15%">
					<?php //echo JText::_('COM_EVENTTABLEEDIT_AUTOSORT');?>
				</th>-->
				<th width="5%">
					<?php echo JText::_('COM_EVENTTABLEEDIT_DELETE'); ?>
				</th>
			</tr>
		</thead>
		
		<tbody>
			
		</tbody>
	</table>
	
	<?php echo JHtml::_('form.token'); ?>
	<input type="hidden" name="task" value="changetable.save" />
	<input type="hidden" name="id" value="<?php echo $tablenumber; ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
</form>

</div>
