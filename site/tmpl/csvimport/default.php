<?php
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
// no direct access
defined('_JEXEC') or die;
?>


<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'csvimport.upload' && checkTableName()) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
		else {
			alert('<?php echo $this->escape(Text::_('COM_EVENTTABLEEDIT_ERROR_ENTER_NAME')); ?>');
			jQuery('#tableName').focus();
		}
	}

	function checkTableName() {
		$val = jQuery('input[name=importaction]:checked').val();
		
		if($val == 'newTable')
		{
		   if (jQuery('#tableName').val() == '') {
			  return false;
		   }
		}
		
		if($val == 'overwriteTable')
		{
		   if (jQuery('#tableList').val() == '') {
			  return false;
		   }
		}
		
		if($val == 'appendTable')
		{
		   if (jQuery('#tableList1').val() == '') {
			  return false;
		   }
		}
		
		return true;
	}
</script>
<form action="<?php echo JUri::getInstance();?>" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
	<div class="">
	<fieldset class="adminform">
		<legend><?php echo Text::_('COM_EVENTTABLEEDIT_UPLOAD_FILE'); ?></legend>
		
		<p><?php echo Text::sprintf('COM_EVENTTABLEEDIT_CSVIMPORT_DESC', (int) $this->maxFileSize); ?></p>
		
		<ul class="adminformlist" style="float:left;">
			<input type="hidden" name="checkfun" value="<?php echo $this->checkfun;?>"/>
			
			<li>
				<label class="hasPopover" title="" data-content="<?php echo Text::_('COM_EVENTTABLEEDIT_CSVFILE_DESC'); ?>" data-original-title="<?php echo Text::_('COM_EVENTTABLEEDIT_CSVFILE_DESC'); ?>"><?php echo Text::_('COM_EVENTTABLEEDIT_CSVFILE'); ?>: </label>
				<input type="file" required name="fupload" />
			</li>
			<li>
				<label class="hasPopover" title="" data-content="<?php echo Text::_('COM_EVENTTABLEEDIT_SEPARATOR_DESC'); ?>" data-original-title="<?php echo Text::_('COM_EVENTTABLEEDIT_SEPARATOR_DESC'); ?>"><?php echo Text::_('COM_EVENTTABLEEDIT_SEPARATOR'); ?>: </label>
				<select name="separator">
					<option selected="selected">;</option>
					<option>,</option>
					<option>:</option>
				</select>
			</li>
			<li>
				<label class="hasPopover" title="" data-content="<?php echo Text::_('COM_EVENTTABLEEDIT_DOUBLEQUOTES_DESC'); ?>" data-original-title="<?php echo Text::_('COM_EVENTTABLEEDIT_DOUBLEQUOTES_DESC'); ?>"><?php echo Text::_('COM_EVENTTABLEEDIT_DOUBLEQUOTES'); ?>: </label>
				<select name="doubleqt">
					<option selected="selected" value="1"><?php echo Text::_('JYES'); ?></option>
					<option value="0"><?php echo Text::_('JNO'); ?></option>
				</select>
			</li>
			
			<li>
				<label class="hasPopover" title="" data-content="<?php echo Text::_('COM_EVENTTABLEEDIT_CSVACTIONS_DESC'); ?>" data-original-title="<?php echo Text::_('COM_EVENTTABLEEDIT_CSVACTIONS_DESC'); ?>"><b><?php echo Text::_('COM_EVENTTABLEEDIT_CSVACTIONS'); ?>: </b></label>
				<ul class="etetable-import-actions" style="list-style: none;margin-left: 0;">
					<li>
						<fieldset class="radio">
							<input type="radio" name="importaction" id="newTable" value="overwriteTableWithHeader" 
								   checked /> 
								   <label class="hasPopover" for="newTable" title="" data-content="<?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_OVERWRITE_TABLE_WITH_HEADER'); ?>" data-original-title="<?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_OVERWRITE_TABLE_WITH_HEADER'); ?>"><?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_OVERWRITE_TABLE_WITH_HEADER'); ?></label>
								   
						</fieldset>
					</li>
					<li>
						<fieldset class="radio">
						<input type="radio" name="importaction" id="overwriteTable" value="overwriteTableWithoutHeader"
							  /> 
							   <label class="hasPopover" for="overwriteTable" title="" data-content="<?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_OVERWRITE_TABLE_WITHOUT_HEADER'); ?>" data-original-title="<?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_OVERWRITE_TABLE_WITHOUT_HEADER'); ?>"><?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_OVERWRITE_TABLE_WITHOUT_HEADER'); ?></label>
								
						</fieldset>
					</li>
					<li>
						<fieldset class="radio">
						<input type="radio" name="importaction" id="appendTable" value="appendTable"
							   /> 
							   <label class="hasPopover" for="appendTable" title="" data-content="<?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_APPEND_TO_TABLE'); ?>" data-original-title="<?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_APPEND_TO_TABLE'); ?>"><?php echo Text::_('COM_EVENTTABLEEDIT_IMPORT_APPEND_TO_TABLE'); ?></label>
							   
						</fieldset>
					</li>
				</ul>
			</li>
			
		
			<li style="list-style: none;">
				<p id="tables1">
					<?php echo $this->tables; ?>
				</p>
				<input type="hidden" name="tableList" value="<?php echo $this->id;?>" />
				<input type="hidden" name="returnURL" value="<?php echo $_REQUEST['return']?>" />
				<input type="hidden" name="returnURLsame" value="<?php echo JUri::getInstance()?>" />
			</li>
			<li style="list-style: none;">
				<button onclick="if (document.adminForm.boxchecked.value == 0) { alert(Joomla.Text._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')); } else { Joomla.submitbutton('csvimport.upload'); }" class="btn btn-small button-upload"><span class="icon-upload" aria-hidden="true"></span>
	<?php echo Text::_('COM_EVENTTABLEEDIT_UPLOAD')?></button>
				<input type="button" name="button" onclick="window.location.href='<?php echo base64_decode($_REQUEST['return'])?>'" class="btn btn-primary" value="<?php echo Text::_('COM_EVENTTABLEEDIT_BACK');?>">
			</li>
		</ul>
	</fieldset>
	</div>
	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<style>#tables, #tables1, #tables2{display: none;}</style>