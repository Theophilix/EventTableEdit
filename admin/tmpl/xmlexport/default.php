<?php
/**
 * $Id: default.php 140 2011-01-11 08:11:30Z kapsl $.
 *
 * @copyright (C) 2007 - 2020 Manuel Kaspar and Theophilix
 * @license GNU/GPL, see LICENSE.php in the installation package
 * This file is part of Event Table Edit
 *
 * Event Table Edit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * Event Table Edit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with Event Table Edit. If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
	->useScript('jquery');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'xmlexport.cancel') {
			window.location.href = 'index.php?option=com_eventtableedit&view=etetables';
			return true;
		}
		if (task == '')
		{
			return false;
		}
		else
		{
			var isValid=true;
			if(jQuery('select#tableList').val() == ""){
				isValid = false;
			}
		
			if (isValid)
			{
				Joomla.submitform(task);
				return true;
			}
			else
			{
				alert('<?php echo $this->escape(Text::_('COM_EVENTTABLEEDIT_PLEASE_SELECT_TABLE')); ?>');
				return false;
			}
		}
	}
</script>



<form action="<?php echo JRoute::_('index.php?option=com_eventtableedit'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="">
	<fieldset class="adminform">
		<legend><?php echo Text::_('COM_EVENTTABLEEDIT_XMLEXPORT_TITLE'); ?></legend>
		
		
		<ul class="adminformlist" style="float: left;">
			<li id="tableList">
				<label id="file-lbl" for="file" class="hasPopover" title="" data-content="<?php echo Text::_('COM_EVENTTABLEEDIT_EXPORT_TABLE_CHOICE'); ?>" data-original-title="<?php echo Text::_('COM_EVENTTABLEEDIT_EXPORT_TABLE_CHOICE'); ?>"><?php echo Text::_('COM_EVENTTABLEEDIT_EXPORT_TABLE_CHOICE'); ?>: </label>
				<?php echo $this->tables; ?>
			</li>
			<li>
				<label id="file-lbl" for="file" class="hasPopover" title="" data-content="<?php echo Text::_('COM_EVENTTABLEEDIT_XMLEXPORT_TIMESTAMP'); ?>" data-original-title="<?php echo Text::_('COM_EVENTTABLEEDIT_XMLEXPORT_TIMESTAMP'); ?>"><?php echo Text::_('COM_EVENTTABLEEDIT_XMLEXPORT_TIMESTAMP'); ?>: </label>
				<select name="xmlexporttimestamp">
					<option selected="selected" value="1"><?php echo Text::_('JYES'); ?></option>
					<option value="0"><?php echo Text::_('JNO'); ?></option>
				</select>
			</li>
		</ul>
	</fieldset>
	</div>
	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<style>

input[type="file"], select {
    width: 100%;
    margin: 10px 0;
}
p#tables2 {
    float: left;
    width: 100%;
    margin-left: 30px;
}
</style>