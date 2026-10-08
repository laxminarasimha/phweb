<div class="panel panel-primary panel-dark m-b-0">
<div id="the-lookup-form">
	<div class="panel-heading">
		<div class="drag-handle panel-title">
			<?php echo $info['title']; ?>
			<a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a>
		</div>
	</div>
	<div class="panel-body perfectScrollbar" style="height: 300px;position: relative;">
		<?php
		if ($info['error']) {
			echo sprintf('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['error']);
		} elseif ($info['warn']) {
			echo sprintf('<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['warn']);
		} elseif ($info['msg']) {
			echo sprintf('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;%s</div>', $info['msg']);
		} ?>

		<ul class="nav nav-tabs" id="user-import-tabs">
			<li class="active">
				<a href="#copy-paste" data-toggle="tab"><i class="fa fa-pencil-square-o"></i>&nbsp;<?php echo __('Copy Paste'); ?></a>
			</li>
			<li>
				<a href="#upload" data-toggle="tab"><i class="fa fa-cloud-upload"></i>&nbsp;<?php echo __('Upload'); ?></a>
			</li>
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
		<div id="user-import-tabs_container" class="tab-content tab-content-bordered">
			<div class="tab-pane fade active in" id="copy-paste" style="margin:5px;">
				<div class="table-caption" style="margin-bottom:10px"><?php echo __('Name and Email'); ?>
				</div>
				<p><?php echo __(
				'Enter one name and email address per line.'); ?><br/><?php echo __(
				'To import more other fields, use the Upload tab.'); ?>
				</p>
				<textarea name="pasted" style="display:block;width:100%;height:8em" placeholder="<?php echo __('e.g. John Doe, john.doe@deskuss.com'); ?>"><?php echo $info['pasted']; ?></textarea>
			</div>

		<div class="tab-pane fade" id="upload" style="margin:5px;">
			<div class="table-caption" style="margin-bottom:10px"><?php echo __('Import a CSV File'); ?>
			</div>
			<p>
			<?php echo sprintf(__(
			'Use the columns shown in the table below. To add more fields, visit the Admin Panel -&gt; Manage -&gt; Forms -&gt; %s page to edit the available fields.  Only fields with `variable` defined can be imported.'),
				UserForm::getUserForm()->get('title')
			); ?>
			</p>
			<table class="table table-bordered table-hover">
			<tr>
			<?php
				$fields = array();
				$data = array(
					array('name' => __('John Doe'), 'email' => __('john.doe@deskuss.com'))
				);
				foreach (UserForm::getUserForm()->getFields() as $f)
					if ($f->get('name'))
						$fields[] = $f->get('name');
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
			</tr></table>
		<br/>

			  <input type="file" name="import"/>
					  
		</div>
			<div class="form-group" style="margin-top:20px;">
				<input type="submit" class="btn btn-success" value="<?php echo __('Import Users'); ?>">
				<input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
				<input type="button" name="cancel" class="close_me btn"  value="<?php
				   echo __('Cancel'); ?>">
			 </div>
		</form>
		</div>
	</div>	
</div>

<script type="text/javascript">
	
	$(document).ready(function (){
		$('.perfectScrollbar').perfectScrollbar();
	});
</script>
