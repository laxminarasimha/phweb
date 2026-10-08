<?php
global $cfg;

$form = $form ?: TransferForm::instantiate($info);
?>
<div class="panel panel-primary panel-dark m-b-0">
<div class="panel-heading">
<div class="drag-handle panel-title"><?php echo $info[':title']; ?>
<a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a></div>
</div>
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
<div class="panel-body">
<div style="display:block; margin:5px;">
<form method="post" name="transfer" id="transfer"
    class="mass-action"
    action="<?php echo $action; ?>">
    <?php csrf_token(); ?>
    <div class="col-md-12">
        <?php
        if ($info[':extra']) {
            ?>
            <div class="form-group"><strong><?php echo $info[':extra'];
            ?></strong></div>
        <?php
        }
       ?>
            <div class="form-group">
             <?php
             $options = array('template' => 'simple', 'form_id' => 'transfer');
             $form->render(true, false, $options);
             ?>
            </div>
    
			<div class="form-group" style="margin-top:20px;">
					<input type="submit" class="btn btn-success" value="<?php
					echo $verb ?: __('Transfer'); ?>">
					<input type="reset" class="btn" value="<?php echo __('Reset'); ?>">
					<input type="button" name="cancel" class="close_me btn"
					value="<?php echo __('Cancel'); ?>">
			 </div>
	 </div>
</form>
</div>
</div>
</div>