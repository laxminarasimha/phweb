<?php

$properties_form = $item ? $item->getConfigurationForm($_POST ?: null)
	: $list->getConfigurationForm($_POST ?: null);
$hasProperties = count($properties_form->getFields()) > 0;
?>
<div class="panel panel-primary panel-dark" style="display:flow-root;border:none">
<div class="panel-heading">
<div class="drag-handle panel-title"><?php echo $list->getName(); ?> &mdash; <?php
	echo $item ? $item->getValue() : __('Add New List Item'); ?>
	<a class="close_me pull-right" style="color:#fff" href=""><i class="fa fa-close"></i></a></div>
</div>

<div class="panel-body">
<?php if ($hasProperties) { ?>
<ul class="nav nav-tabs" id="item_tabs">
	<li class="active">
		<a href="#value" data-toggle="tab" aria-expanded="true"><i class="fa fa-reorder"></i>
		<?php echo __('Value'); ?></a>
	</li>
	<li><a href="#item-properties" data-toggle="tab" aria-expanded="false"><i class="fa fa-asterisk"></i>
		<?php echo __('Item Properties'); ?></a>
	</li>
</ul>
<?php } ?>

<form method="post" class="tab-content" id="item_tabs_container" action="<?php echo $action; ?>">
	<?php
	echo csrf_token();
	$internal = $item ? $item->isInternal() : false;
?>

<div class="tab-pane fade active in" id="value">
<?php
	$form = $item_form;
	include 'dynamic-form-simple.tmpl.php';
?>
</div>

<div class="tab-pane fade" id="item-properties">
<?php
    if ($hasProperties) {
		$form = $properties_form;
		include 'dynamic-form-simple.tmpl.php';
    }
?>
</div>

<br/>
<p class="full-width">
	<span class="buttons pull-left">
		<input type="submit" value="<?php echo __('Save'); ?>" class="btn btn-success">
		<input type="reset" value="<?php echo __('Reset'); ?>" class="btn btn-info">
		<input type="button" value="<?php echo __('Cancel'); ?>" class="close_me btn btn-default">
	</span>
</p>
</div>
</form>
</div>
<script type="text/javascript">
	// Make translatable fields translatable
	$('input[data-translate-tag], textarea[data-translate-tag]').translatable();
</script>
