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
	->useScript('form.validate');
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
