<?php
$title=($cfg && is_object($cfg) && $cfg->getTitle())
    ? $cfg->getTitle() : 'Deskuss - '.__('Support Ticket System');
$signin_url = DESKUSS_ROOT_PATH . "login.php"
    . ($thisclient ? "?e=".urlencode($thisclient->getEmail()) : "");
$signout_url = DESKUSS_ROOT_PATH . "logout.php?auth=".$dsk->getLinkToken();

if (!headers_sent()) {
    header("Content-Type: text/html; charset=UTF-8");
    header("X-Frame-Options: SAMEORIGIN");
    if (($lang = Internationalization::getCurrentLanguage())) {
        $langs = array_unique(array($lang, $cfg->getPrimaryLanguage()));
        $langs = Internationalization::rfc1766($langs);
        header("Content-Language: ".implode(', ', $langs));
    }
}
?>
<!DOCTYPE html>
<html<?php
if ($lang
        && ($info = Internationalization::getLanguageInfo($lang))
        && (!empty($info['direction']) && $info['direction'] == 'rtl'))
    echo ' dir="rtl" class="rtl"';
if ($lang) {
    echo ' lang="' . esc_attr($lang) . '"';
}
?>>

<head>
	
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="description" content="customer support platform">
    <meta name="keywords" content="Deskuss, Customer support system, support ticket system, helpdesk">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title><?php echo Format::htmlchars($title); ?></title>
	
<link href="<?php echo esc_attr(DESKUSS_MEDIA_URL); ?>/css/open-sans.css?<?php echo esc_attr(THIS_VERSION);?>" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo esc_attr(DESKUSS_MEDIA_URL); ?>/css/font-awesome.min.css?<?php echo esc_attr(THIS_VERSION);?>" media="all"/>
    <link href="<?php echo esc_attr(DESKUSS_MEDIA_URL); ?>/css/ionicons.min.css?<?php echo esc_attr(THIS_VERSION);?>" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/css/bootstrap.min.css?<?php echo esc_attr(THIS_VERSION);?>" class="px-demo-stylesheet-core" media="all"/>
    <link rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/css/default.min.css?<?php echo esc_attr(THIS_VERSION);?>" class="px-demo-stylesheet-theme" media="all"/>
    <link rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/css/pixeladmin.min.css?<?php echo esc_attr(THIS_VERSION);?>" class="px-demo-stylesheet-bs" media="all"/>
    <link rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/css/widgets.min.css?<?php echo esc_attr(THIS_VERSION);?>" class="px-demo-stylesheet-widgets" media="all"/>
    <link rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/css/deskuss.css?<?php echo esc_attr(THIS_VERSION);?>" media="all"/>

    <link type="text/css" rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/css/flags.css?<?php echo esc_attr(THIS_VERSION);?>"/>
	
	<!-- Load jQuery and jQuery UI from WordPress -->
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/jquery.min.js')); ?>"></script>
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/jquery-migrate.min.js')); ?>"></script>
	<script type="text/javascript">var $ = jQuery;</script>
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/ui/core.min.js')); ?>"></script>
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/ui/mouse.min.js')); ?>"></script>
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/ui/datepicker.min.js')); ?>"></script>
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/ui/draggable.min.js')); ?>"></script>
	<script type="text/javascript" src="<?php echo esc_url(includes_url('js/jquery/ui/sortable.min.js')); ?>"></script>
	<script>var pxInit = [];</script>
	
    <?php
    if($dsk && ($headers=$dsk->getExtraHeaders())) {
        echo "\n\t".implode("\n\t", $headers)."\n";
    }

    // Offer alternate links for search engines
    // @see https://support.google.com/webmasters/answer/189077?hl=en
    if (($all_langs = Internationalization::getConfiguredSystemLanguages())
        && (count($all_langs) > 1)
    ) {
        $langs = Internationalization::rfc1766(array_keys($all_langs));
        $qs = array();
        parse_str($_SERVER['QUERY_STRING'], $qs);
        foreach ($langs as $L) {
            $qs['lang'] = $L; ?>
        <link rel="alternate" href="//<?php echo esc_url( $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); ?>?<?php
            echo http_build_query($qs); ?>" hreflang="<?php echo esc_attr($L); ?>" />
<?php
        } ?>
        <link rel="alternate" href="//<?php echo esc_url( $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); ?>"
            hreflang="x-default" />
<?php
    }
    ?>
<style>

h1, h2, h3, h4, h5, h6{
	margin:0px;
}
.clear{
	clear: both;
}
#kb{
padding-inline-start:0px;
}
#kb li{
	
	list-style:none;
}
.thread-entry > .avatar {
    display: inline-block;
    width: 48px;
    height: auto;
    border-radius: 5px;
}
.avatar > img.avatar {
    width: 100%;
    height: auto;
}
.thread-event img.avatar {
    vertical-align: middle;
    border-radius: 3px;
    width: auto;
    max-height: 24px;
    margin: -3px 3px 0;
}
.type-icon {
    border-radius: 8px;
    background-color: #f4f4f4;
    padding: 0px 6px;
    margin-right: 5px;
    text-align: center;
    display: inline-block;
    font-size: 1.1em;
    border: 1px solid #eee;
    vertical-align: top;
}
.thread-event {
    padding: 0px 2px 15px;
    margin-left: 60px;
}
#faq ol li {
    list-style: none;
    margin: 0;
    padding: 0;
    color: #999;
}
#faq ol{
   padding-left: 15px;
}
</style>	
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
			<img src="<?php echo esc_url(DESKUSS_MEDIA_URL.'/images/avatars/1.jpg'); ?>" alt="" class="pull-xs-left m-r-2 border-round" style="width: 30px; height: 30px;">
			<div class="font-size-14"><span class="font-weight-light"></span>
				 <?php
				if ($thisclient && is_object($thisclient) && $thisclient->getId()
					&& !$thisclient->isGuest()) {
					 echo Format::htmlchars($thisclient->getName());
					 ?>
				<?php
				} elseif($nav) {
					if ($cfg->getClientRegistrationMode() == 'public') { ?>
						<?php echo __('Guest User'); ?><?php
					}
				} ?>
			</div>
		</li>
		<?php
		if($nav && ($navs=$nav->getNavLinks()) && is_array($navs)){
			foreach($navs as $name =>$nav) {
				$active = !empty($nav['active']) ? 'active' : '';
				$count_html = !empty($nav['count']) ? '<span class="label label-'.(!empty($nav['count_class']) ? $nav['count_class'] : 'info').'"> '.$nav['count'].'</span>' : '';
				echo sprintf('<li class="px-nav-item %s"><a class="%s" href="%s"><i class="px-nav-icon %s"></i><span class="px-nav-label">%s'.$count_html.'</span></a></li>%s', $active, $name, (DESKUSS_ROOT_PATH.$nav['href']), $nav['icon'], $nav['desc'], "\n");
			}
		} ?>
    </ul>
  </nav>

  <nav class="navbar px-navbar">
    <!-- Header -->
    <div class="navbar-header">
	<!-- <a class="navbar-brand px-demo-brand" href="index.php"><img src="../../admin/images/deskuss-logo.png" alt="deskuss-logo" class="pull-xs-left m-r-2" style="width: 160px; height: 45px;"></a> -->
	
	<a class="navbar-brand px-demo-brand" id="header_logo" href="<?php echo esc_url(DESKUSS_ROOT_PATH); ?>index.php"
	title="<?php echo $dsk->getConfig()->getTitle(); ?>">
		<img class="pull-xs-left m-r-2" src="<?php echo esc_url(DESKUSS_ROOT_PATH); ?>logo.php" alt="<?php
		echo $dsk->getConfig()->getTitle(); ?>">
	</a>
    </div>

    <!-- Navbar togglers -->
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#px-demo-navbar-collapse" aria-expanded="false"><i class="navbar-toggle-icon"></i></button>

	<?php
	if ($thisclient && is_object($thisclient) && $thisclient->getId()
                    && !$thisclient->isGuest()) { ?>
    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="px-demo-navbar-collapse">

      <ul class="nav navbar-nav navbar-right">
		<?php
			if ($cfg->getClientRegistrationMode() != 'disabled'
				|| !$cfg->isClientLoginRequired()) { ?>
					<li><a href="<?php echo esc_url(DESKUSS_ROOT_PATH); ?>open.php"><i class="fa fa-plus">&nbsp;&nbsp;</i><?php
						echo __('New Ticket');?></a></li>
		<?php } ?>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <img src="<?php echo esc_url(DESKUSS_MEDIA_URL.'/images/avatars/1.jpg'); ?>"  alt="" class="px-navbar-image">
            <span class="hidden-md"><?php echo esc_html($thisclient->getName()); ?></span>
          </a>
          <ul class="dropdown-menu">
			<li><a href="<?php echo esc_url(DESKUSS_ROOT_PATH); ?>profile.php"><i class="dropdown-icon fa fa-user"></i>&nbsp;&nbsp;<?php echo __('Profile'); ?></a></li>
			<li><a href="<?php echo esc_url(DESKUSS_ROOT_PATH); ?>tickets.php"><i class="dropdown-icon fa fa-ticket"></i>&nbsp;&nbsp;<?php echo sprintf(__('Tickets '), $thisclient->getNumTickets()); ?><span class="label label-info pull-xs-right"><?php echo esc_html($thisclient->getNumTickets()); ?></span></a></li>
			<li class="divider"></li>
			<li><a href="<?php echo esc_url($signout_url); ?>"><i class="dropdown-icon fa fa-power-off"></i>&nbsp;&nbsp;<?php echo __('Log Out'); ?></a></li>
          </ul>
        </li>

      </ul>
    </div><!-- /.navbar-collapse -->
	<?php 
	}else{ ?>
	
    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="px-demo-navbar-collapse">
      <ul class="nav navbar-nav navbar-right">
		<?php
			if ($cfg->getClientRegistrationMode() != 'disabled'
				|| !$cfg->isClientLoginRequired()) { ?>
					<li><a href="open.php"><i class="fa fa-plus">&nbsp;&nbsp;</i><?php
						echo __('New Ticket');?></a></li>
		<?php } ?>
		<?php if ($cfg->getClientRegistrationMode() != 'disabled') { ?>
			<li><a href="<?php echo esc_url($signin_url); ?>"><i class="fa fa-sign-in">&nbsp;&nbsp;</i><?php echo __('Sign In'); ?></a></li>
			<?php
			if ($cfg && $cfg->isClientRegistrationEnabled()) {
			?>
			<li><a href="account.php?do=create"><i class="fa fa-user-plus">&nbsp;&nbsp;</i><?php echo __('Sign Up'); ?></a></li>
			<?php
			}
			
			// Do we have a filter for additional navbar links ? 
			apply_filters('post_navbar_links_client', null);
			
			?>
			
		<?php
		}
		?>
      </ul>
    </div><!-- /.navbar-collapse -->
		
	<?php
	}
	?>
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
  </style>
  <!-- / Custom styling -->

  <div class="px-content">
	<div id="header">
		<div class="pull-right flush-right">
		<p>
		<?php

		if (($all_langs = Internationalization::getConfiguredSystemLanguages())
			&& (count($all_langs) > 1)
		) {
			$qs = array();
			parse_str($_SERVER['QUERY_STRING'], $qs);
			foreach ($all_langs as $code=>$info) {
				list($lang, $locale) = explode('_', $code);
				$qs['lang'] = $code;
		?>
			<a class="flag flag-<?php echo esc_attr(strtolower($locale ?: $info['flag'] ?: $lang)); ?>"
				href="?<?php echo http_build_query($qs);
				?>" title="<?php echo esc_attr(Internationalization::getLanguageDescription($code)); ?>">&nbsp;</a>
		<?php }
		} ?>
		</p>
		</div>
	</div>
	
	 <?php if($errors) { ?>
		<div class="alert alert-danger">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<?php echo (!empty($errors['err']) ? esc_html($errors['err']).'<br />' : ''); ?>
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
	 <?php }elseif($msg) { ?>
		<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo $msg; ?></div>
	 <?php }elseif($warn) { ?>
		<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;<?php echo $warn; ?></div>
	 <?php } ?>
