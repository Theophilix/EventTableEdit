<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_eventtableedit
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\etetables\Administrator\View\etetable\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')->useScript('jquery');
/* 	->useScript('com_eventtableedit.admin-etetable-edit'); */

?>

<form action="<?php echo Route::_('index.php?option=com_eventtableedit&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="etetable-form" aria-label="<?php echo Text::_('com_eventtableedit_etetable_FORM_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_EVENTTABLEEDIT_ETETABLE_DETAILS')); ?>
		<div class="row">
			<div class="col-lg-9">
				<?php echo $this->form->renderField('row'); ?>
				<?php echo $this->form->renderField('col'); ?>
				
				<?php echo $this->form->renderFieldset('details'); ?>
				
				
				
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('add_option_list'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('add_option_list'); ?></div>
				</div>	
				<div class="control-group correspond_table">
					<table class="adminlist" id="dropdown-table" style="width: 100%;">
						<thead>
							<tr>
								<th width="10%"></th>
								<th width="40%"><?php echo Text::_('COM_EVENTTABLEEDIT_GLOBAL_OPTIONS'); ?></th>
								<th width="40%"><?php echo Text::_('COM_EVENTTABLEEDIT_CORRESPONDING_TABLE'); ?></th>
								<th><?php echo Text::_('COM_EVENTTABLEEDIT_DELETE'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php

                            if ('' !== $this->item->corresptable) {
                                $corresptables = json_decode($this->item->corresptable, true);

                                if (!empty($corresptables)) {
                                    foreach ($corresptables as $global_option => $corresptable) {
                                        ?>
										<tr>
											<td><span class="icon-move"></span></td>
											<td width="40%"><input type="text" name="global_options[]" id="global_options" value="<?php echo $global_option; ?>"/></td>
											<td width="40%"><select name="corresponding_table[]" id="corresponding_table">
												<?php
                                                foreach ($this->appointment_tables as $appointment_tables) {
                                                    ?><option <?php if ($appointment_tables->id === $corresptable) {
                                                        echo "selected='selected'";
                                                    } ?> value="<?php echo $appointment_tables->id; ?>"><?php echo $appointment_tables->name; ?></option><?php
                                                } ?>
											</select></td>											
											<td><img src="<?php echo JURI::root(); ?>administrator/components/com_eventtableedit/images/cross.png" class="correspond_delete"></td>
										</tr>
										<?php
                                    }
                                }
                            } else {
                                ?>
								<tr>
									<td><span class="icon-move"></span></td>
									<td width="40%"><input type="text" name="global_options[]" id="global_options" value=""/></td>
									<td width="40%"><select name="corresponding_table[]" id="corresponding_table">
														<?php
                                                        foreach ($this->appointment_tables as $appointment_tables) {
                                                            ?><option value="<?php echo $appointment_tables->id; ?>"><?php echo $appointment_tables->name; ?></option><?php
                                                        } ?>
													</select></td>
									<td><img src="<?php echo JURI::root(); ?>administrator/components/com_eventtableedit/images/cross.png" class="correspond_delete"></td>
								</tr>
								<?php
                            }?>
						</tbody>
					</table>
					<div id="addNew"></div>
					
					
						<style>
							<?php if (0 === (int)$this->item->add_option_list) {?>
							.correspond_table{display: none;}
							<?php }?>
							.correspond_table{list-style: none;}
							.correspond_delete{background: transparent;border: none;text-decoration: underline;cursor: pointer;}
							#addNew {
								background: url(<?php echo JURI::root();?>/administrator/components/com_eventtableedit/images/add.png) top left no-repeat;
								width: 32px;
								height: 32px;
								cursor: pointer;
								margin-left: 4px;
								margin-top: 7px;
							}
						</style>
					</div>	
				
				
				
				
				
			</div>
			<div class="col-lg-3">
				<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'style', Text::_('COM_EVENTTABLEEDIT_STYLE')); ?>
			<fieldset id="fieldset-style" class="options-form">
				<legend><?php echo Text::_('COM_EVENTTABLEEDIT_STYLE'); ?></legend>
				<div>
					<?php echo $this->form->renderFieldset('style'); ?>
				</div>
			</fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
		<div class="row">
			<div class="col-md-6">
				<fieldset id="fieldset-publishingdata" class="options-form">
					<legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
					<div>
						<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
					</div>
				</fieldset>
			</div>
			<div class="col-md-6">
				<fieldset id="fieldset-metadata" class="options-form">
					<legend><?php echo Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
					<div>
					<?php echo $this->form->renderFieldset('jmetadata'); ?>
					</div>
				</fieldset>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'acl', Text::_('COM_EVENTTABLEEDIT_FIELDSET_RULES')); ?>
		<div class="row">
			<div class="col-md-12">
				<?php echo $this->form->renderFieldset('acl'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="">
	<!-- added to resolve loading issue -->
	<input type="hidden" name="title" id="jform_title" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
<script type="text/javascript">

	$(document).ready(function(){
		//$("#sortable").sortable({items: '> .tablerow'});
		$("#dropdown-table tbody").sortable({
				items: '> tr'
			});
	})
</script>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery("#addNew").click(function(){
		var varHtml = '<tr><td><span class="icon-move"></span></td><td width="40%"><input type="text" name="global_options[]" value=""/></td><td width="40%"><select name="corresponding_table[]"><?php foreach ($this->appointment_tables as $appointment_tables) { ?><option value="<?php echo $appointment_tables->id; ?>"><?php echo $appointment_tables->name; ?></option><?php } ?></select></td><td><img src="<?php echo JURI::root(); ?>administrator/components/com_eventtableedit/images/cross.png" class="correspond_delete"></td></tr>';
		jQuery("#dropdown-table tbody").append(varHtml);
		
		$("#dropdown-table tbody").sortable({
				items: '> tr'
			});
	})
	jQuery('body').on('click','.correspond_delete',function(){
		jQuery(this).parent().parent().remove();
	})
	jQuery('#jform_add_option_list input[type=radio]').change(function() {
		if (this.value == 1) {
			jQuery(".correspond_table").show();
		}
		else if (this.value == 0) {
			jQuery(".correspond_table").hide();
		}
	});
	
	jQuery('body').on('click','.up,.down',function(){
		var row = jQuery(this).parents("tr:first");
		if (jQuery(this).is(".up")) {
			row.insertBefore(row.prev());
		} else {
			row.insertAfter(row.next());
		}
	});	
});

</script>
<style>
#dropdown-table tbody tr {
    border: solid 1px;
}
#dropdown-table tbody tr td{
	padding: 10px;
}
</style>

