<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$matches=Filter::getSupportedMatches();
$match_types=Filter::getSupportedMatchTypes();

$info = $qs = array();
if($filter && $_REQUEST['a']!='add'){
    $title=__('Update Filter');
    $action='update';
    $submit_text=__('Save Changes');
    $info=array_merge($filter->getInfo());
    $info['id']=$filter->getId();
    $info['rules'] = $filter->getRules();
    $qs += array('id' => $filter->getId());
}else {
    $title=__('Add New Filter');
    $action='add';
    $submit_text=__('Add Filter');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['rules'] = array();
    $qs += array('a' => $_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="panel">
<form action="filters.php?<?php echo Http::build_query($qs); ?>" method="post" class="save form-inline">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo esc_attr($action); ?>">
<input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
<input type="hidden" name="id" value="<?php echo esc_attr($info['id']); ?>">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo esc_html($title); ?>
	— <small class="text-muted"><em><?php echo __('Filters are executed based on execution order. Filter can target specific ticket source.');?></em></small>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="name" class="required"><?php echo __('Filter Name');?>: </label><br />
		<input type="text" size="40" class="form-control" name="name" id="name" value="<?php echo esc_attr($info['name']); ?>"
			autofocus>
		<?php
		if(!empty($errors['name'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['name'].'</div>';
		}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label for="isactive" class="required" style="display:block;"><?php echo __('Filter Status');?>: 
			</label>
			<input type="radio" name="isactive" id="isactive" value="1" <?php echo
			$info['isactive']?'checked="checked"':''; ?>> <?php echo __('Active'); ?>&nbsp;&nbsp;
			<input type="radio" name="isactive" id="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>
			> <?php echo __('Disabled'); ?>
		</div>
	</div>
	<br />
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="target" class="required" style="display:block;"><?php echo __('Target Channel');?>:  &nbsp;
			<i class="help-tip fa fa-question-circle" href="#target_channel"></i>
			</label>
			<select name="target" id="target" class="form-control">
			   <option value="">&mdash; <?php echo __('Select a Channel');?> &mdash;</option>
				<?php
				foreach(Filter::getTargets() as $k => $v) {
					echo sprintf('<option value="%s" %s>%s</option>',
						   $k, (($k==$info['target'])?'selected="selected"':''), $v);
				}
				$sql='SELECT email_id,email,name FROM '.EMAIL_TABLE.' email ORDER by name';
				if(($res=db_query($sql)) && db_num_rows($res)) {
					echo sprintf('<OPTGROUP label="%s">', __('System Emails'));
					while(list($id,$email,$name)=db_fetch_row($res)) {
						$selected=($info['email_id'] && $id==$info['email_id'])?'selected="selected"':'';
						if($name)
							$email=Format::htmlchars("$name <$email>");
						echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$email);
					}
					echo '</OPTGROUP>';
				}
				?>
			</select>
		<?php
		if(!empty($errors['target'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['target'].'</div>';
		}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label for="execorder" class="required" style="display:block;"><?php echo __('Execution Order');?>:
			&nbsp;<i class="help-tip fa fa-question-circle" href="#execution_order"></i>
			</label>
			<input type="text" size="6" class="form-control" name="execorder" id="execorder" value="<?php echo esc_attr($info['execorder']); ?>">
			<em>(1...99)</em>
			&nbsp;
			<label class="checkbox" for="stop_onmatch">
			<input type="checkbox" name="stop_onmatch" id="stop_onmatch" class="" value="1" <?php echo $info['stop_onmatch']?'checked="checked"':''; ?> >&nbsp;
			<?php echo __('<strong>Stop</strong> processing further on match!');?>
			</label>
		<?php
		if(!empty($errors['execorder'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['execorder'].'</div>';
		}
			?>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption"><i class="fa fa-filter"></i> <?php echo __('Filter Rules'); ?> — <small class="text-muted"><em><?php
		echo __('Rules are applied based on the criteria.');?></em></small>
	</div>
</div>
<?php
if(!empty($errors['rules'])){
	echo '<div class="alert-danger">&nbsp;'.$errors['rules'].'</div>';
}
?>

<!-- ====================== FILTER RULES ========================== -->
<div class="panel-body">
	<div id="rules">
	<div class="row">
		<div class="col-sm-12 form-group">
			<label for="match_all_rules" class="required" style="display:inline;"><?php echo __('Rules Matching Criteria');?>  : 
			</label>
			<input type="radio" name="match_all_rules" id="match_all_rules" value="1" <?php echo $info['match_all_rules']?'checked="checked"':''; ?>>
                            <?php echo __('Match All');?>
			<input type="radio" name="match_all_rules" id="match_all_rules" value="0" <?php echo !$info['match_all_rules']?'checked="checked"':''; ?>>
                            <?php echo __('Match Any');?>
			&nbsp;
			<em>(<?php echo __('case-insensitive comparison');?>)</em>&nbsp;<i class="help-tip fa fa-question-circle" href="#rules_matching_criteria"></i>
		</div>
	</div>
	<?php
	foreach ($info['rules'] as $i=>$rule) { ?>
		<div><br />
		<div class="form-group">
			<select style="" class="form-control" name="rules[<?php echo esc_attr($i); ?>][w]">
			<option value="">&mdash; <?php echo __('Select Data for Matching');?> &mdash;</option>
				<?php
				foreach ($matches as $group=>$ms) { ?>
					<optgroup label="<?php echo __($group); ?>"><?php
					foreach ($ms as $k=>$v) {
						$sel=($rule["w"]==$k)?'selected="selected"':'';
						echo sprintf('<option value="%s" %s>%s</option>',
							$k,$sel,__($v));
					} ?>
				</optgroup>
				<?php } ?>
			</select>
			&nbsp;
		</div>
		<div class="form-group">
			<select class="form-control" name="rules[<?php echo esc_attr($i); ?>][h]">
				<option value="0">&mdash; <?php echo __('Select Comparison Operator');?> &mdash;</option>
				<?php
					foreach($match_types as $k=>$v){
					$sel=($rule["h"]==$k)?'selected="selected"':'';
					echo sprintf('<option value="%s" %s>%s</option>',
						$k,$sel,$v);
				}
				?>
			</select>
			&nbsp;
		</div>
		<div class="form-group">
		<input type="text" class="form-control" size="30" name="rules[<?php echo esc_attr($i); ?>][v]" value="<?php echo esc_attr($rule["v"]); ?>">
		<?php
		if(!empty($errors["rule_$i"])){
				echo '<div class="alert-danger">&nbsp;'.$errors["rule_$i"].'</div>';
		}
			?>
		</div>
		<div class="form-group"><a data-type="warning" data-message='You must click "<?php echo $submit_text; ?>" to apply the new changes' href="#" class="clearrule growl_popup"	onclick="javascript: $(this).parent('div').parent('div').remove();">&nbsp;<i class="fa fa-trash text-danger"></i></a>
		</div>
		</div>
<?php    $maxi = max($maxi ?: 0, $i+1);
	} ?>
	
	<div class="hidden" id="new-rule-template">
		<div><br />
		<div class="form-group">
			<select class="form-control" data-name="rulew">
				<option value="">&mdash; <?php echo __('Select Data for Matching');?> &mdash;</option>
				<?php
				foreach ($matches as $group=>$ms) { ?>
					<optgroup label="<?php echo __($group); ?>"><?php
					foreach ($ms as $k=>$v) {
						echo sprintf('<option value="%s">%s</option>',
							$k,__($v));
					} ?>
				</optgroup>
				<?php } ?>
			</select>
		</div>
		<div class="form-group">
			&nbsp;
			<select class="form-control" data-name="ruleh">
				<option value="0">&mdash; <?php echo __('Select Comparison Operator');?> &mdash;</option>
				<?php
					foreach($match_types as $k=>$v){
					echo sprintf('<option value="%s">%s</option>',
						$k,$v);
				}
				?>
			</select>&nbsp;
		</div>
		<div class="form-group">
			<input type="text" class="form-control" size="30" data-name="rulev">
		</div>
		<div class="form-group"><a data-type="warning" data-message='You must click "<?php echo $submit_text; ?>" to apply the new changes' href="#" class="clearrule growl_popup" onclick="javascript: $(this).parent('div').parent('div').remove();">&nbsp;<i class="fa fa-trash text-danger"></i></a>
		</div>
		</div>
	</div>
	</div>
	<br />
	<div>
		<button class="btn btn-success btn-outline" type="button" id="add-rule">
			<i class="fa fa-plus-circle"></i> <?php echo __('Add Rule'); ?>
		</button>
	</div>
</div>

<!-- ======================= FILTER ACTIONS ========================= -->

<div class="panel-heading">
	<div class="panel-title table-caption"><i class="fa fa-bolt"></i> <?php echo __('Filter Actions'); ?> — 
		<small class="text-muted">
		<em>
		<?php
		echo __('Actions are executed in the order declared below and can be overridden by other filters depending on processing order'); ?>
		</em>
		</small>
	</div>
</div>

<div class="panel-body">
	<div class="row">
	<div class="col-md-12">
			<div id="dynamic-actions" class="sortable-rows">
				<?php
				$existing = array();
				if ($filter) { foreach ($filter->getActions() as $A) {
					$existing[] = $A->type;
				?>
				<div style="background-color:white;">
				<div class="col-md-12 sortable-rows ui-sortable" style="border:1px solid #D3D3D3; padding-top:5px;padding-bottom:5px;">
					<div class="col-md-3 form-group"><i class="fa fa-sort fa-lg"></i>&nbsp;
					<?php echo esc_html($A->getImpl()->getName()); ?></div>
					<div class="col-md-9 form-group">
						<div style="position:relative"><?php
						$form = $A->getImpl()->getConfigurationForm($_POST ?: false);
						// XXX: Drop this when the ORM supports proper caching
						$form->isValid();
						include STAFFINC_DIR . 'templates/dynamic-form-simple.tmpl.php';
						?>
						<input type="hidden" name="actions[]" value="I<?php echo $A->getId(); ?>"/>
						<div class="pull-right" style="position:absolute;top:2px;right:2px;">
							<a class="growl_popup" data-type="warning" data-message='You must click "<?php echo $submit_text; ?>" to apply the new changes' href="#" title="<?php echo __('clear'); ?>" onclick="javascript:	if (!confirm(__('You sure?')))	return false;	$(this).closest('div.col-md-12').fadeOut(400, function() { $(this).hide(); });return false;"><i class="fa fa-trash text-danger"></i></a>
						</div>
						</div>
					</div>
					</div>
				</div>
				<?php } } ?>
			</div>
		</div>
	</div>
	<div style="padding: 5px">
		<i class="fa fa-plus-circle"></i>
		<select class="form-control" name="new-action" id="new-action-select"
				onchange="javascript: $('#new-action-btn').trigger('click');">
			<option value="">— <?php echo __('Select New Action'); ?> —</option>
			<?php
			$current_group = '';
			foreach (FilterAction::allRegistered() as $group=>$actions) {
				if ($group && $current_group != $group) {
					if ($current_group) echo '</optgroup>';
					$current_group = $group;
					?><optgroup label="<?php echo Format::htmlchars($group); ?>"><?php
				}
				foreach ($actions as $type=>$name) {
			?>
		<option data-title="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($type); ?>"
				data-multi-use="<?php echo $mu = FilterAction::lookupByType($type)->hasFlag(TriggerAction::FLAG_MULTI_USE); ?> " <?php
				if (in_array($type, $existing) && !$mu) echo 'disabled="disabled"';
				?>><?php echo esc_html($name); ?></option>
			<?php }
			} ?>
		</select>
		<button id="new-action-btn" type="button" class="btn btn-success button" style="vertical-align: top;" onclick="javascript:
			var dropdown = $('#new-action-select'), selected = dropdown.find(':selected');
			dropdown.val('');
			$('#dynamic-actions')
			  .append($('<div></div>')
			  .append($('<div></div>').addClass('col-md-12 sortable-rows ui-sortable').css({'border':'1px solid #D3D3D3','padding-top':'5px','padding-bottom':'5px'})
				.append($('<div></div>').addClass('col-md-3')
				  .text(selected.data('title'))
				  .prepend('<i class=\'fa fa-sort fa-lg \'>&nbsp;')
				).append($('<div></div>').addClass('col-md-9')
				  .append($('<em></em>').text(__('Loading ...')))
				  .load('ajax.php/filter/action/' + selected.val() + '/config?warnbtn=<?php echo rawurlencode($submit_text); ?>', function() {
					if (!selected.data('multiUse')) selected.prop('disabled', true);
				  })
				))
			  ).append(
				$('<input>').attr({type:'hidden',name:'actions[]',value:'N'+selected.val()})
			  );"><?php echo __('Add'); ?>
		</button>
	</div>
</div>

<!-- ======================== INTERNAL NOTES ======================== -->

<div class="panel-heading">
	<a class="internal_note">
		<div class="panel-title table-caption"><i class="fa fa-plus-square"></i>&nbsp;<?php echo __('Internal Notes'); ?>: <small class="text-muted"><em><?php echo __('Be liberal, they\'re internal'); ?></em></small></div>
	</a>
</div>

<div class="panel-body notes" style="display:none;">
	<div class="form-group">
	<textarea class="summernote-base no-bar" name="notes" cols="21"
			rows="8" style="width: 80%;"><?php echo $info['notes']; ?></textarea>
	</div>
</div>
<p class="text-center" style="margin-top:20px;">
	<input type="submit" name="submit" class="btn btn-success" value="<?php echo $submit_text; ?>">
	<input type="reset"  name="reset"  class="btn btn-info" value="<?php echo __('Reset');?>">
	<input type="button" name="cancel" class="btn btn-default" value="<?php echo __('Cancel');?>" onclick='window.location.href="filters.php"'>
</p>
<br/>
</form>
</div>
<script type="text/javascript">
   var fixHelper = function(e, ui) {
      ui.children().each(function() {
          $(this).width($(this).width());
      });
      return ui;
   };
   $(function() {
     $('#dynamic-actions').sortable({helper: fixHelper, opacity: 0.5});
     var next = <?php echo $maxi ?: 0; ?>;
     $('#add-rule').click(function() {
       var clone = $('#new-rule-template>div').clone();
       clone.find('[data-name=rulew]').attr('name', 'rules['+next+'][w]');
       clone.find('[data-name=ruleh]').attr('name', 'rules['+next+'][h]');
       clone.find('[data-name=rulev]').attr('name', 'rules['+next+'][v]');
       clone.appendTo('#rules');
       next++;
     });
<?php if (!$info['rules']) { ?>
        $('#add-rule').trigger('click');
<?php } ?>
   });
</script>
