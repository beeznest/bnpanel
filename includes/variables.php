<?php
/* For licensing terms, see /license.txt */

$navigation = $pagegen = $version = '';
 
if (INSTALL == 1) {
	//Define global, as we are going to pull up things from db
	global $db, $starttime, $style, $main;
	
	if ($db->config("show_page_gentime") == 1) {
		$mtime = explode(' ', microtime());
		$totaltime = $mtime[0] + $mtime[1] - $starttime;
		$gentime = substr($totaltime, 0, 5);
		$array['PAGEGEN'] = $gentime;
		$array['IP'] = getenv('REMOTE_ADDR');		
		$pagegen .= $style->replaceVar('tpl/footergen.tpl', $array);
		if($db->config("show_footer")) {
			if(ini_get('safe_mode') or
			strpos(ini_get('disable_functions'), 'shell_exec') != false or
			stristr(PHP_OS, 'Win')) {
				$version[0] = "N/A";
			}
			else {
				$output = shell_exec('mysql -V');
				preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
			}
			global $style;
			$array2['OS'] = PHP_OS;
			$array2['SOFTWARE'] = $main->removeXSS($_SERVER["SERVER_SOFTWARE"]);
			$array2['PHP_VERSION'] = phpversion();
			$array2['MYSQL_VERSION'] = $version[0];
			$array2["SERVER"] = $main->removeXSS($_SERVER["HTTP_HOST"]);
			$array['TITLE'] = $style->replaceVar('tpl/aserverstatus.tpl',$array2);
			$pagegen .= $style->replaceVar('tpl/footerdebug.tpl',$array);
		}
	}
	 
	if ($db->config("show_version_id") == 1) {
	 	$version = $db->config("version");
	}

	if (FOLDER != 'install') {
		$array = array();
		$navigation_list = $main->getMainNavigation();		
		$navbits = '';
		foreach($navigation_list as $nav_item) {			
			if(!$db->config("show_acp_menu") && $nav_item['link'] == 'admin') {
				continue;
			} else {
				$array['ID'] = "nav_". $nav_item['link'];
				if (PAGE == $nav_item['visual']) {
					$array['ACTIVE'] = ' class="active" ';
				}
				else {
					$array['ACTIVE'] = '';
				}
				$array['LINK'] = $nav_item['link'];
				$array['ICON'] = $nav_item['icon'];
				$array['NAME'] = $nav_item['visual'];
				$navbits .= $style->replaceVar("tpl/menu/top_link.tpl", $array);
			}
		}
	}	
	$array3 = array();
	$array3['NAV'] = null;
	if (!empty($navbits)) {
	    $array3['NAV'] = $navbits;
	}
	$navigation = $style->replaceVar("tpl/menu/top_main.tpl", $array3);    
}

global $main;
$current_token = $main->getToken();

$data = preg_replace("/<AJAX>/si", URL."includes/ajax.php?_get_token=".$current_token."&", $data);

$sub = defined('SUB') ? ' - '.SUB : '';
$data = preg_replace("/<APP TITLE>/si", NAME . " - " . PAGE.$sub, $data);
$data = preg_replace("/<NAME>/si", NAME, $data);
$data = preg_replace("/<CSS>/si", $this->css(), $data);
$data = preg_replace("/<JAVASCRIPT>/si", $this->javascript(), $data);
$data = preg_replace("/<MENU>/si", $navigation, $data);
$data = preg_replace("/<URL>/si", URL, $data);

$data = preg_replace("/<IMG>/si", URL . "themes/". THEME ."/images/", $data);
$data = preg_replace("/<ICONDIR>/si", URL . "themes/icons/", $data);
$data = preg_replace("/<PAGEGEN>/si", $pagegen, $data); #Page Generation Time
$data = preg_replace("/<COPYRIGHT>/si", '<div id="footer">Powered by <a href="http://www.beeznest.com" target="_blank">BNPanel</a> '. $version .'</div>', $data);
$error_messages = $main->errors();
if (!empty($error_messages)) {
	$data = preg_replace("/<ERRORS>/si", '<div class="info">'.$error_messages.'</div><div style="clear:both"></div>', $data);	
}
//$data = preg_replace("/%INFO%/si", INFO, $data);