<?php

if (!$info['title'])
    $info['title'] = __('Organization Lookup');

$msg_info = __('Search existing organizations or add a new one.');
if ($info['search'] === false)
    $msg_info = __('Complete the form below to add a new organization.');

?>
<div class="panel panel-primary panel-dark m-b-0" id="the-lookup-form">
	<div class="panel-heading">
		<div class="drag-handle panel-title"><?php echo $info['title']; ?>
			<a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a>
		</div>
	</div>

	<div class="panel-body ps-block perfectScrollbar" style="height: 300px;position: relative;">

	<?php
	if ($info['search'] !== false) { ?>
		<div>
			<p id="msg_info"><i class="fa fa-info-circle"></i>&nbsp; <?php echo $msg_info; ?></p>
		</div>
		<div style="margin-bottom:10px;">
			<input class="form-control" type="text" class="search-input" style="width:100%;" placeholder="Search by name" id="org-search" autofocus autocorrect="off" autocomplete="off"/>
		</div>

		<?php
		}
		if ($info['error']) {
			echo sprintf('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['error']);
		} elseif ($info['warning']) {
			echo sprintf('<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['warning']);
		} elseif ($info['msg']) {
			echo sprintf('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;%s</div>', $info['msg']);
		} ?>

	<div id="selected-org-info" style="display:<?php echo $org ? 'block' :'none'; ?>;margin:5px;">
		<form method="post" class="org" action="<?php echo $info['action'] ?: '#orgs/lookup'; ?>">
			<input type="hidden" id="org-id" name="orgid" value="<?php echo $org ? $org->getId() : 0; ?>"/>
			<i class="fa fa-users fa-4x fa-pull-left fa-border"></i>
			<a class="btn btn-success action-button pull-right" style="overflow:inherit"
				id="unselect-org"  href="#"><i class="fa fa-remove"></i>
				<?php echo __('Add New Organization'); ?></a>
			<fieldset class="form-group">	
				<label class="control-label" id="org-name">
					<?php echo $org ?  Format::htmlchars($org->getName()) : ''; ?>
				</label>
		<?php if ($org) { ?>
		<?php foreach ($org->getDynamicData() as $entry) { ?>
				<div class="form-group">
					<label class="control-label"><?php
					 echo $entry->getForm()->getTitle(); ?></label>
				</div>	 
		<?php foreach ($entry->getAnswers() as $a) { ?>
				<div class="form-group">
					<div class="col-md-3">
						<?php echo Format::htmlchars($a->getField()->get('label'));?>:
					 </div>
					<div class="col-md-9">
						<?php echo $a->display(); ?>
					</div>
				</div>
		<?php }
			} ?>
		   </fieldset>
		 <?php
		  } ?>
			<div class="form-group" style="margin-top:20px;">
				<input type="submit" class="btn btn-success" value="<?php echo __('Continue'); ?>">
				<input type="button" name="cancel" class="btn close_me"  value="<?php echo __('Cancel'); ?>">
			 </div>
		</form>
	</div>

		<div id="new-org-form" style="display:<?php echo $org ? 'none' :'block'; ?>;">
			<form method="post" class="org" action="<?php echo $info['action'] ?: '#orgs/add'; ?>">
				
				<?php
					if (!$form) $form = OrganizationForm::getInstance();
					$form->render(true, __('Create New Organization')); ?>
				
				<div class="panel-body">
					<input type="submit" class="btn btn-success" value="<?php echo __('Add Organization'); ?>">
					<input type="reset" class="btn" value="<?php echo __('Reset'); ?>">
					<input type="button" name="cancel" class="<?php echo $org ? 'cancel' : 'close_me' ?> btn"
							value="<?php echo __('Cancel'); ?>">
				</div>
			</form>	
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function (){
	$('.perfectScrollbar').perfectScrollbar();
});


$(function() {
    var last_req;
    $('#org-search').typeahead({
        source: function (typeahead, query) {
            if (last_req) last_req.abort();
            last_req = $.ajax({
                url: "ajax.php/orgs/search?q="+query,
                dataType: 'json',
                success: function (data) {
                    typeahead.process(data);
                }
            });
        },
        onselect: function (obj) {
            $('#the-lookup-form').load(
                '<?php echo $info['onselect'] ?: 'ajax.php/orgs/select'; ?>/'+encodeURIComponent(obj.id)
            );
        },
        property: "/bin/true"
    });

    $('a#unselect-org').click( function(e) {
        e.preventDefault();
        $('div#selected-org-info').hide();
        $('div#new-org-form').fadeIn({start: function(){ $('#org-search').focus(); }});
        return false;
     });

    $(document).on('click', 'form.org input.cancel', function (e) {
        e.preventDefault();
        $('div#new-org-form').hide();
        $('div#selected-org-info').fadeIn({start: function(){ $('#org-search').focus(); }});
        return false;
     });
});
</script>

