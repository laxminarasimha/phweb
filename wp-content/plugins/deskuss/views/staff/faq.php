<?php
if (!defined('DSKADMININC') || !$thisstaff
        || !$thisstaff->hasPerm(FAQ::PERM_MANAGE))
    die('Access Denied');

$info = $qs = array();
if($faq){
    $title=__('Update FAQ').': '.$faq->getQuestion();
    $action='update';
    $submit_text=__('Save Changes');
    $info=$faq->getHashtable();
    $info['id']=$faq->getId();
    $info['answer']=Format::viewableImages($faq->getAnswer());
    $info['notes']=Format::viewableImages($faq->getNotes());
    $qs += array('id' => $faq->getId());
    $langs = $cfg->getSecondaryLanguages();
    $translations = $faq->getAllTranslations();
    foreach ($langs as $tag) {
        foreach ($translations as $t) {
            if (strcasecmp($t->lang, $tag) === 0) {
                $trans = $t->getComplex();
                $info['trans'][$tag] = array(
                    'question' => $trans['question'],
                    'answer' => Format::viewableImages($trans['answer']),
                );
                break;
            }
        }
    }
}else {
    $title=__('Add New FAQ');
    $action='create';
    $submit_text=__('Add FAQ');
    if($category) {
        $qs += array('cid' => $category->getId());
        $info['category_id']=$category->getId();
    }
}
//TODO: Add attachment support.
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
$qstr = Http::build_query($qs);
?>
<form action="faq.php?<?php echo $qstr; ?>" method="post" class="save" enctype="multipart/form-data">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo esc_attr($action); ?>">
<input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
<input type="hidden" name="id" value="<?php echo esc_attr($info['id']); ?>">
 
<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Frequently Asked Questions');?>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-md-6">
			<label class="required">
			  <?php echo __('Category Listing');?>&nbsp;
			</label>
			<select class="form-control" name="category_id">
				<option value="0"><?php echo __('Select FAQ Category');?> </option>
		<?php foreach (Category::objects() as $C) { ?>
				<option value="<?php echo esc_attr($C->getId()); ?>" <?php
					if ($C->getId() == $info['category_id']) echo 'selected="selected"';
					?>><?php echo sprintf('%s (%s)',
						esc_html($C->getName()),
						$C->isPublic() ? __('Public') : __('Private')
					); ?></option>
			<?php } ?>
			</select>
		<?php if(!empty($errors['category_id'])){ ?>
			<div class="alert-danger"><?php echo esc_html($errors['category_id']); ?></div>
		<?php } ?>
			
			<small class="text-muted"><?php echo __('FAQ category the question belongs to.');?></small>
		</div>
		<br/>
		<div class="row">
			<div class="col-sm-6">
				<label class="required">
				<?php echo __('Listing Type');?>:&nbsp;
					<i class="help-tip fa fa-question-circle" href="#listing_type"></i>
				</label>
				
				<select class="form-control" name="ispublished">
					<option value="2" <?php echo $info['ispublished'] == 2 ? 'selected="selected"' : ''; ?>>
						<?php echo __('Featured (promote to front page)'); ?>
					</option>
					<option value="1" <?php echo $info['ispublished'] == 1 ? 'selected="selected"' : ''; ?>>
						<?php echo __('Public').' '.__('(publish)'); ?>
					</option>
					<option value="0" <?php echo !$info['ispublished'] ? 'selected="selected"' : ''; ?>>
						<?php echo __('Internal').' '.('(private)'); ?>
					</option>
				</select>
			<?php if(!empty($errors['ispublished'])){ ?>
					<div class="alert-danger"><?php echo $errors['ispublished']; ?></div>
			<?php } ?>
				
			</div>
		</div>


	<div style="margin-top:25px"></div>

	<ul class="nav nav-pills">
		<li class="active"><a href="#article" data-toggle="tab"><?php echo __('Article Content'); ?></a></li>
		<li><a href="#attachments" data-toggle="tab"><?php echo __('Attachments') . sprintf(' (%d)',
			$faq ? count($faq->attachments->getSeparates('')) : 0); ?></a></li>
		<li><a href="#notes" data-toggle="tab"><?php echo __('Internal Notes'); ?></a></li>
	</ul>
	<div class="tab-content">
	<div class="tab-pane active in" id="article">
	<label>
	<?php echo __('Knowledgebase Article Content'); ?><br/>
	<small class="text-muted"><?php echo __('Here you can manage the question and answer for the article. Multiple languages are available if enabled in the admin panel.'); ?></small>
	</label>
	<div class="clear"></div>

	<?php
	$langs = Internationalization::getConfiguredSystemLanguages();
	if ($faq && count($langs) > 1) { ?>
		<ul class="tabs alt clean" id="trans" style="margin-top:10px;">
			<li class="empty"><i class="fa fa-globe" title="This content is translatable"></i></li>
	<?php foreach ($langs as $tag=>$i) {
		list($lang, $locale) = explode('_', $tag);
	 ?>
		<li class="<?php if ($tag == $cfg->getPrimaryLanguage()) echo "active";
			?>"><a href="#lang-<?php echo esc_attr($tag); ?>" title="<?php
			echo Internationalization::getLanguageDescription($tag);
		?>"><span class="flag flag-<?php echo strtolower($i['flag'] ?: $locale ?: $lang); ?>"></span>
		</a></li>
	<?php } ?>
		</ul>
	<?php
	} ?>

	<div id="trans_container">
	<?php foreach ($langs as $tag=>$i) {
		$code = $i['code'];
		if ($tag == $cfg->getPrimaryLanguage()) {
			$namespace = $faq ? $faq->getId() : false;
			$answer = $info['answer'];
			$question = $info['question'];
			$qname = 'question';
			$aname = 'answer';
		}
		else {
			$namespace = $faq ? $faq->getId() . $code : $code;
			$answer = $info['trans'][$code]['answer'];
			$question = $info['trans'][$code]['question'];
			$qname = 'trans['.$code.'][question]';
			$aname = 'trans['.$code.'][answer]';
		}
	?>
		<div class="tab_content <?php
			if ($code != $cfg->getPrimaryLanguage()) echo "hidden";
		 ?>" id="lang-<?php echo esc_attr($tag); ?>"
	<?php if ($i['direction'] == 'rtl') echo 'dir="rtl" class="rtl"'; ?>
		>
		<br>
		<div class="row">
			<div class="col-sm-6">
				<label class="required">
				 <?php echo __('Question');?>:&nbsp;
				</label>
			<input class="form-control" type="text" name="<?php echo esc_attr($qname); ?>" value="<?php echo esc_attr($question); ?>">
			<?php if(!empty($errors['question'])){ ?>
					<div class="alert-danger"><?php echo $errors['question']; ?></div>
			<?php } ?>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">	
				<label class="required">
				 <?php echo __('Answer');?> &nbsp;
				</label>
			<?php if(!empty($errors['answer'])){ ?>
					<div class="alert-danger"><?php echo $errors['answer']; ?></div>
			<?php } ?>
			<textarea name="<?php echo esc_attr($aname); ?>"
					class="summernote-base draft" <?php
			list($draft, $attrs) = Draft::getDraftAndDataAttrs('faq', $namespace, $answer);
			echo $attrs; ?>><?php echo $draft ?: $answer;
					?></textarea>
				</div>
			</div>
		</div>
	<?php } ?>
		</div>
	</div>

	<div class="tab-pane fade" id="attachments">
		  <label><?php echo __('Common Attachments'); ?>
			<div class="text-muted"><?php echo __(
				'These attachments are always available, regardless of the language in which the article is rendered'
			); ?></div>
			<?php if(!empty($errors['files'])){ ?>
				<div class="alert-danger"><?php echo $errors['files']; ?></div>
			<?php } ?>
			<div style="margin-top:15px"></div>
		</label>
		<div class="col-md-10">
		<?php
		print $faq_form->getField('attachments')->render(); ?> </div>

	<?php if (count($langs) > 1) { ?>
		<div style="margin-top:15px"></div>
		<strong><?php echo __('Language-Specific Attachments'); ?></strong>
		<div><?php echo __(
			'These attachments are only available when article is rendered in one of the following languages.'
		); ?></div>
		<?php if(!empty($errors['files'])){ ?>
				<div class="alert-danger"><?php echo $errors['files']; ?></div>
			<?php } ?>
		<div style="margin-top:15px"></div>

		<ul class="tabs alt clean">
			<li class="empty"><i class="fa fa-globe" title="This content is translatable"></i></li>
	<?php foreach ($langs as $lang=>$i) { ?>
			<li class="<?php if ($i['code'] == $cfg->getPrimaryLanguage()) echo 'active';
	?>"><a href="#attachments-<?php echo esc_attr($i['code']); ?>">
		<span class="flag flag-<?php echo esc_attr($i['flag']); ?>"></span>
		</a></li>
	<?php } ?>
		</ul>
	<?php foreach ($langs as $lang=>$i) {
		$code = $i['code']; ?>
		<div class="tab_content" id="attachments-<?php echo esc_attr($i['code']); ?>" <?php if ($i['code'] != $cfg->getPrimaryLanguage()) echo 'style="display:none;"'; ?>>
		<div style="padding:0 0 9px">
			<strong><?php echo sprintf(__(
				/* %s is the name of a language */ 'Attachments for %s'),
				Internationalization::getLanguageDescription($lang));
			?></strong>
		</div>
		<?php
		print $faq_form->getField('attachments.'.$code)->render();
		?></div><?php
		}
	} ?>
	<div class="clear"></div>
	</div>

	<div class="tab-pane fade" id="notes">
		<div class="panel-heading">
			<a class="internal_note"><div class="panel-title table-caption">
				<i class="fa fa-plus-square"></i>&nbsp;<?php echo __('Internal Notes');?>:&nbsp;
				<small class="text-muted"><em><?php echo __("Be liberal, they're internal");?></em></small></div>
			</a>
		</div>	
		<div class="panel-body notes" style="display:none;">
			<textarea class="summernote-base" name="notes"><?php echo $info['notes']; ?></textarea>
		</div>
	</div>
	</div>
	<div class="form-group" style="text-align:center; margin-top:20px;">
		<input type="submit" class="btn btn-success" name="submit" value="<?php echo esc_attr($submit_text); ?>">
		<input type="reset" class="btn btn-info" name="reset"  value="<?php echo __('Reset'); ?>" onclick="javascript:
			$(this.form).find('textarea.richtext')
				.redactor('deleteDraft');
			location.reload();" />
		<input type="button" class="btn" name="cancel" value="<?php echo __('Cancel'); ?>" onclick='window.location.href="faq.php?<?php echo $qstr; ?>"'>
	</div>
</div>
</div>
</form>
