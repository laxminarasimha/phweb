<div class="panel m-b-0">
<div class="panel-heading">
	<div class="panel-title drag-handle"><?php echo __('Manage Content'); ?> &mdash; <?php echo Format::htmlchars($content->getName()); ?>
		<a class="close_me pull-right" href="#"><i class="fa fa-close"></i></a>
	</div>
</div>

<div class="panel-body">

<?php
if(!empty($errors['err'])){
	echo '<div class="alert-danger">&nbsp;'.$errors['err'].'</div>'; 
}
?>

<form method="post" action="#content/<?php echo $content->getId(); ?>" style="clear:none">

<?php
if (count($langs) > 1) { ?>
    <ul class="nav nav-tabs" id="content-trans">
    <li class="empty"><i class="fa fa-globe" title="<?php echo __('This content is translatable'); ?>"></i></li>
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

<div id="content-trans_container">

	<small class="text-muted"><em><?php echo $content->getNotes(); ?></em></small><br /><br />
    <div id="translation-<?php echo $cfg->getPrimaryLanguage(); ?>"
        class="tab_content" lang="<?php echo $cfg->getPrimaryLanguage(); ?>">
	<label><?php echo __('Title'); ?>:</label>
	<?php
	if(!empty($errors['name'])){
		echo '<div class="alert-danger">&nbsp;'.$errors['name'].'</div>';
	}
	?>
	<input type="text" class="form-control" name="name" value="<?php
	echo Format::htmlchars($info['title']); ?>" spellcheck="true"
        lang="<?php echo $cfg->getPrimaryLanguage(); ?>" />
	<div style="margin-top: 5px">
	<label><?php echo __('Body'); ?>:</label>
	<?php
	if(!empty($errors['body'])){
		echo '<div class="alert-danger">&nbsp;'.$errors['body'].'</div>';
	}
	?>
    <textarea class="summernote-base no-bar" name="body"
        data-root-context="<?php echo $content->getType(); ?>">
		<?php echo Format::htmlchars(Format::viewableImages($info['body'])); ?>
	</textarea>
    </div>
    </div>
<?php foreach ($langs as $tag=>$nfo) {
        if ($tag == $cfg->getPrimaryLanguage())
            continue;
        $trans = $info['trans'][$tag]; ?>
    <div id="translation-<?php echo $tag; ?>" class="tab_content hidden"
        dir="<?php echo $nfo['direction']; ?>" lang="<?php echo $tag; ?>">
    <input type="text" class="form-control"
        name="trans[<?php echo $tag; ?>][title]" value="<?php
        echo Format::htmlchars($trans['title']); ?>"
        placeholder="<?php echo __('Title'); ?>"  spellcheck="true"
        lang="<?php echo $tag; ?>" />
    <div style="margin-top: 5px">
	<div>
    <textarea class="summernote-base no-bar" data-direction=<?php echo $nfo['direction']; ?>
        data-root-context="<?php echo $content->getType(); ?>"
        placeholder="<?php echo __('Message content'); ?>"
        name="trans[<?php echo $tag; ?>][body]"><?php
    echo Format::htmlchars(Format::viewableImages($trans['body']));
	?>
	</textarea>
	</div>
	</div>
    </div>
<?php } ?>

</div>

<p style="text-align:center;">
	<input class="btn btn-success" type="submit" value="<?php echo __('Save Changes'); ?>">
	<input class="btn btn-info" type="reset" id="reset" value="<?php echo __('Reset'); ?>">
	<input class="btn close_me" type="button" value="<?php echo __('Cancel'); ?>">
</p>
</div>
</form>
</div>
<script>
$(document).ready(function() {
	load_summernote();
});
</script>