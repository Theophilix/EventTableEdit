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
			<div class="col-lg-12">
				<div id="dropdown-table">
					<div class="tableheading">
						<div>
							<?php echo Text::_('JGLOBAL_TITLE'); ?>
						</div>
						<div style="display:none;">
							<?php echo Text::_('JGRID_HEADING_ORDERING'); ?>
						</div>
						<div>
							<?php echo Text::_('COM_EVENTTABLEEDIT_DELETE'); ?>
						</div>
					</div>
					<ul class="tablebody" id="sortable">
						<?php
						foreach($this->dropdowns as $dropdown){
							?>
							<li class="tablerow"><div class="innerrow"><span class="icon-move"></span><div><input type="text" value="<?php echo $dropdown->name;?>" name="dropdowns[]"/></div><div><img class="closeBtn" src="<?php echo JURI::root();?>/administrator/components/com_eventtableedit/images/cross.png"/></div></div></li>
							<?php
						}
						?>
					</ul>
				</div>
				<div id="addNew"></div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
<script type="text/javascript">

	$(document).ready(function(){
		//$("#sortable").sortable({items: '> .tablerow'});
		$("#sortable").sortable({
				items: '> .tablerow'
			});
		$('#addNew').click(function(){
			$('.tablebody').append('<li class="tablerow"><div class="innerrow"><span class="icon-move"></span><div><input type="text" name="dropdowns[]"/></div><div><img class="closeBtn" src="<?php echo JURI::root();?>/administrator/components/com_eventtableedit/images/cross.png"/></div></div></li>');
			
			//$("#sortable").sortable('destroy');
			$("#sortable").sortable({
				items: '> .tablerow'
			});
		});
		
		$('body').on('click', '.closeBtn', function(){
			$(this).parent().closest(".tablerow").remove();
		})
	})
</script>
<style>
#addNew {
    background: url(<?php echo JURI::root();?>/administrator/components/com_eventtableedit/images/add.png) top left no-repeat;
    width: 32px;
    height: 32px;
    cursor: pointer;
    margin-left: 4px;
    margin-top: 7px;
	float: left;
}
ul#sortable {
    padding: 0;
    margin: 0;
    width: 100%;
    float: left;
}
.tableheading, .tablerow{
	width: 100%;
    padding: 10px;
    margin: 4px 0;
    float: left;
    list-style: none;
    border: solid 1px #d6d6d6;
}

.tableheading div{
	float:left;
	padding: 5px;
	min-width: 30%;
}
.closeBtn{
	cursor: pointer;
}
.innerrow{
	width: 100%;
	float:left;
}
.innerrow div {
        float: left;
    margin-right: 0%;
    width: 88%;
}
.innerrow div:last-child {
    float: right;
    margin-right: 0%;
	width: 10%;
    text-align: right;
}
.tableheading div:last-child {
    float: right;
    margin: 0;
    width: 50%;
    text-align: right;
}
.innerrow input{
	width: 100%;
	
}
.innerrow span.icon-move {
    float: left;
    margin: 7px 10px 0 0;
	cursor: move;
}
</style>