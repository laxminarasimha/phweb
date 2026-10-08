<?php
if(($tabs=$nav->getTabs()) && is_array($tabs)){

    foreach($tabs as $name =>$tab) {
		$subnav=$nav->getSubMenu($name);
        if ($tab['href'][0] != '/')
            $tab['href'] = DESKUSS_ROOT_PATH . 'admin/' . $tab['href'];
			$tab['subnav'] = (!empty($subnav) ? 'px-nav-dropdown' : '');
        echo sprintf('<li class="%s %s px-nav-item %s"><a href="%s"><i class="px-nav-icon %s"></i><span class="px-nav-label">%s</span></a>',
			($tab['active'] ?? false) ? 'active':'inactive',
			$tab['class'] ?? '',
			$tab['subnav'] ?? '',
			$tab['href'],
			$tab['icon'] ?? '',
			$tab['desc'] ?? '');
        if(!empty($subnav)){
            echo "<ul class=\"px-nav-dropdown-menu\">\n";
			$active_submenu = $nav->getActiveMenu() -1;
            foreach($subnav as $k => $item){
                $active_class = ((!empty($tab['active']) && $active_submenu == $k) || !empty($item['active']) ? ' active' : '');
                if (!($id=$item['id'] ?? ''))
                    $id="nav$k";
                if (!empty($item['href']) && $item['href'][0] != '/')
                    $item['href'] = DESKUSS_ROOT_PATH . 'admin/' . $item['href'];

                echo sprintf(
                    '<li class="px-nav-item'.$active_class.'"><a class="%s" href="%s" title="%s" id="%s"><span class="px-nav-label">%s'.(!empty($item['count']) ? '<span class="label label-'.(!empty($item['count_class']) ? $item['count_class'] : 'info').'"> '.$item['count'].'</span>' : '').'</span></a></li>',
                    !empty($item['iconclass']) ? $item['iconclass'] : '',
                    !empty($item['href']) ? $item['href'] : '#',
                    !empty($item['title']) ? $item['title'] : '',
                    $id,
                    !empty($item['desc']) ? $item['desc'] : '');
            }
            echo "\n</ul>\n";
        }
        echo "\n</li>\n";
    }
} ?>
