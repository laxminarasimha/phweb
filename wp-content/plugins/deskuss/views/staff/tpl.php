<?php
$info=Format::htmlchars(($errors && $_POST)?$_POST:$_REQUEST);

if (is_a($template, 'EmailTemplateGroup')) {
    // New template implementation
    $id = 0;
    $tpl_id = $template->getId();
    $name = $template->getName();
    $group = $template;
    $selected = $_REQUEST['code_name'];
    $action = 'implement';
    $extras = array('code_name'=>$selected, 'tpl_id'=>$tpl_id);
    $msgtemplates=$template::$all_names;
    $desc = $msgtemplates[$selected];
    // Attempt to lookup the default data if it is defined
    $default = @$template->getMsgTemplate($selected);
    if ($default) {
        $info['subject'] = $default->getSubject();
        $info['body'] = Format::viewableImages($default->getBody());
    }
} else {
    // Template edit
    $id = $template->getId();
    $tpl_id = $template->getTplId();
    $name = $template->getGroup()->getName();
    $desc = $template->getDescription();
    $group = $template->getGroup();
    $selected = $template->getCodeName();
    $action = 'updatetpl';
    $extras = array();
    $msgtemplates=$group::$all_names;
    $info=array_merge(array('subject'=>$template->getSubject(), 'body'=>$template->getBodyWithImages()),$info);
}
$tpl=$msgtemplates[$selected];

?>
<div class="panel">
<form method="get" action="templates.php?">

<div class="panel-heading">
	<div class="panel-title table-caption">
		<span><?php echo __('Edit Email Template'); ?></span>
	</div>
</div>

<div class="panel-body">
	<div class="col-sm-4 panel-title table-caption form-inline">
		<span><?php echo __('Editing'); ?>:</span>&nbsp;
		<select id="tpl_options" class="form-control" name="id">
			<option value="">&mdash; <?php echo __('Select Setting Group'); ?> &mdash;</option>
			<?php
			$impl = $group->getTemplates();
			$current_group = false;
			$_tpls = $group::$all_names;
			$_groups = $group::$all_groups;
			uasort($_tpls, function($a,$b) {
				return strcmp($a['group'].$a['name'], $b['group'].$b['name']);
			});
			foreach($_tpls as $cn=>$nfo) {
				if (!$nfo['name'])
					continue;
				if (!$current_group || $current_group != $nfo['group']) {
					if ($current_group)
						echo "</optgroup>";
					$current_group = $nfo['group']; ?>
					<optgroup label="<?php echo isset($_groups[$current_group])
						? __($_groups[$current_group]) : $current_group; ?>">
				<?php }
				$sel=($selected==$cn)?'selected="selected"':'';
				echo sprintf('<option value="%s" %s>%s</option>',
					isset($impl[$cn]) ? $impl[$cn]->getId() : $cn,
					$sel,__($nfo['name']));
			}
			if ($current_group)
				echo "</optgroup>";
			?>
		</select>
    </div>
	<div class="col-sm-4 panel-title table-caption">
		<span><?php echo __('Template Set Name'); ?></span> 
		<small> — <a href="templates.php?tpl_id=<?php echo $tpl_id; ?>"><?php echo $name; ?></a></small>
	</div>
	<div class="col-sm-4 panel-title table-caption">
		<a class="ajax-tooltip" href="#ticket_variables.txt" data-container="body" data-toggle="popover" data-placement="left">
		<i class="fa fa-tags"></i>
		<?php echo __('Supported Variables'); ?></a>
	</div>
</div>

<input type="hidden" name="a" value="manage">
<input type="hidden" name="tpl_id" value="<?php echo $tpl_id; ?>">
</form>

<?php if ($errors) { ?>
    <br /><div class="alert alert-danger"><?php echo $errors['subject']; ?>&nbsp;<?php echo $errors['body']; ?></div>
<?php } ?>

<div class="panel-body">
<div class="col-sm-12">
<form action="templates.php?id=<?php echo $id; ?>&amp;a=manage" method="post" class="save">
<?php csrf_token(); ?>
<?php foreach ($extras as $k=>$v) { ?>
    <input type="hidden" name="<?php echo $k; ?>" value="<?php echo Format::htmlchars($v); ?>" />
<?php } ?>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="a" value="manage">
<input type="hidden" name="do" value="<?php echo $action; ?>">

<?php
$invalid = array();
if ($template instanceof EmailTemplate) {
    if ($invalid = $template->getInvalidVariableUsage()) {
    $invalid = array_unique($invalid); ?>
    <div class="alert alert-warning warning-banner"><?php echo sprintf(
        __('Some variables may not be a valid for this context. Please check for spelling errors and correct usage for %s.'), __('this template')); ?>
    <br/>
    <code><?php echo implode(', ', $invalid); ?></code>
</div>
<?php }
} ?>

<div class="row">
	<div class="col-sm-12">
		<label class="required"><?php echo __('Email Subject'); ?>: </label>
		<input type="text" class="form-control" name="subject" value="<?php echo $info['subject']; ?>">
		<br />
	</div>
	
	<div class="col-sm-12">
		<label class="required"><?php echo __('Email Body'); ?>: </label>
		<input type="hidden" name="draft_id" value=""/>
		<textarea name="body" cols="21" rows="16" style="width:98%;" wrap="soft"
			data-root-context="<?php echo $selected; ?>"
			data-toolbar-external="#toolbar" class="summernote-base draft" <?php
		list($draft, $attrs) = Draft::getDraftAndDataAttrs('tpl.'.$selected, $tpl_id, $info['body']);
		echo $attrs; ?>><?php echo $draft ?: $info['body'];
		?></textarea>
	</div>
</div>

<br/>
<p style="text-align:center">
    <input class="button btn btn-success" type="submit" name="submit" value="<?php echo __('Save Changes'); ?>">
    <input class="button btn btn-info" type="reset" name="reset" value="<?php echo __('Reset Changes'); ?>" onclick="javascript:
        setTimeout('location.reload()', 25);" />
    <input class="button btn btn-default" type="button" name="cancel" value="<?php echo __('Cancel Changes'); ?>" onclick='window.location.href="templates.php?tpl_id=<?php echo $tpl_id; ?>"'>
</p>
</form>
</div>
</div>
</div>
