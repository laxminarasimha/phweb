<div class="panel panel-primary panel-dark m-b-0">
	<div class="panel-heading">
		<div class="drag-handle panel-title table-caption"><?php echo __('Raw Email Headers'); ?><a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a></div>
	</div>
	<div class="panel-body">	
		<pre style="max-height: 300px; overflow-y: scroll">
		<?php echo Format::htmlchars($headers); ?>
		</pre>
		<div style="margin-top:20px;">
			<input type="button" name="cancel" class="btn close_me" value="<?php echo __('Close'); ?>">
		</div>
	</div>
</div>
