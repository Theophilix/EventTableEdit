<?php

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'xmlexport.cancel') {
			window.location.href = 'index.php?option=com_eventtableedit&view=etetables';
			return true;
		}
		else{
			Joomla.submitform(task);
			return true;
		}
	}
</script>
<form action="<?php echo Route::_('index.php?option=com_eventtableedit'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="">
	<fieldset class="adminform">
		<legend><?php echo Text::_('COM_EVENTTABLEEDIT_XMLEXPORT_TITLE'); ?></legend>
		
		<textarea readonly="readonly" rows="20" cols="100" id="export-text"><?php echo $this->orderxml; //readfile($file); //echo $this->csvFile;?></textarea>
		<input type="hidden" name="tableList" value="<?php echo $this->id; ?>" >
		<input type="hidden" name="xmlexporttimestamp" value="<?php echo $this->xmlexporttimestamp; ?>" >
	</fieldset>
	</div>
	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<?php echo JHtml::_('form.token'); ?>
</form>
