<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$info = $qs = array();
if($template && $_REQUEST['a']!='add'){
    $title=__('Update Template');
    $action='update';
    $submit_text=__('Save Changes');
    $info=$template->getInfo();
    $info['tpl_id']=$template->getId();
    $qs += array('tpl_id' => $template->getId());
}else {
    $title=__('Add New Template');
    $action='add';
    $submit_text=__('Add Template');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['lang_id'] = $cfg->getPrimaryLanguage();
    $qs += array('a' => $_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>

<div class="panel">
<form action="templates.php?<?php echo Http::build_query($qs); ?>" method="post" class="save">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo $action; ?>">
<input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
<input type="hidden" name="tpl_id" value="<?php echo $info['tpl_id']; ?>">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo $title; ?>
	</div>
</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-6 form-group">	
				<label class="required"><?php echo __('Name'); ?>:
				</label>
				<input type="text" size="30" class="form-control" name="name" value="<?php echo $info['name']; ?>">
				<?php
				if(!empty($errors['name'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['name'].'</div>';
				}
				?>
			</div>
			<div class="col-sm-6 form-group">	
				<label class="required"><?php echo __('Status'); ?>:
					&nbsp;<i class="help-tip fa fa-question-circle" href="#status"></i>
				</label>
				<p>
					<label><input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>>
					<strong>&nbsp;<?php echo __('Enabled'); ?></strong>
					</label>
					&nbsp;
					<label><input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>>
					&nbsp;<?php echo __('Disabled'); ?>
					</label>
					<?php
					if(!empty($errors['isactive'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['isactive'].'</div>';
					}
					?>
				</p>
			</div>
		</div>
		<?php
        if(!$template){ ?>
		<div class="row">
			<div class="col-sm-6 form-group">	
				<label class="required"><?php echo __('Template Set To Clone'); ?>:
					&nbsp;<i class="help-tip fa fa-question-circle" href="#template_to_clone"></i>
				</label>
				<select name="tpl_id" class="form-control" onchange="javascript:
					if ($(this).val() == 0)
						$('#language').show();
					else
						$('#language').hide();
					">
					<option value="0">&mdash; <?php echo __('Stock Templates'); ?> &mdash;</option>
					<?php
						$sql='SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_GRP_TABLE.' ORDER by name';
						if(($res=db_query($sql)) && db_num_rows($res)){
							while(list($id,$name)=db_fetch_row($res)){
								$selected=($info['tpl_id'] && $id==$info['tpl_id'])?'selected="selected"':'';
								echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
							}
						}
					?>
				</select>
				<?php
				if(!empty($errors['tpl_id'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['tpl_id'].'</div>';
				}
				?>
			</div>
			<div class="col-sm-6 form-group" id="language">	
				<label class="required"><?php echo __('Language'); ?>:
					&nbsp;<i class="help-tip fa fa-question-circle" href="#language"></i>
				</label>
				<p>
					<?php
					$langs = Internationalization::availableLanguages(); ?>
					<select class="form-control" name="lang_id">
						<?php foreach($langs as $l) {
							$selected = ($info['lang_id'] == $l['code']) ? 'selected="selected"' : ''; ?>
								<option value="<?php echo $l['code']; ?>" <?php echo $selected;
								?>><?php echo Internationalization::getLanguageDescription($l['code']); ?></option>
						<?php } ?>
					</select>
					<?php
					if(!empty($errors['lang_id'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['lang_id'].'</div>';
					}
					?>
				</p>
			</div>
		</div>
<?php }  ?>		
		
<div class="row">
<div class="col-md-12">
<div class="table-light table-responsive">
<table class="table table-bordered form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <tbody>
        <?php
        if($template){ ?>
        <tr>
            <td width="180" class="required">
                <?php echo __('Language');?>:
            </td>
            <td><?php
            echo Internationalization::getLanguageDescription($info['lang']);
            ?></td>
        </tr>
        <?php
		$current_group = false;
		// Only EmailTemplateGroup has getTemplates(), static all_names, all_groups
		$impl = method_exists($template, 'getTemplates') ? $template->getTemplates() : array();
		$_tpls = isset($template::$all_names) ? $template::$all_names : array();
		$_groups = isset($template::$all_groups) ? $template::$all_groups : array();
		uasort($_tpls, function($a,$b) {
			return strcmp($a['group'].$a['name'], $b['group'].$b['name']);
		});
         foreach($_tpls as $cn=>$info){
             if (!$info['name'])
                 continue;
             if (!$current_group || $current_group != $info['group']) {
                $current_group = $info['group']; ?>
			<tr>
				<th colspan="2">
				<em><strong><?php echo isset($_groups[$current_group])
				? $_groups[$current_group] : $current_group; ?></strong>
				:: <?php echo __('Click on the title to edit.'); ?></em>
				</th>
			</tr>
		<?php } # end if ($current_group)
            if (isset($impl[$cn])) {
                echo sprintf('<tr><td colspan="2">&nbsp;<strong><a href="templates.php?id=%d&a=manage">%s</a></strong>, <span class="faded">%s</span><br/>&nbsp;%s</td></tr>',
                $impl[$cn]->getId(), Format::htmlchars(__($info['name'])),
                sprintf(__('Updated %s'), Format::datetime($impl[$cn]->getLastUpdated())),
                Format::htmlchars(__($info['desc'])));
            } else {
                echo sprintf('<tr><td colspan=2>&nbsp;<strong><a
                    href="templates.php?tpl_id=%d&a=implement&code_name=%s"
                    >%s</a></strong><br/>&nbsp%s</td></tr>',
                    $template->getid(),$cn,format::htmlchars(__($info['name'])),
                    format::htmlchars(__($info['desc'])));
            }
         } # endforeach
        } # end if template ?>
    </tbody>
</table>
</div>
</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<div class="panel-heading">
			<a class="internal_note"><div class="panel-title table-caption">
				<i class="fa fa-plus-square"></i>&nbsp;<strong><?php echo __('Internal Notes');?></strong>:<small class="text-muted"><em> <?php echo __("Be liberal, they're internal");?></em></small></div>
			</a>
		</div>
	</div>
	<div class="col-sm-12 panel-body notes" style="display:none;">
		<textarea name="notes" class="summernote-base richtext no-bar" rows="6" cols="80"><?php echo $info['notes']; ?></textarea>
	</div>
</div>
</div>

<p style="text-align:center">
    <input type="submit" class="btn btn-success" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  class="btn btn-info" name="reset"  value="<?php echo __('Reset');?>">
    <input type="button" class="btn btn-default" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="templates.php"'>
</p>
<br/>
</form>
</div>
