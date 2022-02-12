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
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'xmlimport.cancel') {
			window.location.href = 'index.php?option=com_eventtableedit&view=etetables';
			return true;
		}
		if (task == 'xmlimport.upload' && checkForm()) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
		else {			
			document.getElementById('fupload').focus();
		}
	}

	function checkForm() {
		$val = document.getElementById('fupload').value;
		
		if($val == '')
		{
			return false;
		}
		return true;
	}
</script>

<form action="<?php echo Route::_('index.php?option=com_eventtableedit'); ?>" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
	<div class="">
	<fieldset class="adminform">
		<legend><?php echo Text::_('COM_EVENTTABLEEDIT_UPLOAD_XMLFILE'); ?></legend>
		
		
		<ul class="adminformlist" style="float: left;">
			
			<li>
				<label id="file-lbl" for="file" class="hasPopover" title="" data-content="<?php echo Text::_('COM_EVENTTABLEEDIT_XMLFILE_DESC'); ?>" data-original-title="<?php echo Text::_('COM_EVENTTABLEEDIT_XMLFILE'); ?>"><?php echo Text::_('COM_EVENTTABLEEDIT_XMLFILE'); ?>: </label>
				<input type="file" name="fupload" id="fupload"/>
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