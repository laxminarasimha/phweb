<?php
if (!defined('DSKADMININC')
        || !$ticket
        || !($ticket->checkStaffPerm($thisstaff, TicketModel::PERM_EDIT)))
    die('Access Denied');

$info=Format::htmlchars(($errors && $_POST)?$_POST:$ticket->getUpdateInfo());
if ($_POST)
    // Reformat duedate to the display standard (but don't convert to local
    // timezone)
    $info['duedate'] = Format::date(strtotime($info['duedate']), false, false, 'UTC');
?>
<div class="panel">
	<form action="tickets.php?id=<?php echo $ticket->getId(); ?>&a=edit" method="post" class="save"  enctype="multipart/form-data">
		<?php csrf_token(); ?>
		<input type="hidden" name="do" value="update">
		<input type="hidden" name="a" value="edit">
		<input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
		<div style="margin-bottom:20px; padding-top:5px;">
			<div class="panel-heading">
				<div class="panel-title table-caption"><?php echo sprintf(__('Update Ticket #%s'),$ticket->getNumber());?></div>
			</div>
		</div>
		<div class="panel-body">
			<div class="panel-heading"><div class="panel-title table-caption"><?php echo __('User Information'); ?>&nbsp;: <small class="text-muted"><em><?php echo __('Currently selected user'); ?></small></em></div></div> 
			<?php
				if(!$info['user_id'] || !($user = User::lookup($info['user_id'])))
				$user = $ticket->getUser();
			?>
			<div class="panel-body">
				<div class="col-md-2"><label class="control-label"><?php echo __('User'); ?>:</label></div>
				<div class="col-md-10">
					<div id="client-info">
						<a href="#" onclick="javascript:
							$.userLookup('ajax.php/users/<?php echo $ticket->getOwnerId(); ?>/edit',
									function (user) {
										$('#client-name').text(user.name);
										$('#client-email').text(user.email);
									});
							return false;
							"><i class="fa fa-user"></i>
						<span id="client-name"><?php echo Format::htmlchars($user->getName()); ?></span>
						&lt;<span id="client-email"><?php echo $user->getEmail(); ?></span>&gt;
						</a>
						<a class="inline btn action-button" style="overflow:inherit" href="#"
							onclick="javascript:
								$.userLookup('ajax.php/tickets/<?php echo $ticket->getId(); ?>/change-user',
										function(user) {
											$('input#user_id').val(user.id);
											$('#client-name').text(user.name);
											$('#client-email').text('<'+user.email+'>');
								});
								return false;
							"><i class="fa fa-pencil-square-o"></i> <?php echo __('Change'); ?></a>
						<input type="hidden" name="user_id" id="user_id"
							value="<?php echo $info['user_id']; ?>" />
					</div>
				</div>
			</div>
			   <div class="panel-heading"><div class="panel-title table-caption"> <?php echo __('Ticket Information'); ?> :<small class="text-muted"><em> <?php echo __("Due date overrides SLA's grace period."); ?></small></em></div></div>
			   <div class="panel-body">
				<div class="form-group form-inline row">
					<div class="col-md-3">
						<label class="control-label required"><?php echo __('Ticket Source');?>:</label>
					</div>
					<div class="col-md-9">
						<select class="form-control" name="source">
							<option value="" selected >&mdash; <?php
								echo __('Select Source');?> &mdash;</option>
							<?php
							$source = $info['source'] ?: 'Phone';
							foreach (Ticket::getSources() as $k => $v) {
								echo sprintf('<option value="%s" %s>%s</option>',
										$k,
										($source == $k ) ? 'selected="selected"' : '',
										$v);
							}
							?>
						</select>
						<?php
							if(!empty($errors['source'])){
								echo '<div class="alert-danger">&nbsp;'.$errors['source'].'</div>';
							}
						?>
					</div>
				</div>
        <div class="form-group form-inline row">
            <div class="col-md-3">
		    <label class="control-label required"><?php echo __('Department');?>:</label>
		    </div>
	            <div class="col-md-9">
	                <select class="form-control" name="deptId">
	                    <option value="" selected >&mdash; <?php echo __('Select Department');?> &mdash;</option>
	                    <?php
				if($depts=Dept::getDepartments(array('dept_id' => $thisstaff->getDepts()))) {
					foreach($depts as $id =>$name) {
						if (!($role = $thisstaff->getRole($id))
							|| !$role->hasPerm(Ticket::PERM_CREATE)
						) {
							// No access to create tickets in this dept
							continue;
						}
						echo sprintf('<option value="%d" %s>%s</option>',
								$id, ($info['deptId']==$id)?'selected="selected"':'',$name);
					}
				}
	                    ?>
	                </select>
			<?php
				if(!empty($errors['deptId'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['deptId'].'</div>';
				}
			?>
	            </div>
	        </div>
	        <div class="form-group form-inline row">
            <div class="col-md-3">
				<label class="control-label"><?php echo __('SLA Plan');?>:</label>
			</div>
            <div class="col-md-9">
                <select class="form-control" name="slaId">
                    <option value="0" selected="selected" >&mdash; <?php echo __('None');?> &mdash;</option>
                    <?php
                    if($slas=SLA::getSLAs()) {
                        foreach($slas as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['slaId']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
				<?php
					if(!empty($errors['slaId'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['slaId'].'</div>';
					}
				?>
            </div>
        </div>
        <div class="form-group form-inline row">
            <div class="col-md-3">
				<label class="control-label"><?php echo __('Due Date');?>:</label>
			</div>
            <div class="col-md-9">
                <input class="dp form-control" id="duedate" name="duedate" value="<?php echo Format::htmlchars($info['duedate']); ?>" size="12" autocomplete=OFF>
				<?php
					if(!empty($errors['duedate'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['duedate'].'</div>';
					}
				?>
				<?php
					if(!empty($errors['time'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['time'].'</div>';
					}
				?>
                &nbsp;&nbsp;
                <?php
                $min=$hr=null;
                if($info['time'])
                    list($hr, $min)=explode(':', $info['time']);

                echo Misc::timeDropdown($hr, $min, 'time');
                ?>
                <?php echo __('Time is based on your time zone');?>
                    (<?php echo $cfg->getTimezone($thisstaff); ?>)
            </div>
        </div>
	</div>
		<div class="form-group">
				<?php if ($forms)
					foreach ($forms as $form) {
						$form->render(true, false, array('mode'=>'edit','width'=>160,'entry'=>$form));
				} ?>
		</div>
			<div class="panel-heading"><div class="panel-title table-caption"><?php echo __('Internal Note');?>&nbsp;: <small class="text-muted"><em><?php echo __('Reason for editing the ticket (required)');?> </small></em></div></div>
			<?php
			if(!empty($errors['note'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['note'].'</div>';
				}
			?>
			<div class="panel-body"> 
				<textarea id="updatetickeditor" class="summernote-base no-bar form-control" name="note" cols="21" rows="6" style="width:80%;"><?php echo $info['note'];?></textarea>
			</div>
		<div class="form-group" style="text-align:center;">
			<input type="submit" class="btn btn-success" name="submit" value="<?php echo __('Save');?>">
			<input type="reset" class="btn btn-info" name="reset"  value="<?php echo __('Reset');?>">
			<input type="button" class="btn" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="tickets.php?id=<?php echo $ticket->getId(); ?>"'>
		</div>
	</form>
		<div style="display:none;" class="dialog draggable" id="user-lookup">
			<div class="body"></div>
		</div>
	</div>
</div>
<script type="text/javascript">
+(function() {
  var I = setInterval(function() {
    if (!$.fn.sortable)
      return;
    clearInterval(I);
    $('table.dynamic-forms').sortable({
      items: 'tbody',
      handle: 'th',
      helper: function(e, ui) {
        ui.children().each(function() {
          $(this).children().each(function() {
            $(this).width($(this).width());
          });
        });
        ui=ui.clone().css({'background-color':'white', 'opacity':0.8});
        return ui;
      }
    });
  }, 20);
})();

</script>
