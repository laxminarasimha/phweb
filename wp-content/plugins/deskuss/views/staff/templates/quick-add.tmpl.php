<div class="panel panel-primary panel-dark m-b-0">
<div class="panel-heading">
<div class="drag-handle panel-title"><?php echo $title ?><a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a></div>
</div>
<div class="panel-body">
<?php if (isset($errors['err'])) { ?>
    <div id="msg_error" class="alert-danger"><?php echo Format::htmlchars($errors['err']); ?></div>
<?php } ?>
<form method="post" action="#<?php echo $path; ?>">
<?php csrf_token(); ?>
<div class="row">
  <div class="quick-add col-sm-12">
    <?php echo $form->asTable(); ?>
  </div>
</div>
<div class="row">
  <div class="form-group col-sm-12">
      <input type="submit" class="btn btn-success" value="<?php
        echo $verb ?: __('Create'); ?>" />
      <input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>" />
      <input type="button" name="cancel" class="close_me btn btn-default"
        value="<?php echo __('Cancel'); ?>" />
  </div>
</div>
</form>
</div>
</div>
