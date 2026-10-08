<?php

//////////////////////////////////////////////////////////////
//===========================================================
// SOFTACULOUS PROJECT
//===========================================================
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Pulkit and Brijesh
// ----------------------------------------------------------
// Please Read the Terms of use at http://deskuss.com
// ----------------------------------------------------------
//===========================================================
// (c)Softaculous Ltd.
//===========================================================
//////////////////////////////////////////////////////////////

require_once(INCLUDE_DIR.'class.app.php');

#[\AllowDynamicProperties]
class StaffNav {

    var $activetab;
    var $activeMenu;
    var $panel;

    var $staff;

    function __construct($staff, $panel='staff'){
        $this->staff=$staff;
        $this->panel=strtolower($panel);
    }

    function __get($what) {
        // Lazily initialize the tabbing system
        switch($what) {
        case 'tabs':
            $this->tabs=$this->getTabs();
            break;
        case 'submenus':
            $this->submenus=$this->getSubMenus();
            break;
        default:
            throw new Exception($what . ': No such attribute');
        }
        return $this->{$what};
    }

    function getPanel(){
        return $this->panel;
    }

    function isAdminPanel(){
        return (!strcasecmp($this->getPanel(),'admin'));
    }

    function isStaffPanel() {
        return (!$this->isAdminPanel());
    }

    function getRegisteredApps() {
        return Application::getStaffApps();
    }

    function setTabActive($tab, $menu=''){

        if(isset($this->tabs[$tab]) && $this->tabs[$tab]){
            $this->tabs[$tab]['active']=true;
            if($this->activetab && $this->activetab!=$tab && isset($this->tabs[$this->activetab]))
                 $this->tabs[$this->activetab]['active']=false;

            $this->activetab=$tab;
            if($menu) $this->setActiveSubMenu($menu, $tab);

            return true;
        }

        return false;
    }

    function setActiveTab($tab, $menu=''){
        return $this->setTabActive($tab, $menu);
    }

    function getActiveTab(){
        return $this->activetab;
    }

    function setActiveSubMenu($mid, $tab='') {
        if(is_numeric($mid))
            $this->activeMenu = $mid;
        elseif($mid && $tab && ($subNav=$this->getSubNav($tab))) {
            foreach($subNav as $k => $menu) {
                if(strcasecmp($mid, $menu['href'])) continue;

                $this->activeMenu = $k+1;
				
                // Set the submenu as active so it can be used in breadcrumb
                if(!empty($this->submenus[$this->getPanel().'.'.$tab][$k])){
				$this->submenus[$this->getPanel().'.'.$tab][$k]['active'] = 1;
                }
				
                break;
            }
        }
    }

    function getActiveMenu() {
        return $this->activeMenu;
    }

    function addSubMenu($item,$active=false){

        // Triger lazy loading if submenus haven't been initialized
        isset($this->submenus[$this->getPanel().'.'.$this->activetab]);
        $this->submenus[$this->getPanel().'.'.$this->activetab][]=$item;
        if($active)
            $this->activeMenu=sizeof($this->submenus[$this->getPanel().'.'.$this->activetab]);
    }


    function getTabs(){
		global $thisstaff;
		
		$this->tabs = array();
		
		if(!$this->tabs) {

			$this->tabs['dashboard'] = array('desc'=>__('Dashboard'),'href'=>'dashboard.php','title'=>__('Agent Dashboard'), "class"=>"no-pjax", 'icon' => 'fa fa-tachometer');

			$this->tabs['tickets'] = array('desc'=>__('Tickets'),'href'=>'tickets.php','title'=>__('Ticket Queue'), 'icon' => 'fa fa-ticket');
			
			$this->tabs['kbase'] = array('desc'=>__('Knowledgebase'),'href'=>'kb.php','title'=>__('Knowledgebase'), 'icon' => 'fa fa-book');

			if ($thisstaff->hasPerm(User::PERM_DIRECTORY)) {
				$this->tabs['users'] = array('desc' => __('Users'), 'href' => 'users.php', 'title' => __('User Directory'), 'icon' => 'fa fa-users');
			}
			
			// Merge 
			if($thisstaff->isAdmin()){
				
				$this->tabs['staff'] = array('desc'=>__('Agents'),'href'=>'staff.php','title'=>__('Manage Agents'), 'icon' => 'fa fa-user-secret');
				
				$this->tabs['departments'] = array('desc'=>__('Departments'),'href'=>'departments.php','title'=>__('Departments'), 'icon' => 'fa fa-sitemap');

				$this->tabs['settings'] = array('desc'=>__('Settings'),'href'=>'settings.php','title'=>__('System Settings'), 'icon' => 'fa fa-cogs');

				$this->tabs['manage'] = array('desc'=>__('Manage'),'href'=>'departments.php','title'=>__('Manage Options'), 'icon' => 'fa fa-sliders');

				$this->tabs['emails'] = array('desc'=>__('Emails'),'href'=>'emails.php','title'=>__('Email Settings'), 'icon' => 'fa fa-envelope');

				$this->tabs['about'] = array('desc'=>__('About'),'href'=>'logs.php','title'=>__('Admin Dashboard'), 'icon' => 'fa fa-info');
			}else{
				$this->tabs['agents'] = array('desc'=>__('Agents'), 'href'=>'directory.php', 'title'=>__('Agents'), 'icon' => 'fa fa-user-secret');
			}

			if(count($this->getRegisteredApps() ?? []))
				$this->tabs['apps'] = array('desc'=>__('Applications'),'href'=>'apps.php','title'=>__('Applications'), 'icon' => 'fa fa-archive');
			
			$this->tabs = apply_filters('post_load_admin_menus', $this->tabs);
			
		}
		
		return $this->tabs;
    }

    function getSubMenus(){ //Private.
        global $cfg, $thisstaff;

        $staff = $this->staff;
        $submenus=array();
        foreach($this->getTabs() as $k=>$tab){
			$tab_is_active = $this->activetab == $k;
            $subnav=array();
            switch(strtolower($k)){
                case 'tickets':
					if($staff) {
					
						$stats= $thisstaff->getTicketsStats();
						
						if($cfg->showAnsweredTickets()) {
							
								$subnav[] = array('desc'=> _P('queue-name', 'Open'),
												'title' => __('Open Tickets'),
												'href'=>'tickets.php?status=open',
												'iconclass'=>'Ticket',
												'active'=> $tab_is_active && (($this->activetab == 'tickets') && ($_REQUEST['a'] ?? '') != 'open' && (empty($_REQUEST['status']) && !isset($_SESSION['advsearch']) || ($_REQUEST['status'] ?? '')=='open')),
												'count' => number_format($stats['open']+$stats['answered']),
												'count_class' => 'danger');
						}else{
							
								$subnav[] = array('desc'=> _P('queue-name', 'Open'),
												'title' => __('Open Tickets'),
												'href'=>'tickets.php?status=open',
												'iconclass'=>'Ticket',
												'active'=> $tab_is_active && (($this->activetab == 'tickets') && ($_REQUEST['a'] ?? '') != 'open' && (empty($_REQUEST['status']) && !isset($_SESSION['advsearch']) || ($_REQUEST['status'] ?? '')=='open')),
												'count' => number_format($stats['open']),
												'count_class' => 'danger');
							
								$subnav[] = array('desc'=> __('Answered'),
												'title' => __('Answered Tickets'),
												'href'=>'tickets.php?status=answered',
												'iconclass'=>'answeredTickets',
												'active'=> $tab_is_active && ($_REQUEST['status'] ?? '')=='answered',
												'count' => number_format($stats['answered']),
												'count_class' => 'info');

						}

						$subnav[] = array('desc'=> __('My Tickets'),
										'title' => __('Assigned Tickets'),
										'href'=>'tickets.php?status=assigned',
										'iconclass'=>'assignedTickets',
										'active'=> $tab_is_active && ($_REQUEST['status'] ?? '')=='assigned',
										'count' => number_format($stats['assigned']),
										'count_class' => 'info');

						$subnav[] = array('desc'=> __('Overdue'),
										'title' => __('Stale Tickets'),
										'href'=>'tickets.php?status=overdue',
										'iconclass'=>'overdueTickets',
										'active'=> $tab_is_active && ($_REQUEST['status'] ?? '')=='overdue',
										'count' => number_format($stats['overdue']),
										'count_class' => 'danger');

						$subnav[] = array('desc'=> __('Closed'),
										'title' => __('Closed Tickets'),
										'href'=>'tickets.php?status=closed',
										'iconclass'=>'closedTickets',
										'active'=> $tab_is_active && ($_REQUEST['status'] ?? '')=='closed');

					if ($staff->hasPerm(TicketModel::PERM_CREATE, false))
						$subnav[]=array('desc'=>__('New Ticket'),
							'title' => __('Open a New Ticket'),
							'href'=>'tickets.php?a=open',
							'iconclass'=>'newTicket',
							'id' => 'new-ticket',
							'active'=> $tab_is_active && ($_REQUEST['a'] ?? '')=='open');
									
				}else{
					$subnav[]=array('desc'=>__('Tickets'),'href'=>'tickets.php','iconclass'=>'Ticket', 'droponly'=>true);
				}
                    break;
                /* case 'dashboard':
                    $subnav[]=array('desc'=>__('Dashboard'),'href'=>'dashboard.php','iconclass'=>'logs');
                    $subnav[]=array('desc'=>__('All Agents'),'href'=>'directory.php','iconclass'=>'teams');
                    //$subnav[]=array('desc'=>__('My Profile'),'href'=>'profile.php','iconclass'=>'users');
                    break; */
                case 'users':
                    $subnav[] = array('desc' => __('List Users'), 'href' => 'users.php', 'iconclass' => 'teams');
                    $subnav[] = array('desc' => __('Organizations'), 'href' => 'orgs.php', 'iconclass' => 'departments');
                    $subnav[]=array('desc'=>__('Banned Users / Emails'),'href'=>'banlist.php',
									'title'=>__('Banned Users / Emails'),'iconclass'=>'emailDiagnostic');
                    break;
                case 'kbase':
                    $subnav[]=array('desc'=>__('FAQs'),'href'=>'kb.php', 'urls'=>array('faq.php'), 'iconclass'=>'kb');
                    if($staff) {
                        if ($staff->hasPerm(FAQ::PERM_MANAGE))
                            $subnav[]=array('desc'=>__('Categories'),'href'=>'categories.php','iconclass'=>'faq-categories');
                        if ($cfg->isCannedResponseEnabled() && $staff->hasPerm(Canned::PERM_MANAGE, false))
                            $subnav[]=array('desc'=>__('Canned Responses'),'href'=>'canned.php','iconclass'=>'canned');
                    }
                   break;
                case 'apps':
                    foreach ($this->getRegisteredApps() as $app)
                        $subnav[] = $app;
                    break;
					
				case 'staff':
					$subnav[]=array('desc'=>__('Agents'),'href'=>'staff.php','iconclass'=>'users');
					$subnav[]=array('desc'=>__('Teams'),'href'=>'teams.php','iconclass'=>'teams');
					$subnav[]=array('desc'=>__('Roles'),'href'=>'roles.php','iconclass'=>'lists');
					break;
					
				case 'departments':
					$subnav[]=array('desc'=>__('List Departments'),'href'=>'departments.php','iconclass'=>'departments');
					$subnav[]=array('desc'=>__('SLA Plans'),'href'=>'slas.php','iconclass'=>'sla');
					$subnav[]=array('desc'=>__('Forms'),'href'=>'forms.php','iconclass'=>'forms');
					$subnav[]=array('desc'=>__('Lists'),'href'=>'lists.php','iconclass'=>'lists');
					break;
				case 'settings':
					$subnav[]=array('desc'=>__('Company'),'href'=>'settings.php?t=pages','iconclass'=>'pages');
					$subnav[]=array('desc'=>__('System'),'href'=>'settings.php?t=system','iconclass'=>'preferences');
					$subnav[]=array('desc'=>__('Tickets'),'href'=>'settings.php?t=tickets','iconclass'=>'ticket-settings');
					$subnav[]=array('desc'=>__('Agents'),'href'=>'settings.php?t=agents','iconclass'=>'teams');
					$subnav[]=array('desc'=>__('Users'),'href'=>'settings.php?t=users','iconclass'=>'groups');
					$subnav[]=array('desc'=>__('Knowledgebase'),'href'=>'settings.php?t=kb','iconclass'=>'kb-settings');
					$subnav[]=array('desc'=>__('API Keys'),'href'=>'apikeys.php','iconclass'=>'api');
					break;
				case 'manage':
					$subnav[]=array('desc'=>__('Ticket Filters'),'href'=>'filters.php',
										'title'=>__('Ticket Filters'),'iconclass'=>'ticketFilters');
					$subnav[]=array('desc'=>__('Pages'), 'href'=>'pages.php','title'=>'Pages','iconclass'=>'pages');
					break;
				case 'emails':
					$subnav[]=array('desc'=>__('Email Addresses'),'href'=>'emails.php', 'title'=>__('Email Addresses'), 'iconclass'=>'emailSettings');
					$subnav[]=array('desc'=>__('Settings'),'href'=>'emailsettings.php','iconclass'=>'email-settings');
					$subnav[]=array('desc'=>__('Templates'),'href'=>'templates.php','title'=>__('Email Templates'),'iconclass'=>'emailTemplates');
					$subnav[]=array('desc'=>__('Test Outgoing Email'),'href'=>'emailtest.php', 'title'=>__('Test Outgoing Email'), 'iconclass'=>'emailDiagnostic');
					break;
				case 'about':
					$subnav[]=array('desc'=>__('System Logs'),'href'=>'logs.php','iconclass'=>'logs');
					$subnav[]=array('desc'=>__('Information'),'href'=>'system.php','iconclass'=>'preferences');
					break;
            }
            if($subnav)
                $submenus[$this->getPanel().'.'.strtolower($k)]=$subnav;
        }

        return $submenus;
    }

    function getSubMenu($tab=null){
        $tab=$tab?$tab:$this->activetab;
        // Lazy initialize submenus if not already loaded
        if (!isset($this->submenus) || $this->submenus === null) {
            $this->submenus = $this->getSubMenus();
        }
        $key = $this->getPanel().'.'.$tab;
        return $this->submenus[$key] ?? array();
    }

    function getSubNav($tab=null){
        return $this->getSubMenu($tab);
    }

}

class AdminNav extends StaffNav{

    function __construct($staff){
        parent::__construct($staff, 'admin');
    }

    function getRegisteredApps() {
        return Application::getAdminApps();
    }

    function getTabs(){

        if(!$this->tabs){

            $tabs=array();
			
            $tabs['dashboard']=array('desc'=>__('Dashboard'),'href'=>'logs.php','title'=>__('Admin Dashboard'), 'icon' => 'fa fa-tachometer');
			
            $tabs['settings']=array('desc'=>__('Settings'),'href'=>'settings.php','title'=>__('System Settings'), 'icon' => 'fa fa-cogs');
			
            $tabs['manage']=array('desc'=>__('Manage'),'href'=>'departments.php','title'=>__('Manage Options'), 'icon' => 'fa fa-sliders');
			
            $tabs['emails']=array('desc'=>__('Emails'),'href'=>'emails.php','title'=>__('Email Settings'), 'icon' => 'fa fa-envelope');
			
            $tabs['staff']=array('desc'=>__('Agents'),'href'=>'staff.php','title'=>__('Manage Agents'), 'icon' => 'fa fa-users');
			
            if (count($this->getRegisteredApps()))
                $tabs['apps']=array('desc'=>__('Applications'),'href'=>'apps.php','title'=>__('Applications'), 'icon' => 'fa fa-archive');
			
            $this->tabs=$tabs;
        }

        return $this->tabs;
    }

    function getSubMenus(){

        $submenus=array();
        foreach($this->getTabs() as $k=>$tab){
            $subnav=array();
            switch(strtolower($k)){
                case 'dashboard':
                    $subnav[]=array('desc'=>__('System Logs'),'href'=>'logs.php','iconclass'=>'logs');
                    $subnav[]=array('desc'=>__('Information'),'href'=>'system.php','iconclass'=>'preferences');
                    break;
                case 'settings':
                    $subnav[]=array('desc'=>__('Company'),'href'=>'settings.php?t=pages','iconclass'=>'pages');
                    $subnav[]=array('desc'=>__('System'),'href'=>'settings.php?t=system','iconclass'=>'preferences');
                    $subnav[]=array('desc'=>__('Tickets'),'href'=>'settings.php?t=tickets','iconclass'=>'ticket-settings');
                    $subnav[]=array('desc'=>__('Agents'),'href'=>'settings.php?t=agents','iconclass'=>'teams');
                    $subnav[]=array('desc'=>__('Users'),'href'=>'settings.php?t=users','iconclass'=>'groups');
                    $subnav[]=array('desc'=>__('Knowledgebase'),'href'=>'settings.php?t=kb','iconclass'=>'kb-settings');
                    break;
                case 'manage':
                    $subnav[]=array('desc'=>__('Ticket Filters'),'href'=>'filters.php',
                                        'title'=>__('Ticket Filters'),'iconclass'=>'ticketFilters');
                    $subnav[]=array('desc'=>__('SLA Plans'),'href'=>'slas.php','iconclass'=>'sla');
                    $subnav[]=array('desc'=>__('API Keys'),'href'=>'apikeys.php','iconclass'=>'api');
                    $subnav[]=array('desc'=>__('Pages'), 'href'=>'pages.php','title'=>'Pages','iconclass'=>'pages');
                    $subnav[]=array('desc'=>__('Forms'),'href'=>'forms.php','iconclass'=>'forms');
                    $subnav[]=array('desc'=>__('Lists'),'href'=>'lists.php','iconclass'=>'lists');
                    break;
                case 'emails':
                    $subnav[]=array('desc'=>__('Emails'),'href'=>'emails.php', 'title'=>__('Email Addresses'), 'iconclass'=>'emailSettings');
                    $subnav[]=array('desc'=>__('Settings'),'href'=>'emailsettings.php','iconclass'=>'email-settings');
                    $subnav[]=array('desc'=>__('Banlist'),'href'=>'banlist.php',
                                        'title'=>__('Banned Emails'),'iconclass'=>'emailDiagnostic');
                    $subnav[]=array('desc'=>__('Templates'),'href'=>'templates.php','title'=>__('Email Templates'),'iconclass'=>'emailTemplates');
                    $subnav[]=array('desc'=>__('Diagnostic'),'href'=>'emailtest.php', 'title'=>__('Email Diagnostic'), 'iconclass'=>'emailDiagnostic');
                    break;
                case 'staff':
                    $subnav[]=array('desc'=>__('Agents'),'href'=>'staff.php','iconclass'=>'users');
                    $subnav[]=array('desc'=>__('Teams'),'href'=>'teams.php','iconclass'=>'teams');
                    $subnav[]=array('desc'=>__('Roles'),'href'=>'roles.php','iconclass'=>'lists');
                    $subnav[]=array('desc'=>__('Departments'),'href'=>'departments.php','iconclass'=>'departments');
                    break;
                case 'apps':
                    foreach ($this->getRegisteredApps() as $app)
                        $subnav[] = $app;
                    break;
            }
            if($subnav)
                $submenus[$this->getPanel().'.'.strtolower($k)]=$subnav;
        }

        return $submenus;
    }
}

class UserNav {

    var $navs=array();
    var $activenav;

    var $user;

    function __construct($user=null, $active=''){

        $this->user=$user;
        $this->navs=$this->getNavs();
        if($active)
            $this->setActiveNav($active);
    }

    function getRegisteredApps() {
        return Application::getClientApps();
    }

    function setActiveNav($nav){

        if($nav && $this->navs[$nav]){
            $this->navs[$nav]['active']=true;
            if($this->activenav && $this->activenav!=$nav && $this->navs[$this->activenav])
                 $this->navs[$this->activenav]['active']=false;

            $this->activenav=$nav;

            return true;
        }

        return false;
    }

    function getNavLinks(){
        global $cfg;

        //Paths are based on the root dir.
        if(!$this->navs){

            $navs = array();
            $user = $this->user;
            $navs['home']=array('desc'=>__('Home'),'href'=>'index.php','title'=>'', 'icon'=> 'fa fa-home');
            if($cfg && $cfg->isKnowledgebaseEnabled())
                $navs['kb']=array('desc'=>__('Knowledgebase'),'href'=>'kb/index.php','title'=>'', 'icon'=> 'fa fa-book');

            // Show the "Open New Ticket" link unless BOTH client
            // registration is disabled and client login is required for new
            // tickets. In such a case, creating a ticket would not be
            // possible for web clients.
            if ($cfg->getClientRegistrationMode() != 'disabled'
                    || !$cfg->isClientLoginRequired())
                $navs['new']=array('desc'=>__('New Ticket'),'href'=>'open.php','title'=>'', 'icon'=> 'fa fa-plus');
            if($user && $user->getId()) {
                if(!$user->isGuest()) {
                    $navs['tickets']=array('desc'=>__('Tickets'),
                                           'href'=>'tickets.php',
                                            'title'=>__('All tickets'),
										'icon'=> 'fa fa-ticket',
										'count' => $user->getNumTickets($user->canSeeOrgTickets()),
										'count_class' => 'info');
                } else {
                    $navs['tickets']=array('desc'=>__('View Ticket Thread'),
                                           'href'=>sprintf('tickets.php?id=%d',$user->getTicketId()),
                                           'title'=>__('View ticket status'),
										'icon'=> 'fa fa-ticket');
                }
            } else {
                $navs['status']=array('desc'=>__('Check Ticket Status'),'href'=>'view.php','title'=>'', 'icon'=> 'fa fa-th-list');
            }
            $this->navs=$navs;
        }

        return $this->navs;
    }

    function getNavs(){
        return $this->getNavLinks();
    }

}

?>
