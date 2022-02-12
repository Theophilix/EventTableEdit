<?php


defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
	->useScript('jquery');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'csvimport.cancel' || checkTableName()) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
		else {
			alert('<?php echo $this->escape(Text::_('COM_EVENTTABLEEDIT_ERROR_ENTER_NAME')); ?>');
		}
	}

	function checkTableName() {
		if (jQuery('#tableName').val() == '') {
			return false;
		}
		return true;
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_eventtableedit'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="">
		<fieldset class="adminform">
		<legend><?php echo Text::_('COM_EVENTTABLEEDIT_SET_SETTINGS'); ?></legend>
			<ul>
			<li style="display:none;">
				<label for="tableName"><b><?php echo Text::_('COM_EVENTTABLEEDIT_TABLE_NAME'); ?>: </b></label>
				
			</li>
			</ul>
			<table id="datatypeTable" border="0" width="90%">
				<?php for ($a = 0; $a < count($this->headLine); ++$a) :
                    if ('timestamp' !== $this->headLine[$a]):
                    ?>
					<tr>
						<td id="colText"><b><?php echo Text::_('COM_EVENTTABLEEDIT_DATATYPE_FOR').' '.$this->headLine[$a]; ?></b></td>
						<td><?php echo $this->listDatatypes; ?></td>
					</tr>
				<?php
                    endif;
                    endfor; ?>
			</table>
		</fieldset>
	</div>
	<input type="hidden" id="tableName" class="inputbox required" size="30" value="<?php echo $this->tableName; ?>" name="tableName" />
	<input type="hidden" name="task" value="" />
		<input type="hidden" name="checkfun" value="<?php echo $this->checkfun ? $this->checkfun : '0'; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>