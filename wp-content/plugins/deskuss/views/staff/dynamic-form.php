<?php

$info=array();
if($form && $_REQUEST['a']!='add') {
    $title = __('Update form section');
    $action = 'update';
    $url = "?id=".urlencode($_REQUEST['id']);
    $submit_text=__('Save Changes');
    $info = $form->getInfo();
    $trans = array(
        'title' => $form->getTranslateTag('title'),
        'instructions' => $form->getTranslateTag('instructions'),
    );
    $newcount=2;
    $translations = CustomDataTranslation::allTranslations($trans, 'phrase');
    $_keys = array_flip($trans);
    foreach ($translations as $t) {
        if (!Internationalization::isLanguageEnabled($t->lang))
            continue;
        // Create keys of [trans][de_DE][title] for instance
        $info['trans'][$t->lang][$_keys[$t->object_hash]]
            = Format::viewableImages($t->text);
    }
} else {
    $title = __('Add new custom form section');
    $action = 'add';
    $url = '?a=add';
    $submit_text=__('Add Form');
    $newcount=4;
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>
<div class="panel">
<form class="manage-form" action="<?php echo $url ?>" method="post" class="save">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo $action; ?>">
<input type="hidden" name="a" value="<?php echo $action; ?>">
<input type="hidden" name="id" value="<?php echo $info['id']; ?>">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo $title; ?>
		<small class="text-muted">
		— <em><?php echo __('Forms are used to allow for collection of custom data'); ?></em>
		</small>
	</div>
</div>

<div class="panel-body">
<div class="row">
<div class="col-md-12">
<div class="form-group">
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Form Information'); ?>
		</div>
	</div>
	<div class="form-group">
<?php
$langs = Internationalization::getConfiguredSystemLanguages();
if ($form && count($langs) > 1) { ?>
	<ul class="alt tabs clean" id="translations">
		<li class="empty"><i class="fa fa-globe" title="This content is translatable"></i></li>
<?php foreach ($langs as $tag=>$nfo) { ?>
		<li class="<?php if ($tag == $cfg->getPrimaryLanguage()) echo "active";
			?>"><a href="#translation-<?php echo $tag; ?>" title="<?php
		echo Internationalization::getLanguageDescription($tag);
    ?>"><span class="flag flag-<?php echo strtolower($nfo['flag']); ?>"></span>
	</a></li>
<?php } ?>
	</ul>
<?php
} ?>
	<div id="translations_container">
		<div id="translation-<?php echo $cfg->getPrimaryLanguage(); ?>" class="tab_content form-inline form-group" 
				lang="<?php echo $cfg->getPrimaryLanguage(); ?>">
			<div class="col-sm-6 form-group">
				<label class="required"><?php echo __('Title'); ?>: &nbsp;
					<i class="help-tip fa fa-question-circle" href="#form_title"></i>
				</label>
				&nbsp;
				<input type="text" class="form-control" name="title" size="50" autofocus
					value="<?php echo $info['title']; ?>"/>	
				<?php
				if(!empty($errors['title'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['title'].'</div>';
				}
				?>
				<br /><br />
			</div>
			
			<div class="col-sm-12 form-group" >
				<label style="margin-top: 8px;"><?php echo __('Instructions'); ?>: &nbsp;
					<i class="help-tip fa fa-question-circle" href="#form_instructions"></i>
				</label>
				<textarea name="instructions" rows="6" cols="40"  class="summernote-base small form-control"><?php
					echo $info['instructions']; ?></textarea>
			</div>
		</div>
	</div>

<?php if ($langs && $form) {
    foreach ($langs as $tag=>$nfo) {
		if ($tag == $cfg->getPrimaryLanguage())
			continue; ?>
		<div id="translation-<?php echo $tag; ?>" class="tab_content form-inline"
			style="display:none;" lang="<?php echo $tag; ?>">
			<div class="col-sm-6 form-group">
				<label class="required"><?php echo __('Title'); ?>: &nbsp;
					<i class="help-tip fa fa-question-circle" href="#form_title"></i>
				</label>
				&nbsp;
				<input type="text" name="trans[<?php echo $tag; ?>][title]" class="form-control" size="50"
					value="<?php echo $info['trans'][$tag]['title']; ?>"/>
					
			</div>
			<div class="col-sm-12 form-group">
				<label style="margin-top: 8px"><?php echo __('Instructions'); ?>: &nbsp;
					<i class="help-tip fa fa-question-circle" href="#form_instructions"></i>
				</label>
				<textarea name="trans[<?php echo $tag; ?>][instructions]" cols="21" rows="12"
					style="width:100%" class="summernote-base small"><?php
					echo $info['trans'][$tag]['instructions']; ?>
				</textarea>
			</div>
		</div>
<?php }
} ?>
	</div>
</div>
</div>
</div>

<div class="row">
<div class="col-md-12">
<div class="table-light table-responsive">
    <?php if ($form && $form->get('type') == 'T') {
		$uform = UserForm::objects()->one();
    ?>
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('User Information Fields'); ?>
			<small class="text-muted">
			— <em><?php echo sprintf(__('(These fields are requested for new tickets
					via the %s form)'),
					$uform->get('title')); ?></em>
			</small>
			
		</div>
	</div>
	<table class="table form_table" width="940" border="0" cellspacing="0" cellpadding="2">
	<thead>
		<tr>
			<th></th>
			<th><?php echo __('Label'); ?></th>
			<th><?php echo __('Type'); ?></th>
			<th><?php echo __('Visibility'); ?></th>
			<th><?php echo __('Variable'); ?></th>
			<th><?php echo __('Delete'); ?></th>
		</tr>
	</thead>
	<tbody>
    <?php
		$ftypes = FormField::allTypes();
		foreach ($uform->getFields() as $f) {
			if (!$f->isVisibleToUsers()) continue;
		?>
		<tr>
			<td></td>
			<td><?php echo $f->get('label'); ?></td>
			<td><?php $t=FormField::getFieldType($f->get('type')); echo __($t[0]); ?></td>
			<td><?php echo $f->getVisibilityDescription(); ?></td>
			<td><?php echo $f->get('name'); ?></td>
			<td><input type="checkbox" disabled="disabled"/></td>
		</tr>

        <?php } ?>
	</tbody>
	</table>
	<br />
    <?php } # form->type == 'T' ?>
	
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Form Fields'); ?>
			<small class="text-muted">
			— <em><?php echo __('fields available where this form is used'); ?></em>
			</small>
			
		</div>
	</div>
	<table class="table form_table" width="940" border="0" cellspacing="0" cellpadding="2">
	<thead>
		<tr>
			<th nowrap width="4%"
				><i class="help-tip fa fa-question-circle" href="#field_sort"></i></th>
			<th nowrap><?php echo __('Label'); ?>
				<i class="help-tip fa fa-question-circle" href="#field_label"></i></th>
			<th nowrap><?php echo __('Type'); ?>
				<i class="help-tip fa fa-question-circle" href="#field_type"></i></th>
			<th nowrap><?php echo __('Visibility'); ?>
				<i class="help-tip fa fa-question-circle" href="#field_visibility"></i></th>
			<th nowrap><?php echo __('Variable'); ?>
				<i class="help-tip fa fa-question-circle" href="#field_variable"></i></th>
			<th nowrap><?php echo __('Delete'); ?>
				<i class="help-tip fa fa-question-circle" href="#field_delete"></i></th>
		</tr>
	</thead>
	<tbody class="sortable-rows" data-sort="sort-">
	<?php if ($form) foreach ($form->getDynamicFields() as $f) {
		$id = $f->get('id');
		$deletable = !$f->isDeletable() ? 'disabled="disabled"' : '';
		$force_name = $f->isNameForced() ? 'disabled="disabled"' : '';
		$fi = $f->getImpl();
		$ferrors = $f->errors(); ?>
		<tr>
			<td align="center"><i class="fa fa-sort"></i></td>
			<td><input type="text" size="32" class="form-control" name="label-<?php echo $id; ?>"
				data-translate-tag="<?php echo $f->getTranslateTag('label'); ?>"
				value="<?php echo Format::htmlchars($f->get('label')); ?>"/>
				<?php
				if(!empty($ferrors['label'])){
					echo '<div class="alert-danger">&nbsp;'.$ferrors['label'].'</div>';
				}
				?>
			</td>
			<td nowrap><select style="max-width:150px;display:inline-block" class="form-control" name="type-<?php echo $id; ?>" <?php
				if (!$fi->isChangeable()) echo 'disabled="disabled"'; ?>>
				<?php foreach (FormField::allTypes() as $group=>$types) {
						?><optgroup label="<?php echo Format::htmlchars(__($group)); ?>"><?php
						foreach ($types as $type=>$nfo) {
							if ($f->get('type') != $type
									&& isset($nfo[2]) && !$nfo[2]) continue; ?>
				<option value="<?php echo $type; ?>" <?php
					if ($f->get('type') == $type) echo 'selected="selected"'; ?>>
					<?php echo __($nfo[0]); ?></option>
					<?php } ?>
				</optgroup>
				<?php } ?>
			</select>
			<?php if ($f->isConfigurable()) { ?>
				<a class="action-button field-config" style="overflow:inherit"
					href="#ajax.php/form/field-config/<?php echo $f->get('id'); ?>"
					onclick="javascript:
						$.dialog($(this).attr('href').substr(1), [201]);
						return false;
					">(<i class="fa fa-cog"></i> <?php echo __('Config'); ?>)</a>
			<?php } ?></td>
			<td>
				<?php echo $f->getVisibilityDescription(); ?>
			</td>
			<td>
				<input type="text" class="form-control" size="20" name="name-<?php echo $id; ?>"
					value="<?php echo Format::htmlchars($f->get('name'));
					?>" <?php echo $force_name ?>/>
				<?php
				if(!empty($ferrors['name'])){
					echo '<div class="alert-danger">&nbsp;'.$ferrors['name'].'</div>';
				}
				?>
				</td>
			<td align="center">
				<input class="delete-box" type="checkbox" name="delete-<?php echo $id; ?>"
					data-field-label="<?php echo $f->get('label'); ?>"
					data-field-id="<?php echo $id; ?>"
					<?php echo $deletable; ?>/>
				<input type="hidden" name="sort-<?php echo $id; ?>"
					value="<?php echo $f->get('sort'); ?>"/>
			</td>
		</tr>
		<tr>
    <?php
    }
    for ($i=0; $i<$newcount; $i++) { ?>
			<td align="center"><em>+</em>
				<input type="hidden" name="sort-new-<?php echo $i; ?>"
					value="<?php echo $info["sort-new-$i"]; ?>"/>
			</td>
			<td><input type="text" class="form-control" size="32" name="label-new-<?php echo $i; ?>"
					value="<?php echo $info["label-new-$i"]; ?>"/>
			</td>
			<td>
				<select style="max-width:150px" class="form-control" name="type-new-<?php echo $i; ?>">
				<?php foreach (FormField::allTypes() as $group=>$types) {
					?>
					<optgroup label="<?php echo Format::htmlchars(__($group)); ?>"><?php
					foreach ($types as $type=>$nfo) {
						if (isset($nfo[2]) && !$nfo[2]) continue; ?>
						<option value="<?php echo $type; ?>"
						<?php if ($info["type-new-$i"] == $type) echo 'selected="selected"'; ?>>
							<?php echo __($nfo[0]); ?>
						</option>
					<?php } ?>
					</optgroup>
				<?php } ?>
				</select>
			</td>
			<td>
				<select class="form-control" name="visibility-new-<?php echo $i; ?>">
				<?php
					$rmode = $info['visibility-new-'.$i];
					foreach (DynamicFormField::allRequirementModes() as $m=>$I) { ?>
					<option value="<?php echo $m; ?>" <?php if ($rmode == $m)
						 echo 'selected="selected"'; ?>><?php echo $I['desc']; ?></option>
				<?php } ?>
					<select>
					<td>
						<input type="text" class="form-control" size="20" name="name-new-<?php echo $i; ?>"
							value="<?php echo $info["name-new-$i"]; ?>"/>
						<?php
						if(!empty($errors["new-$i"]['name'])){
							echo '<div class="alert-danger">&nbsp;'.$errors["new-$i"]['name'].'</div>';
						}
						?>
					</td>
					</select>
			</select>
			</td>
		</tr>
    <?php } ?>
    </tbody>
	</table>
</div>
</div>
</div>

<div class="panel-heading">
	<a class="internal_note">
		<div class="panel-title table-caption"><em><strong><i class="fa fa-plus-square"></i>&nbsp;<?php echo __('Internal Notes'); ?>:</strong><?php echo __("Be liberal, they're internal"); ?></em></div>
	</a>
</div>
<div class="panel-body notes" style="display:none;">
	<textarea class="summernote-base no-bar form-control" name="notes" rows="6" cols="80"><?php
	echo $info['notes']; ?></textarea>
</div>

<div class="form-group" style="margin-top:20px;text-align:center;">
    <input type="submit" name="submit" class="btn btn-success" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  class="btn btn-info" value="<?php echo __('Reset'); ?>">
    <input type="button" name="cancel" class="btn btn-default" value="<?php echo __('Cancel'); ?>" onclick='window.location.href="?"'>
</div>

<div style="display:none;border:none" class="dialog panel panel-primary panel-dark" id="delete-confirm">
<div class="panel-heading">
	<div class="drag-handle panel-title"><?php echo __('Remove Existing Data?'); ?>
		<a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
	</div>
</div>	
<div class="panel-body">
	<p>
		<?php echo sprintf(__('You are about to delete %s fields.'),
			'<span id="deleted-count"></span>'); ?>
		<br /><br />
		<span class="text-muted">
			<?php echo __('Would you also like to remove data currently entered for this field?'); ?>
			<br />
			<em>
			<?php echo __('If you opt not to remove the data now, you will have the option to delete the the data when editing it.'); ?>
			</em>
		</span>
	</p>
	<p>
		<font class="text-danger"><?php echo __('Deleted data CANNOT be recovered.'); ?></font>
	</p>
	<div id="deleted-fields"></div>
	<p class="full-width">
		<span class="buttons pull-left">
			<input type="button" value="<?php echo __('No, Cancel'); ?>" class="close_me btn btn-success">
		</span>
		<span class="buttons pull-right">
			<input type="submit" value="<?php echo __('Continue'); ?>" class="confirm btn btn-info">
		</span>
	</p>
</div>
</div>
</div>
</form>
</div>
<div style="display:none;" class="dialog draggable" id="field-config">
	<div class="body"></div>
</div>

<script type="text/javascript">
$('form.manage-form').on('submit.inline', function(e) {
    var formObj = this, deleted = $('input.delete-box:checked', this);
    if (deleted.length) {
        e.stopImmediatePropagation();
        $('#overlay').show();
        $('#deleted-fields').empty();
        deleted.each(function(i, e) {
            $('#deleted-fields').append($('<p></p>')
                .append($('<input/>').attr({type:'checkbox',name:'delete-data-'
                    + $(e).data('fieldId')})
                ).append($('<strong>').html(
                    ' &nbsp;<?php echo __('Remove all data entered for <u> %s </u>?');
                        ?>'.replace('%s', $(e).data('fieldLabel'))
                ))
            );
        });
        $('#delete-confirm').show().delegate('input.confirm', 'click.confirm', function() {
            $('.dialog#delete-confirm').hide();
            $(formObj).unbind('submit.inline');
            $(window).unbind('beforeunload');
            $('#loading').show();
        })
        return false;
    }
    // TODO: Popup the 'please wait' dialog
    $(window).unbind('beforeunload');
    $('#loading').show();
});
</script>
