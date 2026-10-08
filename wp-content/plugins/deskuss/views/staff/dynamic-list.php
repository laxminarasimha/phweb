<?php

$info=array();
if ($list) {
    $title = __('Update custom list');
    $action = 'update';
    $submit_text = __('Save Changes');
    $info = $list->getInfo();
    $trans['name'] = $list->getTranslateTag('name');
    $trans['plural'] = $list->getTranslateTag('plural');
    $newcount=2;
} else {
    $title = __('Add New Custom List');
    $action = 'add';
    $submit_text = __('Add List');
    $newcount=4;
}

$info=Format::htmlchars(($errors && $_POST) ? array_merge($info,$_POST) : $info);

?>
<div class="panel">
<form action="" method="post" class="save">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo esc_attr($action); ?>">
<input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
<input type="hidden" name="id" value="<?php echo esc_attr($info['id']); ?>">

<div class="panel-heading">
	<div class="panel-title"><?php echo esc_html($title); ?> —
		<small class="text-muted">
			<em>
				<?php echo __(
				'Custom lists are used to provide drop-down lists for custom forms.'
				); ?>&nbsp;<i class="help-tip fa fa-question-circle" href="#custom_lists"></i>
			</em>
		</small>
	</div>
</div>

<div class="panel-body">
	<div class="row form-inline">
		<div class="col-sm-4 form-group" >
			<label class="required"><?php echo __('Name'); ?>: </label>
		 
			<?php
					if ($list && !$list->isEditable())
						echo esc_html($list->getName());
					else {
						echo sprintf('<input size="25" type="text" class="form-control" name="name"
								data-translate-tag="%s" autofocus
								value="%s"/> ',
								$trans['name'], esc_attr($info['name']));

						if(!empty($errors['name'])){
							echo '<div class="alert-danger">&nbsp;'.esc_html($errors['name']).'</div>';
						}
					}
			 ?>
		</div>	
		<div class="col-sm-4 form-group" >
			<label><?php echo __('Plural Name'); ?>: </label>
		 
			<?php
				if ($list && !$list->isEditable())
					echo esc_html($list->getPluralName());
				else
					echo sprintf('<input size="25" type="text"
							data-translate-tag="%s"
							name="name_plural" class="form-control" value="%s"/>',
							$trans['plural'], esc_attr($info['name_plural']));
			   ?>
		</div>	
		<div class="col-sm-4 form-group" >
			<label><?php echo __('Sort Order'); ?>: </label>
			<select name="sort_mode" class="form-control">
				<?php
				$sortModes = $list ? $list->getSortModes() : DynamicList::getSortModes();
				foreach ($sortModes as $key=>$desc) { ?>
				<option value="<?php echo esc_attr($key); ?>" <?php
				if ($key == $info['sort_mode']) echo 'selected="selected"';
				?>><?php echo esc_html($desc); ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
	<br />
	<ul class="nav nav-tabs" id="list-tabs">
	<?php if ($list) { ?>
		<li class="active"><a href="#items" data-toggle="tab" aria-expanded="true">
			<i class="fa fa-list"></i> <?php echo sprintf(__('Items (%d)'), $list->getItems()->count()); ?></a></li>
	<?php } ?>
			<li <?php if (empty($list)) echo 'class="active"'; ?>><a href="#properties" data-toggle="tab" aria-expanded="<?php echo empty($list) ? 'true' : 'false' ?>">
			<i class="fa fa-asterisk"></i> <?php echo __('Properties'); ?></a></li>
	</ul>

	<div class="tab-content tab-content-bordered">
		<div id="properties" class="tab-pane fade<?php if (empty($list)) echo ' active in'; ?>">
			<div class="row">
				<div class="col-md-12">
					<p><em><strong><?php echo __('Item Properties'); ?></strong>:
					<?php echo __('Properties definable for each item'); ?></em></p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="table-light table-responsive">
					<table class="table table-bordered form_table" width="940" border="0" cellspacing="0" cellpadding="2">
					<thead>
					<tr>
						<th nowrap></th>
						<th nowrap><?php echo __('Label'); ?></th>
						<th nowrap><?php echo __('Type'); ?></th>
						<th nowrap><?php echo __('Visibility'); ?></th>
						<th nowrap><?php echo __('Variable'); ?></th>
						<th nowrap><?php echo __('Delete'); ?></th>
					</tr>
					</thead>
					<tbody class="sortable-rows" data-sort="prop-sort-">
					<?php if ($list && $form=$list->getForm()) foreach ($form->getDynamicFields() as $f) {
					$id = $f->get('id');
					$deletable = !$f->isDeletable() ? 'disabled="disabled"' : '';
					$force_name = $f->isNameForced() ? 'disabled="disabled"' : '';
					$fi = $f->getImpl();
					$ferrors = $f->errors(); ?>
					<tr>
					<td><i class="fa fa-sort"></i></td>
					<td><input type="text" class="form-control" size="32" name="prop-label-<?php echo esc_attr($id); ?>"
						data-translate-tag="<?php echo esc_attr($f->getTranslateTag('label')); ?>"
						value="<?php echo Format::htmlchars($f->get('label')); ?>"/>
						<?php
						if(!empty($ferrors['label'])){
							echo '<div class="alert-danger">&nbsp;'.esc_html($ferrors['label']).'</div>';
						}
						?>
					</td>
				<td nowrap><select style="max-width:150px;display:inline-block" class="form-control" name="type-<?php echo esc_attr($id); ?>" <?php
					if (!$fi->isChangeable() || !$f->isChangeable()) echo 'disabled="disabled"'; ?>>
						<?php foreach (FormField::allTypes() as $group=>$types) {
								?><optgroup label="<?php echo Format::htmlchars(__($group)); ?>"><?php
								foreach ($types as $type=>$nfo) {
									if ($f->get('type') != $type
											&& isset($nfo[2]) && !$nfo[2]) continue; ?>
					<option value="<?php echo esc_attr($type); ?>" <?php
						if ($f->get('type') == $type) echo 'selected="selected"'; ?>>
							<?php echo __($nfo[0]); ?></option>
							<?php } ?>
						</optgroup>
						<?php } ?>
					</select>
					<?php if ($f->isConfigurable()) { ?>
						<a class="action-button field-config"
							style="overflow:inherit"
							href="#form/field-config/<?php echo esc_attr($f->get('id')); ?>">(<i
								class="fa fa-cog"></i> <?php echo __('Config'); ?>)</a> <?php } ?></td>
					<td>
						<?php echo esc_html($f->getVisibilityDescription()); ?></td>
					<td>
						<input type="text" class="form-control" size="20" name="name-<?php echo esc_attr($id); ?>"
							value="<?php echo Format::htmlchars($f->get('name'));
							?>" <?php echo $force_name ?>/>
						<?php
						if(!empty($ferrors['name'])){
							echo '<div class="alert-danger">&nbsp;'.esc_html($ferrors['name']).'</div>';
						}
						?>
						</td>
					<td>
						<?php
						if (!$f->isDeletable())
							echo '<i class="fa fa-ban-circle"></i>';
					else
						echo sprintf('<input type="checkbox" name="delete-prop-%s">', esc_attr($id));
					?>
						<input type="hidden" name="prop-sort-<?php echo esc_attr($id); ?>"
							value="<?php echo esc_attr($f->get('sort')); ?>"/>
						</td>
					</tr>
					<?php
					}
					for ($i=0; $i<$newcount; $i++) { ?>
				<td><em>+</em>
					<input type="hidden" name="prop-sort-new-<?php echo esc_attr($i); ?>"
						value="<?php echo esc_attr($info["prop-sort-new-$i"]); ?>"/></td>
				<td><input type="text" class="form-control" size="32" name="prop-label-new-<?php echo esc_attr($i); ?>"
					value="<?php echo esc_attr($info["prop-label-new-$i"]); ?>"/></td>
				<td><select style="max-width:150px" class="form-control" name="type-new-<?php echo esc_attr($i); ?>">
						<?php foreach (FormField::allTypes() as $group=>$types) {
							?><optgroup label="<?php echo Format::htmlchars(__($group)); ?>"><?php
							foreach ($types as $type=>$nfo) {
								if (isset($nfo[2]) && !$nfo[2]) continue; ?>
					<option value="<?php echo esc_attr($type); ?>"
						<?php if ($info["type-new-$i"] == $type) echo 'selected="selected"'; ?>>
							<?php echo __($nfo[0]); ?>
						</option>
							<?php } ?>
						</optgroup>
						<?php } ?>
					</select></td>
					<td></td>
				<td><input type="text" size="20" class="form-control" name="name-new-<?php echo esc_attr($i); ?>"
					value="<?php echo esc_attr($info["name-new-$i"]); ?>"/>
					<?php
					if(!empty($errors["new-$i"]['name'])){
							echo '<div class="alert-danger">&nbsp;'.$errors["new-$i"]['name'].'</div>';
					}
					?>
					<td></td>
					</tr>
					<?php } ?>
					</tbody>
					</table>
					</div>
				</div>
			</div>
		</div>

		<?php if ($list) { ?>
			<div id="items" class="tab-pane fade active in">
				<?php
				$pjax_container = '#items';
				include STAFFINC_DIR . 'templates/list-items.tmpl.php'; ?>
			</div>
		<?php } ?>
	</div>
	<br />
	<div class="row">
		<div class="col-sm-12 form-group" >
			<div class="panel-heading">
				<a class="internal_note"><div class="panel-title table-caption"><i class="fa fa-plus-square"></i>&nbsp;<strong><?php echo __('Internal Notes'); ?>&nbsp;:&nbsp;</strong><small class="text-muted"><em><?php echo __("Be liberal, they're internal"); ?></em></small>
				</div></a>
			</div>
			<div class="panel-body notes" style="display:none;">
			<textarea name="notes" class="summernote-base no-bar" rows="6" cols="80">
			<?php echo $info['notes'];?></textarea>
			</div>
		</div>
	</div>

	<p class="text-center">
		<input type="submit" class="btn btn-success" name="submit" value="<?php echo esc_attr($submit_text); ?>">
		<input type="reset"  class="btn btn-info" name="reset"  value="<?php echo __('Reset'); ?>">
		<input type="button" class="btn btn-default" name="cancel" value="<?php echo __('Cancel'); ?>"
			onclick='window.location.href="?"'>
	</p>
	<br/>
</div>
</form>
</div>

<script type="text/javascript">
$(function() {
    $('#properties, #items').on('click', 'a.field-config', function(e) {
        e.preventDefault();
        var $id = $(this).attr('id');
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.dialog(url, [201], function (xhr, resp) {
          var json = $.parseJSON(resp);
          if (json && json.success) {
            if (json.row) {
              if (json.id)
                $('#list-item-' + json.id).replaceWith(json.row);
              else
                $('#list-items').append(json.row);
            }
          }
        });
        return false;
    });
    $('#items').on('click', 'a.items-action', function(e) {
        e.preventDefault();
        var ids = [];
        $('form.save :checkbox.mass:checked').each(function() {
            ids.push($(this).val());
        });
		if(!ids.length){
			alert('Please select atleast 1 Item');
		}
        if (ids.length && confirm(__('You sure?'))) {
            $.ajax({
              url: 'ajax.php/' + $(this).attr('href').substr(1),
              type: 'POST',
              data: {count:ids.length, ids:ids},
              dataType: 'json',
              success: function(json) {
                if (json.success) {
					window.location.reload();
                }
              }
            });
        }
        return false;
    });
});
</script>
