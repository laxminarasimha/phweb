<?php
if (!defined('DSKADMININC') || !$thisstaff
        || !$thisstaff->hasPerm(FAQ::PERM_MANAGE))
    die('Access Denied');

$info=array();
$qs = array();
if($category && $_REQUEST['a']!='add'){
    $title=__('Update Category');
    $action='update';
    $submit_text=__('Save Changes');
    $info=$category->getHashtable();
    $info['id']=$category->getId();
    $info['notes'] = Format::viewableImages($category->getNotes());
    $qs += array('id' => $category->getId());
    $langs = $cfg->getSecondaryLanguages();
    $translations = $category->getAllTranslations();
    foreach ($langs as $tag) {
        foreach ($translations as $t) {
            if (strcasecmp($t->lang, $tag) === 0) {
                $trans = $t->getComplex();
                $info['trans'][$tag] = array(
                    'name' => $trans['name'],
                    'description' => Format::viewableImages($trans['description']),
                );
                break;
            }
        }
    }
}else {
    $title=__('Add New Category');
    $action='create';
    $submit_text=__('Add');
    $qs += array('a' => $_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>


<form action="categories.php?<?php echo Http::build_query($qs); ?>" method="post" class="save">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo esc_attr($action); ?>">
<input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
<input type="hidden" name="id" value="<?php echo esc_attr($info['id']); ?>">
 
<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo esc_html($title); ?>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-12">
			<label class="required" style="display:block;"> <?php echo __('Category Type');?>:
			</label>
			<label class="radio-inline">
				<input class="radio-inline" type="radio" name="ispublic" value="2" <?php echo $info['ispublic']==2?'checked="checked"':''; ?>><?php echo __('Featured');?> <?php echo __('(on front-page sidebar)');?>
			</label>&nbsp;&nbsp;
			<label class="radio-inline">
				<input class="radio-inline" type="radio" name="ispublic" value="1" <?php echo $info['ispublic']==1?'checked="checked"':''; ?>><?php echo __('Public');?> <?php echo __('(publish)');?>
			</label>&nbsp;&nbsp;
			<label class="radio-inline">
				<input class="radio-inline" type="radio" name="ispublic" value="0" <?php echo !$info['ispublic']?'checked="checked"':''; ?>><?php echo __('Private');?> <?php echo __('(internal)');?>
			</label>
		<?php if(!empty($errors['ispublic'])){ ?>
			<div class="alert-danger"><?php echo esc_html($errors['ispublic']); ?></div>
		<?php } ?>
		</div>
	</div>
	<br />
	<div class="row">
		<div class="col-sm-12">
		<?php
		$langs = Internationalization::getConfiguredSystemLanguages();
		if (count($langs) > 1) { ?>
			<ul class="nav nav-tabs" id="trans">
				<li class="empty"><i class="fa fa-globe" title="This content is translatable"></i></li>
		<?php foreach ($langs as $tag=>$i) {
			list($lang, $locale) = explode('_', $tag);
		 ?>
			<li class="<?php if ($tag == $cfg->getPrimaryLanguage()) echo "active";
				?>"><a href="#lang-<?php echo esc_attr($tag); ?>" title="<?php
				echo Internationalization::getLanguageDescription($tag);
				?>" data-toggle="tab">
				<span class="flag flag-<?php echo strtolower($i['flag'] ?: $locale ?: $lang); ?>"></span>
			</a></li>
		<?php } ?>
			</ul>
		<?php
		} ?>

		<div class="tab-content">
		<?php foreach ($langs as $tag=>$i) {
			$code = $i['code'];
			$cname = 'name';
			$dname = 'description';
			if ($tag == $cfg->getPrimaryLanguage()) {
				$category = $info[$cname];
				$desc = $info[$dname];
			}
			else {
				$category = $info['trans'][$code][$cname];
				$desc = $info['trans'][$code][$dname];
				$cname = "trans[$code][$cname]";
				$dname = "trans[$code][$dname]";
			} ?>
			<div class="tab-pane fade <?php
				if ($code == $cfg->getPrimaryLanguage()) echo "in active";
				if ($i['direction'] == 'rtl') echo ' rtl" dir="rtl';
				?>" id="lang-<?php echo esc_attr($tag); ?>">
			<div class="form-group">
				<label class="required" style="display:block;"><?php echo __('Category Name');?>:&nbsp;
					<small class="text-muted"><?php echo __('Short descriptive name');?></small>
				</label>
			<input type="text" class="form-control" name="<?php echo esc_attr($cname); ?>" value="<?php echo esc_attr($category); ?>">
			<?php if(!empty($errors['name'])){ ?>
					<div class="alert-danger"><?php echo $errors['name']; ?></div>
			<?php } ?>
			</div>

			<div style="padding:8px 0;">
				<label class="required" style="display:block;"><?php echo __('Category Description');?>:&nbsp;
					<small class="text-muted"><?php echo __('Summary of the category');?></small>
				</label>
			<?php if(!empty($errors['description'])){ ?>
					<div class="alert-danger"><?php echo $errors['description']; ?></div>
			<?php } ?>
			<textarea class="summernote-base" name="<?php echo esc_attr($dname); ?>"><?php
				echo $desc; ?></textarea></div>
			</div>
		<?php } ?>
		</div>
		</div>
	</div>
	<div class="panel-heading">
		<a class="internal_note"><div class="panel-title table-caption"><i class="fa fa-plus-square"></i>&nbsp;<?php echo __('Internal Notes');?>:&nbsp;<small class="text-muted"><em><span class="faded"><?php echo __("Be liberal, they're internal");?></span></em></small>
		</div></a>
	</div>
	<div class="panel-body notes" style="display:none;">
		<textarea class="summernote-base" name="notes"><?php echo $info['notes']; ?></textarea>
	</div>
	
	<div class="form-group" style="text-align:center;margin-top:20px;">
		<input type="submit" class="btn btn-success" name="submit" value="<?php echo esc_attr($submit_text); ?>">
		<input type="reset" class="btn btn-info" name="reset"  value="<?php echo __('Reset');?>">
		<input type="button" class="btn" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="categories.php"'>
	</div>
</div>
</div>
</form>
