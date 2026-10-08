<?php
global $cfg;

if (!$info[':title'])
    $info[':title'] = __('Delete');
?>
<div class="panel panel-primary panel-dark m-b-0">
	<div class="panel-heading">	
		<div class="drag-handle panel-title"><?php echo $info[':title']; ?>
		<a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a>
		</div>
	</div>	
	<div class="panel-body">
		<?php
		if ($info['error']) {
			echo sprintf('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['error']);
		} elseif ($info['warn']) {
			echo sprintf('<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['warn']);
		} elseif ($info['msg']) {
			echo sprintf('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;%s</div>', $info['msg']);
		} elseif ($info['notice']) {
		   echo sprintf('<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-info-circle"></i>&nbsp;&nbsp;%s</div>',
				   $info['notice']);
		}


		$action = $info[':action'] ?: ('#');
		?>

	<div style="display:block; margin:5px;">
		<form class="mass-action" method="post" name="delete" id="delete" action="<?php echo $action; ?>">
			<?php csrf_token(); ?>
			<div class="form-group">
				<?php if ($info[':extra']) { ?>
				<div class="form-group">
					<label class="control-label"><?php echo $info[':extra']; ?></label>
				</div>
				<?php
				}
			   ?>
				<div class="form-group">
					<?php
					$placeholder = $info[':placeholder'] ?: __('Optional reason for the deletion');
					?>
					<textarea name="comments" id="comments" cols="50" rows="3" wrap="soft" style="width:100%" class="form-control <?php if ($cfg->isRichTextEnabled()) echo 'summernote-base'; ?>" placeholder="<?php echo $placeholder ;?>"><?php echo $info['comments']; ?></textarea>
				</div>
			</div>
			<div style="margin-top:20px;">
				<input type="submit" class="btn btn-danger" value="<?php
				echo $verb ?: __('Delete'); ?>">
				<input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
				<input type="button" name="cancel" class="close_me btn"
				value="<?php echo __('Cancel'); ?>">
			 </div>
		</form>
	</div>
	</div>
</div>
