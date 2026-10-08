<?php
if(!defined('DSKSTAFFINC') || !$staff || !$thisstaff) die('Access Denied');
?>

  <style>
 .nav-pills>li.active>a,
.nav-pills>li.active>a:focus,
.nav-pills>li.active>a:hover {
   background-color: #2a94db;
}


</style>
  
 <form action="profile.php" method="post" class="save" autocomplete="off">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="update">
 <input type="hidden" name="id" value="<?php echo $staff->getId(); ?>">
 <div class="panel">
 <div class="panel-heading">
 <div class="panel-title table-caption">
<?php echo __('My Account Profile');?>
</div></div>
<br>
<div class="panel-body">
  <ul class="nav nav-tabs">
    <li class="active"><a href="#account" data-toggle="tab"><?php echo __('Account'); ?></a></li>
    <li class=""><a href="#preferences" data-toggle="tab"><?php echo __('Preferences'); ?></a></li>
    <li class=""><a href="#signature" data-toggle="tab"><?php echo __('Signature'); ?></a></li>
  </ul>
<div class="tab-content">
  <div class="tab-pane fade active in" id="account">
    <div class="row">
		<div class="col-md-4 col-lg-3">
			<div class="panel-body text-xs-center">
				<div class="avatar" style="display: inline-grid;">
			 <?php    echo   $avatar = $staff->getAvatar();
						
			if ($avatar->isChangeable()) { ?>
				  
			 <a class="btn btn-primary no-pjax" id="avatar"
				  href="#ajax.php/staff/<?php echo $staff->getId(); ?>/avatar/change"
							onclick="javascript:
				event.preventDefault();
				var $a = $(this),
					form = $a.closest('form');
				$.ajax({
				  url: $a.attr('href').substr(1),
				  dataType: 'json',
				  success: function(json) {
					if (!json || !json.code)
					  return;
					var code = form.find('[name=avatar_code]');
					if (!code.length)
					  code = form.append($('<input>').attr({type: 'hidden', name: 'avatar_code'}));
					code.val(json.code).trigger('change');
					$a.closest('.avatar').find('img').replaceWith($(json.img));
				  }
				});
				return false;"><i class="fa fa-retweet"></i></a>
				
			<?php
			} ?>
			</div>     
			</div>
		</div>
		<div class="col-md-8 col-lg-9">
            <form>
            <div class="row">
                <div class="col-sm-6 form-group">
                  <label style="color:#696969"><?php echo __('Name'); ?>:</label>
                  <input type="text" class="form-control" name="firstname" 
              autofocus value="<?php echo Format::htmlchars($staff->firstname); ?>"
              placeholder="<?php echo __("First Name"); ?>">
			  <?php
				if(!empty($errors['firstname'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['firstname'].'</div>';
				}
				?>
			</div>	
                <div class="col-sm-6 form-group">
			     <label style="color:#696969"><?php echo __('Last Name'); ?>:</label>
				<input type="text" class="form-control" name="lastname" 
              autofocus value="<?php echo Format::htmlchars($staff->lastname); ?>"
              placeholder="<?php echo __("Last Name"); ?>">
			  <?php
				if(!empty($errors['lastname'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['lastname'].'</div>';
				}
				?>
                </div>
			</div>
			  
              <div class="row">
                <div class="col-sm-6 form-group">
                  <label style="color:#696969"><?php echo __('Email Address'); ?>:</label>
                  <input type="text" class="form-control"
			name="email"	value="<?php echo Format::htmlchars($staff->email); ?>"	placeholder="<?php echo __('e.g. me@mycompany.com'); ?>">
			 <?php
				if(!empty($errors['email'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['email'].'</div>';
				}
				?>
			  </div>
				<div class="col-sm-6 form-group">
                  <label style="color:#696969"><?php echo __('Mobile Number'); ?>:</label>
                  <input type="tel" class="form-control"
			name="mobile"
              value="<?php echo Format::htmlchars($staff->mobile); ?>">
			  <?php
				if(!empty($errors['mobile'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['mobile'].'</div>';
				}
				?>
                </div>            
			</div>	
			
			<div class="row">
                <div class="col-sm-6 form-group">
                  <label style="color:#696969"><?php echo __('Phone Number');?>:</label>
                  <input type="text" class="form-control" 
		name="phone" value="<?php echo Format::htmlchars($staff->phone); ?>">
				<?php
					if(!empty($errors['phone'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['phone'].'</div>';
				}
				?>
                </div>
            
                <div class="col-sm-6 form-group">
                  <label style="color:#696969"> <?php echo __('Ext');?> </label>
				<input type="tel" class="form-control" 
		name="phone_ext" value="<?php echo Format::htmlchars($staff->phone_ext); ?>">
				<?php
					if(!empty($errors['phone_ext'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['phone_ext'].'</div>';
				}
				?>
                </div>            
			</div>
		</form>
      </div>
	  </div>
	
      <!-- ================================================ -->
     <br>
	   <div class="panel-heading">
	   <div class="panel-title table-caption">
		
			<?php echo __('Authentication'); ?>
		</div></div>
         
		<div class="panel-body">
        <?php if ($bk = $staff->getAuthBackend()) { ?>
       
          <div><?php echo __("Backend"); ?></div>
          <div><?php echo $bk->getName(); ?></div>
       
        <?php } ?>
		<div class="row">
		<div class="col-offset-xs-1 col-md-2 form-group">
          <label class="required">
		  <?php echo __('Username'); ?>:&nbsp;
            <i class="fa fa-question-circle" href="#username"></i>
		</label></div>
		
          <div class="col-md-4 form-group">
            <input type="text" 
              class="staff-username typeahead form-control"
              name="username" disabled value="<?php echo Format::htmlchars($staff->username); ?>">
			<?php
				if(!empty($errors['username'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['username'].'</div>';
				}
				?>	
			  </div>
			<?php if (!$bk || $bk->supportsPasswordChange()) { ?>
				<div class="col-md-4 form-group">
				<input type="button" data-toggle="modal" data-target="#myModal" class="btn btn-primary action-button" id="change-pw-button" onclick="javascript:
				$.dialog('ajax.php/staff/'+<?php echo $staff->getId(); ?>+'/change-password', 201);" value="
				<?php echo __('Change Password'); ?>">
				</div>
					   
			<?php } ?>
			   </div>
   </div>
    
      <!-- ================================================ -->
	<div class="panel-heading">
	   <div class="panel-title table-caption">
		  <?php echo __('Status and Settings'); ?>
		  </div></div>
		  
          <div class="panel-body">

            <input type="checkbox" name="show_assigned_tickets"
              <?php echo $cfg->showAssignedTickets() ? 'disabled="disabled" ' : ''; ?>
              <?php echo $staff->show_assigned_tickets ? 'checked="checked"' : ''; ?> />
				  <span class="checkmark"></span>
			  <?php echo __('Show assigned tickets on open queue.'); ?>
            <i class="help-tip fa fa-question-circle" href="#show_assigned_tickets"></i>
             <br>
            <input type="checkbox" name="onvacation"
              <?php echo ($staff->onvacation) ? 'checked="checked"' : ''; ?> />
			    <span class="checkmark"></span>
              <?php echo __('Vacation Mode'); ?>
            <br/>
       </div>
     
  </div>

  <!-- =================== PREFERENCES ======================== -->

  <div class="tab-pane fade" id="preferences">
	 <div class="panel-heading">
	 <div class="panel-title table-caption">
		<?php echo __('Preferences'); ?>:&nbsp;
			<em><small class="text-muted">
			<?php echo __(
            "Profile preferences and settings"
          ); ?>
            </small></em>
       </div></div>   
       
	   <div class="panel-body">
	   <div class="col-md-6">
		<div class="form-group">
            <label class="control-label"><?php echo __('Maximum Page size');?>:</label>
			<div class="input-group">
				<select class="form-control" name="max_page_size">
                    <option value="0">&mdash; <?php echo __('System Default');?> &mdash;</option>
                    <?php
                    $pagelimit = $staff->max_page_size ?: $cfg->getPageSize();
                    for ($i = 5; $i <= 50; $i += 5) {
                        $sel=($pagelimit==$i)?'selected="selected"':'';
                         echo sprintf('<option value="%d" %s>'.__('show %s records').'</option>',$i,$sel,$i);
                    } ?>
                </select>
				<div class="input-group-addon">	
					<?php echo __('per page.');?>
				</div>
			</div>
		</div>
		<div class="form-group">
            <label class="control-label"><?php echo __('Auto Refresh Rate');?>:</label>
              <em><small class="text-muted"><?php echo __('Tickets page refresh rate in minutes.'); ?></small></em>
				<select class="form-control" name="auto_refresh_rate">
                  <option value="0">&mdash; <?php echo __('Disabled');?> &mdash;</option>
                  <?php
                  $y=1;
                   for($i=1; $i <=30; $i+=$y) {
                     $sel=($staff->auto_refresh_rate==$i)?'selected="selected"':'';
                     echo sprintf('<option value="%d" %s>%s</option>', $i, $sel,
                        sprintf(_N('Every minute', 'Every %d minutes', $i), $i));
                     if($i>9)
                        $y=2;
                   } ?>
                </select>
            </div>
		<div class="form-group">
            <label class="control-label"><?php echo __('Default From Name');?>:</label>
              <em><small class="text-muted"><?php echo __('From name to use when replying to a thread');?></small></em>
			<select class="form-control" name="default_from_name">
                  <?php
                   $options=array(
                           'email' => __("Email Address Name"),
                           'dept' => sprintf(__("Department Name (%s)"),
                               __('if public' /* This is used in 'Department's Name (>if public<)' */)),
                           'mine' => __('My Name'),
                           '' => '— '.__('System Default').' —',
                           );
                  if ($cfg->hideStaffName())
                    unset($options['mine']);

                  foreach($options as $k=>$v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $k,($staff->default_from_name==$k)?'selected="selected"':'',$v);
                  }
                  ?>
                </select>
				<?php
				if(!empty($errors['default_from_name'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['default_from_name'].'</div>';
				}
				?>
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label class="control-label"><?php echo __('Thread View Order');?>:</label>
              <em><small class="text-muted"><?php echo __('The order of thread entries');?></small></em>
			<select class="form-control" name="thread_view_order">
                  <?php
                   $options=array(
                           'desc' => __('Descending'),
                           'asc' => __('Ascending'),
                           '' => '— '.__('System Default').' —',
                           );
                  foreach($options as $k=>$v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $k
                                ,($staff->thread_view_order == $k) ? 'selected="selected"' : ''
                                ,$v);
                  }
                  ?>
                </select>
				<?php
				if(!empty($errors['thread_view_order'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['thread_view_order'].'</div>';
				}
				?>
            </div>
		<div class="form-group">
            <label class="control-label"><?php echo __('Default Signature');?>:</label>
              <em><small class="text-muted"><?php echo __('This can be selected when replying to a thread');?></small></em>
			<select class="form-control" name="default_signature_type">
                  <option value="none" selected="selected">&mdash; <?php echo __('None');?> &mdash;</option>
                  <?php
                   $options=array('mine'=>__('My Signature'),'dept'=>sprintf(__('Department Signature (%s)'),
                       __('if set' /* This is used in 'Department Signature (>if set<)' */)));
                  foreach($options as $k=>$v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $k,($staff->default_signature_type==$k)?'selected="selected"':'',$v);
                  }
                  ?>
                </select>
				<?php
				if(!empty($errors['default_signature_type'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['default_signature_type'].'</div>';
				}
				?>
		</div>
	</div>	
		<div class="panel-heading">
			<div class="panel-title table-caption"><?php echo __('Localization'); ?>
			</div>
		</div>
		<div class="panel-body"> 
			<div class="row col-md-7">
				<div class="col-md-3 form-group">
					<label class="control-label"><?php echo __('Time Zone');?>:</label></div>
						<?php
						$TZ_NAME = 'timezone';
						$TZ_TIMEZONE = $staff->timezone;
						include STAFFINC_DIR.'templates/timezone.tmpl.php'; ?>
						<?php
						if(!empty($errors['timezone'])){
							echo '<div class="alert-danger">&nbsp;'.$errors['timezone'].'</div>';
						}
						?>
			</div>
			<div class="row form-inline col-md-5 pull-right">
				<span class="col-md-4">
					<label class="control-label"><?php echo __('Time Format');?>:</label></span>
					   <span class="">
					<select class="form-control" name="datetime_format">
			<?php
				$datetime_format = $staff->datetime_format;
				foreach (array(
				'relative' => __('Relative Time'),
				'' => '— '.__('System Default').' —',
			) as $v=>$name) { ?>
								<option value="<?php echo $v; ?>" <?php
								if ($v == $datetime_format)
									echo 'selected="selected"';
								?>><?php echo $name; ?></option>
			<?php } ?>
					</select>
				</span>
			</div>
		</div>
<?php if ($cfg->getSecondaryLanguages()) { ?>
        <div class="row">
		 <div class="col-md-4">
            <div><?php echo __('Preferred Language'); ?>:</div></div>
            <div class="col-md-8">
        <?php
        $langs = Internationalization::getConfiguredSystemLanguages(); ?>
				
			<select class="form-control" name="lang" style="width:40%;margin:10px">
                    <option value="">&mdash; <?php echo __('Use Browser Preference'); ?> &mdash;</option>
<?php foreach($langs as $l) {
    $selected = ($staff->lang == $l['code']) ? 'selected="selected"' : ''; ?>
                    <option value="<?php echo $l['code']; ?>" <?php echo $selected;
                        ?>><?php echo Internationalization::getLanguageDescription($l['code']); ?></option>
<?php } ?>
                </select>
				<?php
				if(!empty($errors['lang'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['lang'].'</div>';
				}
				?>
            </div>
        </div>
<?php } ?>
<?php if (extension_loaded('intl')) { ?>
        <div class="row">
            <div><?php echo __('Preferred Locale');?>:</div>
           
			<select class="form-control" name="locale" style="width:40%;margin:10px">

			
                    <option value=""><?php echo __('Use Language Preference'); ?></option>
<?php foreach (Internationalization::allLocales() as $code=>$name) { ?>
                    <option value="<?php echo $code; ?>" <?php
                        if ($code == $staff->locale)
                            echo 'selected="selected"';
                    ?>><?php echo $name; ?></option>
<?php } ?>
                </select>
            </div>
        
<?php } ?>
    
  </div>

  <!-- ==================== SIGNATURES ======================== -->

	<div id="signature" class="tab-pane fade">
		<div class="panel-heading">
			<div class="panel-title table-caption">  
				<?php echo __('Signature'); ?>:<em><small class="text-muted">
			  <?php echo __(
				"Optional signature used on outgoing emails.")
				.' '.
				__('Signature is made available as a choice, on ticket reply.'); ?> </small></em>
			</div>
		</div>
		<div class="panel-body">
			<textarea class="summernote-base" name="signature"><?php echo $staff->signature; ?></textarea>
		</div>
	</div>
</div>

	<br>
	<p style="text-align:center">
	<input type="submit" class="btn btn-success" name="submit" value="
	<?php echo __('Save Changes'); ?>" >
    <input type="reset" class="btn btn-info"  name="reset" value="<?php echo __('Reset');?>
">
    <button type="button" class="btn" name="cancel" onclick="window.history.go(-1);"><?php echo __('Cancel');?></button>
  </p>
   </div>
   </div> 
</form>
<?php
if ($staff->change_passwd) { ?>
<script type="text/javascript">
    $(function() { $('#change-pw-button').trigger('click'); });
</script>
<?php
}
