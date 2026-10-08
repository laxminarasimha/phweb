<div class="panel panel-primary panel-dark m-b-0">
	<div class="panel-heading">
		<div class="drag-handle panel-title"><?php echo __('Collaborators'); ?><a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a>
		</div>
	</div>

	<div class="panel-body">
		<?php
		if($info && $info['msg']) {
			echo sprintf('<p id="msg_notice" style="padding-top:2px;">%s</p>', $info['msg']);
		} ?>
		<?php if(($users=$thread->getCollaborators())) {?>
		<div id="manage_collaborators">
			<form method="post" class="collaborators" action="#thread/<?php echo $thread->getId(); ?>/collaborators">
				<?php csrf_token(); ?>
				<div class="form-group">
				<?php
				foreach($users as $user) {
					$checked = $user->isActive() ? 'checked="checked"' : '';
					echo sprintf('<fieldset class="form-group form-inline">
									<div class="checkbox col-md-6 row">
										<label>
										<input type="checkbox" name="cid[]" id="c%d" value="%d" %s>
										</label>&nbsp;&nbsp;
										<a class="collaborator" href="#thread/%d/collaborators/%d/view">%s&nbsp;&nbsp;%s</a>&nbsp;
										<span class="faded">%s</span></div>
									<div class="col-md-6">
										<input type="hidden" name="del[]" id="d%d" value="">
										<a class="remove" href="#d%d"><i class="fa fa-remove"></i></a></div>
								</fieldset>',
								$user->getId(),
								$user->getId(),
								$checked,
								$thread->getId(),
								$user->getId(),
								(($U = $user->getUser()) && ($A = $U->getAvatar()))
									? $U->getAvatar()->getImageTag(24) : '',
								Format::htmlchars($user->getName()),
								$user->getEmail(),
								$user->getId(),
								$user->getId());
				}
				?>
				</div>
				<div>
					<a class="collaborator"
					href="#thread/<?php echo $thread->getId(); ?>/add-collaborator"
					><i class="fa fa-plus"></i> <?php echo __('Add New Collaborator'); ?></a>
				</div>
				<div id="savewarning" style="display:none; padding-top:2px;">
					<p id="msg_warning"><?php echo __('You have made changes that you need to save.'); ?></p>
				</div>
				<div class="form-group" style="margin-top:20px;">
					<input type="submit" class="btn btn-success" value="<?php echo __('Save Changes'); ?>">
					<input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
					<input type="button" value="<?php echo __('Cancel'); ?>" class="btn close_me">
				 </div>
			</form>
		</div>
	</div>
</div>
<?php
} else {
    echo __("Bro, not sure how you got here!");
}

if ($_POST && $thread && $thread->getNumCollaborators()) {

    $collaborators = sprintf('Participants (%d)',
            $thread->getNumCollaborators());

    $recipients = sprintf(__('Recipients (%d of %d)'),
          $thread->getNumActiveCollaborators(),
          $thread->getNumCollaborators());
    ?>
    <script type="text/javascript">
        $(function() {
            $('#emailcollab').show();
            $('#t<?php echo $thread->getId(); ?>-recipients')
            .html('<?php echo $recipients; ?>');
            $('#t<?php echo $thread->getId(); ?>-collaborators')
            .html('<?php echo $collaborators; ?>');
            });
    </script>
<?php
}
?>

<script type="text/javascript">
$(function() {

    $(document).on('click', 'form.collaborators a#addcollaborator', function (e) {
        e.preventDefault();
        $('div#manage_collaborators').hide();
        $('div#add_collaborator').fadeIn();
        return false;
     });

    $(document).on('click', 'form.collaborators a.remove', function (e) {
        e.preventDefault();
        var fObj = $(this).closest('form');
        $('input'+$(this).attr('href'))
            .val($(this).attr('href').substr(2))
            .trigger('change');
        $(this).closest('tr').addClass('strike');

        return false;
     });

    $(document).on('change', 'form.collaborators input:checkbox, input[name="del[]"]', function (e) {
       var fObj = $(this).closest('form');
       $('div#savewarning', fObj).fadeIn();
       $('input:submit', fObj).css('color', 'red');
     });

    $(document).on('click', 'form.collaborators input:reset', function(e) {
        var fObj = $(this).closest('form');
        fObj.find('input[name="del[]"]').val('');
        fObj.find('tr').removeClass('strike');
        $('div#savewarning', fObj).hide();
        $('input:submit', fObj).removeAttr('style');
    });

    $(document).on('click', 'form.collaborators input.cancel', function (e) {
        e.preventDefault();
        var $elem = $(this);

        if($elem.attr('data-href')) {
            var href = $elem.data('href').substr(1);
            $('.dialog.collaborators .body').load('ajax.php/'+href, function () {
                });
        } else {

            $('div#manage_collaborators').show();
            $('div#add_collaborator').hide();
        }
        return false;
    });

});
</script>
