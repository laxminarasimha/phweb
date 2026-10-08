<div class="panel panel-primary panel-dark" style="margin-bottom:0;border:none">
<div class="panel-heading">
	<div class="panel-title drag-handle"><?php echo $info['title']; ?>
		<a class="close_me pull-right" style="color:#fff" href="#"><i class="fa fa-close"></i></a>
	</div>
</div>
<div class="panel-body">
<?php
if ($info['error']) {
	echo sprintf('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['error']);
} elseif ($info['warn']) {
	echo sprintf('<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['warn']);
} elseif ($info['msg']) {
	echo sprintf('<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-info-circle"></i>&nbsp;&nbsp;%s</div>', $info['msg']);
} ?>
<ul class="nav nav-tabs" id="user-import-tabs">
	<li class="active"><a href="#copy-paste" data-toggle="tab" aria-expanded="true">
		<?php echo __('Copy Paste'); ?></a></li>
	<li><a href="#upload" data-toggle="tab" aria-expanded="false">
		<?php echo __('Upload'); ?></a></li>
</ul>

<form action="<?php echo $info['action']; ?>" method="post" enctype="multipart/form-data"
	onsubmit="javascript:
	if ($(this).find('[name=import]').val()) {
		$(this).attr('action', '<?php echo $info['upload_url']; ?>');
		$(document).unbind('submit.dialog');
	}">
<?php echo csrf_token();
if ($org_id) { ?>
	<input type="hidden" name="id" value="<?php echo $org_id; ?>"/>
<?php } ?>
<div id="user-import-tabs_container"  class="tab-content " style="display:flow-root;padding-bottom:0px;">

<div class="tab-pane fade active in" id="copy-paste" style="margin:5px;">
	<h2 class="panel-title" style="margin-bottom:10px"><?php echo __('Value and Abbreviation'); ?></h2>
	<p><?php echo __(
		'Enter one name and abbreviation per line.'); ?><br/><em><?php echo __(
		'To import items with properties, use the Upload tab.'); ?></em>
	</p>
	<textarea name="pasted" class="form-control" rows="4"
		placeholder="<?php echo __('e.g. My Location, MY'); ?>"><?php echo $info['pasted']; ?></textarea>
</div>

<div class="tab-pane fade" id="upload" style="margin:5px;">
	<h2 class="panel-title" style="margin-bottom:10px"><?php echo __('Import a CSV File'); ?></h2>
	<p>
		<em><?php echo __(
		'Use the columns shown in the table below. To add more properties, use the Properties tab.  Only properties with `variable` defined can be imported.'); ?>
		</em>
	</p>
	<div class="row">
	<div class="col-md-12">
	<div class="table-light table-responsive">
	<table class="table table-bordered list">
	<tr>
		<?php
			$fields = array('Value', 'Abbreviation');
			$data = array(
				array('Value' => __('My Location'), 'Abbreviation' => 'MY')
			);
			foreach ($list->getConfigurationForm()->getFields() as $f)
				if ($f->get('name'))
					$fields[] = $f->get('label');
			foreach ($fields as $f) { ?>
				<th><?php echo mb_convert_case($f, MB_CASE_TITLE); ?></th>
		<?php } ?>
	</tr>
	<tr>
		<?php
			foreach ($data as $d) {
				foreach ($fields as $f) {
					?><td><?php
					if (isset($d[$f])) echo $d[$f];
					?></td><?php
				}
		} ?>
	</tr>
	</table>
	</div>
	</div>
	</div>
	<input type="file" class="form-control-file" name="import"/>
</div>

<br/>
<p class="full-width">
	<span class="buttons pull-left">
		<input type="submit" class="btn btn-success" value="<?php echo __('Import Items'); ?>">
		<input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
		<input type="button" name="cancel" class="close_me btn btn-default" value="<?php echo __('Cancel'); ?>">
	</span>
 </p>
</div>	 
</form>
</div>
</div>