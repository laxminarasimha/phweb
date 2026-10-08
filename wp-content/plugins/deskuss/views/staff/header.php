<?php
if (!headers_sent()) {
    header("Content-Type: text/html; charset=UTF-8");
    header("X-Frame-Options: SAMEORIGIN");
}

$title = ($dsk && ($title=$dsk->getPageTitle()))
    ? $title : ('Deskuss - '.__('Staff Control Panel'));

if (!isset($_SERVER['HTTP_X_PJAX'])) { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html<?php
if (($lang = Internationalization::getCurrentLanguage())
        && ($info = Internationalization::getLanguageInfo($lang))
        && (($info['direction'] ?? '') == 'rtl'))
    echo ' dir="rtl" class="rtl"';
if ($lang) {
    echo ' lang="' . esc_attr(Internationalization::rfc1766($lang)) . '"';
}
?>>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="x-pjax-version" content="<?php echo esc_attr(GIT_VERSION); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title><?php echo Format::htmlchars($title); ?></title>
	
    <link href="<?php echo esc_attr(DESKUSS_MEDIA_URL) ?>/css/open-sans.css?<?php echo esc_attr(THIS_VERSION);?>" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo esc_attr(DESKUSS_MEDIA_URL) ?>/css/font-awesome.min.css?<?php echo esc_attr(THIS_VERSION);?>" media="all"/>
    <link href="<?php echo esc_attr(DESKUSS_MEDIA_URL) ?>/css/ionicons.min.css?<?php echo esc_attr(THIS_VERSION);?>" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL) ?>/css/bootstrap.min.css?<?php echo esc_attr(THIS_VERSION);?>" class="px-demo-stylesheet-core" media="all"/>
    <link rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL) ?>/css/default.min.css?<?php echo esc_attr(THIS_VERSION);?>" class="px-demo-stylesheet-theme" media="all"/>
    <link rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL) ?>/css/pixeladmin.min.css?<?php echo esc_attr(THIS_VERSION);?>" class="px-demo-stylesheet-bs" media="all"/>
    <link rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL) ?>/css/widgets.min.css?<?php echo esc_attr(THIS_VERSION);?>" class="px-demo-stylesheet-widgets" media="all"/>
    <link rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL) ?>/css/admin/deskuss.css?<?php echo esc_attr(THIS_VERSION);?>" media="all"/>
    <link rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL) ?>/css/admin/nivo-lightbox.css?<?php echo esc_attr(THIS_VERSION);?>" media="all"/>
    <link type="text/css" rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/css/flags.css?<?php echo esc_attr(THIS_VERSION);?>"/>
	
	
    <!-- <link rel="stylesheet" href="<?php echo DESKUSS_ROOT_PATH; ?>assets/css/redactor.css?035fd0a" media="screen"/>
    <link rel="stylesheet" href="<?php echo DESKUSS_ROOT_PATH ?>assets/admin/css/typeahead.css?035fd0a" media="screen"/>
    <link type="text/css" rel="stylesheet" href="<?php echo DESKUSS_ROOT_PATH; ?>assets/css/loadingbar.css?035fd0a"/>
    <link type="text/css" rel="stylesheet" href="<?php echo DESKUSS_ROOT_PATH ?>assets/admin/css/translatable.css?035fd0a"/> -->
	
	<!-- Load jQuery and jQuery UI from WordPress -->
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/jquery.min.js')); ?>"></script>
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/jquery-migrate.min.js')); ?>"></script>
	<script type="text/javascript">var $ = jQuery;</script>
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/ui/core.min.js')); ?>"></script>
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/ui/mouse.min.js')); ?>"></script>
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/ui/datepicker.min.js')); ?>"></script>
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/ui/draggable.min.js')); ?>"></script>
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/ui/sortable.min.js')); ?>"></script>
  
    <!-- Load tinymce -->
    <script type="text/javascript" src="<?php echo esc_url(includes_url('js/tinymce/tinymce.min.js')); ?>"></script>
    <?php
    if($dsk && ($headers=$dsk->getExtraHeaders())) {
        echo "\n\t".implode("\n\t", $headers)."\n";
		
		// This is required to load help tip yaml file
		foreach($headers as $kh => $hv){
			if(preg_match('/name\=\"tip-namespace/is', $hv)){
				preg_match('/content\=\"(.*?)\"/', $hv, $matches);
				$tip_namespace = $matches[1];
				
				require_once(INCLUDE_DIR.'class.i18n.php');
				$lang = Internationalization::getCurrentLanguage();

				$i18n = new Internationalization($lang);
				$tips = $i18n->getTemplate("help/tips/$tip_namespace.yaml");
				
				if(!empty($tips)){
					$help_tips = $tips->getData();
				}
				
				if(!empty($help_tips)){
					// Translate links to the root path of this installation
					foreach ($help_tips as $tip=>&$info) {
						if ($dsk)
							$info = $dsk->replaceTemplateVariables($info, array(
								'config'=>$dsk->getConfig()));
						if (isset($info['links']))
							foreach ($info['links'] as &$l)
								if ($l['href'][0] == '/')
									$l['href'] = DESKUSS_ROOT_PATH.substr($l['href'],1);
					}

					echo '<script>
					help_tips = '.json_encode($help_tips).';
					</script>';
				}
			}
		}
    }
    ?>
    <?php
      wp_enqueue_style('wp-color-picker');
    ?>
</head>
<body>

  <script>var pxInit = [];</script>

  <nav class="px-nav px-nav-left" id="px-nav">
    <button type="button" class="px-nav-toggle" data-toggle="px-nav">
      <span class="px-nav-toggle-arrow"></span>
      <span class="navbar-toggle-icon"></span>
      <span class="px-nav-toggle-label font-size-11">HIDE MENU</span>
    </button>

    <ul class="px-nav-content">
      <li class="px-nav-box p-a-3 b-b-1" id="px-nav-box">
        <img src="<?php echo esc_url(DESKUSS_MEDIA_URL.'/images/avatars/1.jpg'); ?>"  alt="" class="pull-xs-left m-r-2 border-round" style="width: 30px; height: 30px;">
        <div class="font-size-14"><span class="font-weight-light"></span><?php echo sprintf(__('Welcome, %s'), '<strong>'.esc_html($thisstaff->getFirstName()).'</strong>'); ?></div>
      </li>

      <?php include STAFFINC_DIR . "templates/navigation.tmpl.php"; ?>
    </ul>
  </nav>

  <nav class="navbar px-navbar">
    <!-- Header -->
    <div class="navbar-header">
	<a class="navbar-brand px-demo-brand" href="index.php" style="display: flex; align-items: center;"><img src="<?php echo esc_url(DESKUSS_ROOT_PATH); ?>admin/logo.php" alt="deskuss-logo" class="pull-xs-left m-r-2" style="height: 43px;"></a>
    </div>

    <!-- Navbar togglers -->
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#px-demo-navbar-collapse" aria-expanded="false"><i class="navbar-toggle-icon"></i></button>


    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="px-demo-navbar-collapse">

      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <img src="<?php echo esc_url(DESKUSS_MEDIA_URL.'/images/avatars/1.jpg'); ?>"  alt="" class="px-navbar-image">
            <span class="hidden-md"><?php echo esc_html($thisstaff->getName()); ?></span>
          </a>
          <ul class="dropdown-menu">
            <li><a href="<?php echo esc_url(DESKUSS_ROOT_PATH) ?>admin/profile.php"><i class="dropdown-icon fa fa-user"></i>&nbsp;&nbsp;<?php echo __('Profile'); ?></a></li>
            <?php if (function_exists('current_user_can') && current_user_can('manage_options')): ?>
            <li><a href="<?php echo esc_url(admin_url()); ?>" target="_blank"><i class="dropdown-icon fa fa-wordpress"></i>&nbsp;&nbsp;<?php echo __('WordPress Admin'); ?></a></li>
            <?php endif; ?>
            <li class="divider"></li>
            <li><a href="<?php echo esc_url(DESKUSS_ROOT_PATH) ?>admin/logout.php?auth=<?php echo esc_attr($dsk->getLinkToken()); ?>"><i class="dropdown-icon fa fa-power-off"></i>&nbsp;&nbsp;<?php echo __('Log Out'); ?></a></li>
          </ul>
        </li>

      </ul>
    </div><!-- /.navbar-collapse -->
  </nav>

  <script>
    pxInit.push(function() {
      $('#navbar-notifications').perfectScrollbar();
      $('#navbar-messages').perfectScrollbar();
    });
  </script>

  <!-- Custom styling -->
  <style>
    .page-header-form .input-group-addon,
    .page-header-form .form-control {
      background: rgba(0,0,0,.05);
    }
	.hide-inputs-without-form input{
		display: none;
	}
  </style>
  <!-- / Custom styling -->

  <div class="px-content">
    <ol class="breadcrumb page-breadcrumb">
		<li><a href="index.php">Home</a></li>
    <?php
	foreach($nav->__get('tabs') as $tab_slug => $tab){
		if(empty($tab['active'])) continue;
		echo '<li><a href="'.esc_url($tab['href']).'">'.esc_html($tab['desc']).'</a></li>';
		break;
	}
	
	if(!empty($nav->getSubNav($tab_slug))){
		foreach($nav->getSubNav($tab_slug) as $menu_id => $submenu){
			if(empty($submenu['active'])) continue;
			echo '<li><a href="'.esc_url($submenu['href']).'">'.esc_html($submenu['desc']).'</a></li>';
			break;
		}
	}
	
	?>
    </ol>
	<?php
    if($dsk->getError())
        echo sprintf('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $dsk->getError());
    elseif($dsk->getWarning())
        echo sprintf('<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $dsk->getWarning());
    elseif($dsk->getNotice())
        echo sprintf('<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-info-circle"></i>&nbsp;&nbsp;%s</div>', $dsk->getNotice());
    ?>
    <div id="pjax-container" class="<?php if (!empty($_POST)) echo 'no-pjax'; ?>">
<?php } else {
    header('X-PJAX-Version: ' . GIT_VERSION);
    if ($pjax = $dsk->getExtraPjax()) { ?>
    <script type="text/javascript">
    <?php foreach (array_filter($pjax) as $s) echo $s.";"; ?>
    </script>
    <?php }
    foreach ($dsk->getExtraHeaders() as $h) {
        if (strpos($h, '<script ') !== false)
            echo $h;
    } ?>
    <title><?php echo Format::htmlchars(($dsk && ($title=$dsk->getPageTitle()))?$title:'Deskuss - '.__('Staff Control Panel')); ?></title><?php
} # endif X_PJAX ?>

		<?php if(!empty($errors)){
			?>
			<div class="alert alert-danger hide-inputs-without-form">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<?php echo esc_html($errors['err']).'<br />'; ?>
				<ul style="margin-top:0px;">
					<?php
						foreach($errors as $ek => $ev){
							
							if($ek == 'err') continue;
							
							if(is_array($ev)){
								
								foreach($ev as $vk => $ve)
									{
										echo '<li>'.$ve.'</li>';
									}
								}
							else{
								echo '<li>'.$ev.'</li>';
							}
						}
					?>
				</ul>
			</div>
		<?php } ?>
         <?php if($msg) { ?>
			<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo $msg; ?></div>
         <?php }elseif($warn) { ?>
			<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;<?php echo $warn; ?></div>
         <?php }
		 
        foreach (Messages::getMessages() as $M) { 
			
			$alert_class = strtolower($M->getLevel());
			$alert_fa_class = 'exclamation-triangle';
			if(strtolower($M->getLevel()) == 'error'){
				$alert_class = 'danger';
			}elseif(strtolower($M->getLevel()) == 'success'){
				$alert_fa_class = 'check';
			}
			?>
			
			<div class="alert alert-<?php echo esc_attr($alert_class); ?>"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-<?php echo esc_attr($alert_fa_class); ?>"></i>&nbsp;&nbsp;<?php echo (string) $M; ?></div>
			
<?php   }
apply_filters('post_load_header', null);
?>