<div class="panel panel-primary panel-dark m-b-0" id="the-lookup-form">
	<div class="panel-heading">
		<div class="drag-handle panel-title">
			<?php echo $info['title']; ?> <a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a>
		</div>
	</div>

	<div class="panel-body perfectScrollbar" style="height: 300px;position: relative;">

	<?php
	$user = isset($user) ? $user : null;
	if (!isset($info['lookup']) || $info['lookup'] !== false) { ?>
	<div id="infomsg"><p id="msg_info"><i class="fa fa-info-circle"></i>&nbsp; <?php echo
		$thisstaff->hasPerm(User::PERM_CREATE)
		? __('Search existing users or Add a new user')
		: __('Search existing users');
	?></p></div>
		<div id="searchbox" style="margin-bottom:10px;">
		<input type="text" class="search-input form-control" placeholder="<?php echo __('Search by email, phone or name'); ?>" id="user-search" autofocus autocorrect="off" autocomplete="off" /><br />
		</div>
	<?php
	}

	if ($info['error']) {
		echo sprintf('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['error']);
	} elseif ($info['warn']) {
		echo sprintf('<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['warn']);
	} elseif ($info['msg']) {
		echo sprintf('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;%s</div>', $info['msg']);
	} ?>

	<div id="selected-user-info" style="display:<?php echo $user ? 'block' :'none'; ?>;margin:5px;">
	<form method="post" class="user" action="<?php echo $info['action'] ?  $info['action'] : '#users/lookup'; ?>">
		<input type="hidden" id="user-id" name="id" value="<?php echo $user ? $user->getId() : 0; ?>"/>
	<?php
	if ($user) { ?>
		<div class="avatar pull-left" style="margin: 0 10px;">
		<?php echo $user->getAvatar(); ?>
		</div>
	<?php
	}
	else { ?>
		<i class="fa fa-user  fa-4x fa-pull-left fa-border"></i>
	<?php
	}
	if ($thisstaff->hasPerm(User::PERM_CREATE)) { ?>
		<a class="btn btn-success action-button pull-right" style="overflow:inherit"
			id="unselect-user"  href="#"><i class="fa fa-plus"></i>
			<?php echo __('Add New User'); ?></a>
	<?php }
	if ($user) { ?>
		<div><label class="control-label" id="user-name"><?php echo Format::htmlchars($user->getName()->getOriginal()); ?></label></div>
		<div>&lt;<span id="user-email"><?php echo $user->getEmail(); ?></span>&gt;</div>
		<?php
		if ($org=$user->getOrganization()) { ?>
		<div><span id="user-org"><?php echo $org->getName(); ?></span></div>
		<?php
		} ?>
		<fieldset class="form-group">
	<?php foreach ($user->getDynamicData() as $entry) { ?>
		<label class="control-label"><?php
			 echo $entry->getTitle(); ?></label>
	<?php foreach ($entry->getAnswers() as $a) { ?>
		<div class="form-group row">
			<div class="col-md-3">
				<?php echo Format::htmlchars($a->getField()->get('label'));?>:
			 </div>
			<div class="col-md-9"><?php echo $a->display(); ?></div>
		</div>
	<?php }
	}
	?>
	</fieldset>
	<?php } ?>
		<div class="form-group" style="margin-top:20px;">
			<input type="submit" class="btn btn-success" value="<?php echo __('Continue'); ?>">
			<input type="button" name="cancel" class="close_me btn"  value="<?php
			echo __('Cancel'); ?>">
		</div>
	</form>
	</div>

	<div id="new-user-form" style="display:<?php echo $user ? 'none' :'block'; ?>;">
	<?php if ($thisstaff->hasPerm(User::PERM_CREATE)) { ?>
		<form method="post" class="user" action="<?php echo $info['action'] ?: '#users/lookup/form'; ?>">
			
			<?php
				if(!$form) $form = UserForm::getInstance();
				$form->render(true, __('Create New User')); ?>
			
			<div class="form-group">
				<input type="submit" class="btn btn-success" value="<?php echo __('Add User'); ?>">
				<input type="reset" class="btn" value="<?php echo __('Reset'); ?>">
				<input type="button" name="cancel" class="btn <?php echo $user ?  'cancel' : 'close_me' ?>"  value="<?php echo __('Cancel'); ?>">
			</div>
		</form>
	<?php }
	else { ?>
		<div class="form-group">
			<span class="buttons pull-left">
				<input type="button" name="cancel" class="<?php echo $user ?  'cancel' : 'close_me' ?>"  value="<?php echo __('Cancel'); ?>">
			</span>
		 </div>
	<?php } ?>
	</div>
	</div>
</div>
<script type="text/javascript">

$(document).ready(function (){
	$('.perfectScrollbar').perfectScrollbar();
});
	
$(function() {
    var last_req;
    $('#user-search').typeahead({
        source: function (typeahead, query) {
            if (last_req) last_req.abort();
            last_req = $.ajax({
                url: "ajax.php/users<?php
                    echo $info['lookup'] ? "/{$info['lookup']}" : '' ?>?q="+query,
                dataType: 'json',
                success: function (data) {
                    typeahead.process(data);
                }
            });
        },
        onselect: function (obj) {
            $('#the-lookup-form').load(
                '<?php echo isset($info['onselect'])? $info['onselect']: "ajax.php/users/select/"; ?>'+encodeURIComponent(obj.id)
            );
        },
        property: "/bin/true"
    });

    $('a#unselect-user').click( function(e) {
        e.preventDefault();
        $("#msg_error, #msg_notice, #msg_warning").fadeOut();
        $('div#selected-user-info').hide();
        $('div#new-user-form').fadeIn({start: function(){ $('#user-search').focus(); }});
        return false;
     });

    $(document).on('click', 'form.user input.cancel', function (e) {
        e.preventDefault();
        $('div#new-user-form').hide();
        $('div#selected-user-info').fadeIn({start: function(){ $('#user-search').focus(); }});
        return false;
     });
	 
	 // $(document).ready(function(){
		 // $('div#infomsg').hide();
		 // $('div#searchbox').hide();
	 // });
});
</script>
