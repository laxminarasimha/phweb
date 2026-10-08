<?php
if(!defined('DSKADMININC') || !$thisstaff || !is_object($org)) die('Invalid path');

?>
<div class="panel">
	<div class="panel-heading">
		<span class="panel-title table-caption"><a href="orgs.php?id=<?php echo $org->getId(); ?>"
		 title="Reload"><i class="fa fa-refresh text-primary"></i></a> &nbsp;<?php echo $org->getName(); ?></span>
		<div class="panel-heading-controls" style="margin-bottom:5px;">	 
			<?php if ($thisstaff->hasPerm(Organization::PERM_DELETE)) { ?>
				<a id="org-delete" class="btn btn-outline button action-button org-action"
				href="#orgs/<?php echo $org->getId(); ?>/delete"><i class="fa fa-trash"></i>
				<?php echo __('Delete Organization'); ?></a>
			<?php } ?>
			
			<?php if ($thisstaff->hasPerm(Organization::PERM_EDIT)) { ?>
			<div class="btn-group">
				<button class="action-button btn btn-outline dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
				   <i class="fa fa-caret-down"></i>
					<span ><i class="fa fa-cog"></i> <?php echo __('More'); ?></span>
				</button>
			<?php } ?>
				<ul class="dropdown-menu dropdown-menu-right">
					<?php if ($thisstaff->hasPerm(Organization::PERM_EDIT)) { ?>
					<li><a href="#ajax.php/orgs/<?php echo $org->getId();
						?>/forms/manage" onclick="javascript:
						$.dialog($(this).attr('href').substr(1), 201);
						return false"
						><i class="fa fa-clipboard"></i>
						<?php echo __('Manage Forms'); ?></a></li>
			<?php } ?>
				</ul>
			</div>
		</div>
	</div>	
	<div class="panel-body">
		<div class="row">
			<div class="col-md-6">
				<div class="form-group row">
					<div class="col-md-5">
						<label class="control-label"><?php echo __('Name'); ?>:</label>
					</div>
					<div class="col-md-7">
					<?php if ($thisstaff->hasPerm(Organization::PERM_EDIT)) { ?>
						<b><a href="#orgs/<?php echo $org->getId();
						?>/edit" class="org-action"><i class="fa fa-pencil-square-o"></i>
							<?php }
							echo $org->getName();
							if ($thisstaff->hasPerm(Organization::PERM_EDIT)) { ?>
						</a></b>
					<?php } ?>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-5"><label class="control-label"><?php echo __('Account Manager'); ?>:</label></div>
					<div class="col-md-7"><?php echo $org->getAccountManager(); ?>&nbsp;
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group row">
					<div class="col-md-5">
						<label class="control-label"><?php echo __('Created'); ?>:</label>
					</div>
					<div class="col-md-7">
						<?php echo Format::datetime($org->getCreateDate()); ?>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-5">
						<label class="control-label"><?php echo __('Last Updated'); ?>:</label>
					</div>
					<div class="col-md-7">
						<?php echo Format::datetime($org->getUpdateDate()); ?>
					</div>
				</div>
			</div>
		</div>
	<br>
	<ul class="nav nav-tabs" id="orgtabs">
		<li class="active"><a data-toggle="tab" href="#users"><i
		class="fa fa-user"></i>&nbsp;<?php echo __('Users'); ?></a></li>
		<li><a data-toggle="tab" href="#tickets"><i class="fa fa-list-alt"></i>&nbsp;<?php echo __('Tickets'); ?></a></li>
		<li><a data-toggle="tab" href="#notes"><i class="fa fa-thumb-tack"></i></i>&nbsp;<?php echo __('Notes'); ?></a></li>
	</ul>
	<div id="orgtabs_container" class="tab-content">
		<div class="tab-pane fade in active" id="users">
			<?php
				include STAFFINC_DIR . 'templates/users.tmpl.php';
			?>
		</div>
		<div class="tab-pane fade" id="tickets">
			<?php
				include STAFFINC_DIR . 'templates/tickets.tmpl.php';
			?>
		</div>

		<div class="tab-pane fade" id="notes">
			<?php
				$notes = QuickNote::forOrganization($org);
				$create_note_url = 'orgs/'.$org->getId().'/note';
				include STAFFINC_DIR . 'templates/notes.tmpl.php';
			?>
		</div>
	</div>
	</div>
</div>

<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.org-action', function(e) {
        e.preventDefault();
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.dialog(url, [201, 204], function (xhr) {
            if (xhr.status == 204)
                window.location.href = 'orgs.php';
            else
                window.location.href = window.location.href;
         }, {
            onshow: function() { $('#org-search').focus(); }
         });
        return false;
    });
});
</script>
