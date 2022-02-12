<?php
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
// no direct access
defined('_JEXEC') or die;
?>
<script>
Joomla.submitbutton = function(task)
{
	if (task == '')
	{
		return false;
	}
	else
	{
		Joomla.submitform(task);
		return true;
	}
}
</script>
<form action="<?php echo JUri::getInstance();?>" class="form-validate" method="post" name="adminForm" id="adminForm">
	<div class="">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_EVENTTABLEEDIT_CSVEXPORT_TITLE'); ?></legend>
		
		<p><?php echo JText::_('COM_EVENTTABLEEDIT_CSVEXPORT_TITLE_INFO'); ?></p>
		
		<ul class="adminformlist" style="float: left;">
			<input type="hidden" name="tableList" id="tableList" value="<?php echo $this->id?>"/>
			<li>
				<label id="file-lbl" for="file" class="hasPopover" title="" data-content="<?php echo JText::_('COM_EVENTTABLEEDIT_SEPARATOR'); ?>" data-original-title="<?php echo JText::_('COM_EVENTTABLEEDIT_SEPARATOR'); ?>"><?php echo JText::_('COM_EVENTTABLEEDIT_SEPARATOR'); ?>: </label>
				<select name="separator">
					<option selected="selected">;</option>
					<option>,</option>
					<option>:</option>
				</select>
			</li>
			<li>
				<label id="file-lbl" for="file" class="hasPopover" title="" data-content="<?php echo JText::_('COM_EVENTTABLEEDIT_DOUBLEQUOTES'); ?>" data-original-title="<?php echo JText::_('COM_EVENTTABLEEDIT_DOUBLEQUOTES'); ?>"><?php echo JText::_('COM_EVENTTABLEEDIT_DOUBLEQUOTES'); ?>: </label>
				<select name="doubleqt">
					<option selected="selected" value="1"><?php echo JText::_('JYES'); ?></option>
					<option value="0"><?php echo JText::_('JNO'); ?></option>
				</select>
			</li>
			
			<li>
				<label id="file-lbl" for="file" class="hasPopover" title="" data-content="<?php echo JText::_('COM_EVENTTABLEEDIT_CSVEXPORT_TIMESTAMP'); ?>" data-original-title="<?php echo JText::_('COM_EVENTTABLEEDIT_CSVEXPORT_TIMESTAMP'); ?>"><?php echo JText::_('COM_EVENTTABLEEDIT_CSVEXPORT_TIMESTAMP'); ?>: </label>
				<select name="csvexporttimestamp">
					<option selected="selected" value="1"><?php echo JText::_('JYES'); ?></option>
					<option value="0"><?php echo JText::_('JNO'); ?></option>
				</select>
			</li>
			
			
			<li style="list-style:none;">
				<button onclick="Joomla.submitbutton('csvexport.export');" class="btn btn-primary button-apply btn-success">
					<?php echo JText::_('COM_EVENTTABLEEDIT_EXPORT');?>
				</button>
				<input type="button" name="button" onclick="window.location.href='<?php echo base64_decode($_REQUEST['return'])?>';" class="btn btn-primary" value="<?php echo JText::_('COM_EVENTTABLEEDIT_BACK');?>">
			</li>
		</ul>
		
	</fieldset>
	</div>
	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<?php echo JHtml::_('form.token'); ?>
</form>