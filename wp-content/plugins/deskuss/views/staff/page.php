<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$pageTypes = array(
        'landing' => __('Landing Page'),
        'offline' => __('Offline Page'),
        'thank-you' => __('Thank-You Page'),
        'other' => __('Other'),
        );
$info = $qs = array();
if($page && $_REQUEST['a']!='add'){
    $title=__('Update Page');
    $action='update';
    $submit_text=__('Save Changes');
    $info=$page->getHashtable();
    $info['body'] = Format::viewableImages($page->getBody());
    $info['notes'] = Format::viewableImages($info['notes']);
    $trans['name'] = $page->getTranslateTag('name');
    $slug = Format::slugify($info['name']);
    $qs += array('id' => $page->getId());
    $translations = CustomDataTranslation::allTranslations(
        $page->getTranslateTag('name:body'), 'article');
    foreach ($cfg->getSecondaryLanguages() as $tag) {
        foreach ($translations as $t) {
            if (strcasecmp($t->lang, $tag) === 0) {
                $C = $t->getComplex();
                $info['trans'][$tag] = Format::viewableImages($C['body']);
                break;
            }
        }
    }
}else {
    $title=__('Add New Page');
    $action='add';
    $submit_text=__('Add Page');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $qs += array('a' => $_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="panel">
<form action="pages.php?<?php echo Http::build_query($qs); ?>" method="post" class="save">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo $action; ?>">
<input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
<input type="hidden" name="id" value="<?php echo $info['id']; ?>">

<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo $title; ?> &nbsp;
		<i class="help-tip fa fa-question-circle" href="#site_pages"></i>
	</div>
</div>

<div class="panel-body">
<div class="panel">
	<div class="panel-heading">
		<div class="panel-title table-caption">
			<em><?php echo __('Page information');?></em>
		</div>
	</div>
	<div class="panel-body" style="padding-bottom:0px">
		<div class="row">
			<div class="col-sm-6 form-group">
				<label class="required"><?php echo __('Name'); ?>: </label>
				<input type="text" class="form-control" size="40" name="name" value="<?php echo $info['name']; ?>"
						autofocus data-translate-tag="<?php echo $trans['name']; ?>"/>
				<?php
				if(!empty($errors['name'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['name'].'</div>';
				}
				?>
			</div>
			<div class="col-sm-6 form-group">
				<label class="required"><?php echo __('Type'); ?>:
				&nbsp;<i class="help-tip fa fa-question-circle" href="#type"></i>
				</label>
				<select name="type" class="form-control">
					<option value="" selected="selected">&mdash; <?php
					echo __('Select Page Type'); ?> &mdash;</option>
					<?php
					foreach($pageTypes as $k => $v)
						echo sprintf('<option value="%s" %s>%s</option>',
								$k, (($info['type']==$k)?'selected="selected"':''), $v);
					?>
				</select>
				<?php
				if(!empty($errors['type'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['type'].'</div>';
				}
				?>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label class="required"><?php echo __('Status'); ?>: </label>
				<p>
					<input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>>&nbsp;
					<strong><?php echo __('Active'); ?></strong>&nbsp;
					<input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>>&nbsp;
					<strong><?php echo __('Disabled'); ?></strong>
					<?php
					if(!empty($errors['isactive'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['isactive'].'</div>';
					}
					?>
				</p>          
			</div>
			<?php if ($info['name'] && $info['type'] == 'other') { ?>
			<div class="col-sm-6 form-group">
				<label><?php echo __('Public URL'); ?>: &nbsp;
					<em class="text-muted"><?php echo __('(Accessible only if page is active)'); ?></em>
				</label>
				<p>
					<a href="<?php echo sprintf("%s/pages/%s",
							$dsk->getConfig()->getBaseUrl(), urlencode($slug));
						?>">pages/<?php echo $slug; ?></a>
				</p>           
			</div>	
			<?php } ?>
		</div>	
	</div>
</div>

<div style="margin-top: 10px">

<ul class="nav nav-tabs">
	<li class="active"><a href="#page-content" data-toggle="tab" aria-expanded="true"><?php echo __('Page Content'); ?></a></li>
	<li><a href="#notes" data-toggle="tab" aria-expanded="false"><?php echo __('Internal Notes'); ?></a></li>
</ul>

<div class="tab-content tab-content-bordered">
<div class="tab-pane fade active in" id="page-content">

<?php
$langs = Internationalization::getConfiguredSystemLanguages();
if ($page && count($langs) > 1) { ?>
	<ul class="nav nav-tabs" id="translations">
		<li class="empty"><i class="fa fa-globe" title="This content is translatable"></i></li>
<?php foreach ($langs as $tag=>$nfo) { ?>
		<li class="<?php if ($tag == $cfg->getPrimaryLanguage()) echo "active";
         ?>">
			<a href="#translation-<?php echo $tag; ?>" title="<?php
				echo Internationalization::getLanguageDescription($tag);
				?>" data-toggle="tab" aria-expanded="<?php if ($tag == $cfg->getPrimaryLanguage()) {echo "true";}else{echo "false";}
				?>">
				<span class="flag flag-<?php echo strtolower($nfo['flag']); ?>"></span>
			</a>
		</li>
<?php } ?>
    </ul>
<?php
}

	// For landing page, constrain to the diplayed width of 565px;
	if ($info['type'] == 'landing')
		$width = '565px';
	else
		$width = '100%';
	?>
	
	<?php
	if(!empty($errors['body'])){
		echo '<div class="alert-danger">&nbsp;'.$errors['body'].'</div>';
	}
	?>
    <div id="translations_container" class="tab-content">
		<div id="translation-<?php echo $cfg->getPrimaryLanguage(); ?>" class="tab-pane fade active in"
		lang="<?php echo $cfg->getPrimaryLanguage(); ?>">
			<textarea  name="body" cols="21" rows="12" class="summernote-base richtext draft"
				data-width="<?php echo $width; ?>"
			<?php
			if (!$info['type'] || $info['type'] == 'thank-you') echo 'data-root-context="thank-you"';
			list($draft, $attrs) = Draft::getDraftAndDataAttrs('page', $info['id'], $info['body']);
			echo $attrs; ?>><?php echo $info['body'] ?: $draft; ?></textarea>
		</div>

<?php if ($langs && $page) {
	foreach ($langs as $tag=>$nfo) {
		if ($tag == $cfg->getPrimaryLanguage())
			continue; ?>
		<div id="translation-<?php echo $tag; ?>" class="tab-pane fade"
        dir="<?php echo $nfo['direction']; ?>" lang="<?php echo $tag; ?>">
			<textarea name="trans[<?php echo $tag; ?>][body]" cols="21" rows="12"
<?php if ($info['type'] == 'thank-you') echo 'data-root-context="thank-you"'; ?>
          style="width:100%" class="summernote-base richtext draft" data-width="<?php echo $width; ?>"
<?php
    list($draft, $attrs) = Draft::getDraftAndDataAttrs('page', $info['id'].'.'.$tag, $info['trans'][$tag]);
    echo $attrs; ?>><?php echo $info['trans'][$tag] ?: $draft; ?></textarea>
		</div>
<?php }
} ?>
		<div class="alert alert-info">
			<em><i class="fa fa-info-circle"></i> <?php
			  echo __(
				'Ticket variables are only supported in thank-you pages.'
			  ); ?></em>
		</div>
    </div>
</div>

<div class="tab-pane fade" id="notes">
	<textarea  class="summernote-base richtext no-bar" name="notes" cols="21"
		rows="8" style="width: 80%;"><?php echo $info['notes']; ?></textarea>
</div>
</div>
</div>
<br/>
<p style="text-align:center">
	<input type="submit" class="btn btn-success" name="submit" value="<?php echo $submit_text; ?>">
	<input type="reset" class="btn btn-info" name="reset"  value="<?php echo __('Reset'); ?>">
	<input type="button" class="btn btn-default" name="cancel" value="<?php echo __('Cancel'); ?>" onclick='window.location.href="pages.php"'>
</p>
</div>
</form>
</div>
