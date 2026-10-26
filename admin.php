<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// NO HACK PLEASE (Gecorrigeerd naar de veilige, cross-platform basename() functie)
$pagename = basename($_SERVER['SCRIPT_NAME'] ?? '');
if ($pagename === 'admin.php') {
	die('Bye bye');
}

// SECURE_ID controle (wordt in snews.php uit config.php gezet)
if (!defined('SECURE_ID') || SECURE_ID === '') { 
	die('ACCESS DENIED');
}

// NOT LOGGED - > ERROR (Veilig controleren van de constante)
if (!defined('_ADMIN') || !_ADMIN) {
	if (function_exists('notification')) {
		echo notification(1, l('error_not_logged_in'), 'login');
	}
	if (function_exists('set_error')) {
		set_error();
	}
	exit;
}

// ADMINISTRATION
function administration(): void {
	// FIX: Herschreven naar de schone en veilige PHP 8.1 methode om POST data te resetten
	$_POST = [];
	
	echo '<div class="adminpanel">';
	echo '<p class="admintitle"><a href="http://snewscms.com/" title="sNews CMS">sNews</a> ' . htmlspecialchars(l('administration'), ENT_QUOTES, 'UTF-8') . '</p>';
	echo '<p>' . htmlspecialchars(l('categories'), ENT_QUOTES, 'UTF-8') . ': <a href="' . _SITE . 'admin_category/">' . htmlspecialchars(l('add_new'), ENT_QUOTES, 'UTF-8') . '</a>';
	
	$link = ' ' . l('divider') . ' <a href="' . _SITE;
	$catnum = stats('categories', '');
	
	if ($catnum > 0) {
		echo $link . 'snews_categories/">' . htmlspecialchars(l('view'), ENT_QUOTES, 'UTF-8') . '</a>';
	}
	
	echo '</p><p>' . htmlspecialchars(l('articles'), ENT_QUOTES, 'UTF-8') . ': ';
	$art_new = $catnum > 0 ? '<a href="' . _SITE . 'article_new/">' . htmlspecialchars(l('add_new'), ENT_QUOTES, 'UTF-8') . '</a>' : htmlspecialchars(l('create_cat'), ENT_QUOTES, 'UTF-8');
	echo $art_new;
	
	if (stats('articles', '1') > 0) {
		echo $link . 'snews_articles/">' . htmlspecialchars(l('view'), ENT_QUOTES, 'UTF-8') . '</a>';
	}
	
	echo '</p><p>' . htmlspecialchars(l('pages'), ENT_QUOTES, 'UTF-8') . ': <a href="' . _SITE . 'page_new/">' . htmlspecialchars(l('add_new'), ENT_QUOTES, 'UTF-8') . '</a>';
	if (stats('articles', '3') > 0) {
		echo $link . 'snews_pages/">' . htmlspecialchars(l('view'), ENT_QUOTES, 'UTF-8') . '</a>';
	}
	echo '</p>';
	
	if (s('enable_extras') === 'YES') {
		echo '<p class="admintitle">' . htmlspecialchars(l('extra_contents'), ENT_QUOTES, 'UTF-8') . '</p>';
		echo '<p>' . htmlspecialchars(l('groupings'), ENT_QUOTES, 'UTF-8') . ': <a href="' . _SITE . 'admin_groupings/">' . htmlspecialchars(l('add_new'), ENT_QUOTES, 'UTF-8') . '</a>';
		if (stats('extras', '') > 0) {
			echo $link . 'groupings/">' . htmlspecialchars(l('view'), ENT_QUOTES, 'UTF-8') . '</a>';
		}
		echo '</p>';
	}
	
	echo '<p>' . htmlspecialchars(l('extra_contents'), ENT_QUOTES, 'UTF-8') . ': <a href="' . _SITE . 'extra_new/">' . htmlspecialchars(l('add_new'), ENT_QUOTES, 'UTF-8') . '</a>';
	if (stats('articles', '2') > 0) {
		echo $link . 'extra_contents/">' . htmlspecialchars(l('view'), ENT_QUOTES, 'UTF-8') . '</a>';
	}
	echo '</p>';
	
	# ADDONS
	echo '<p>' . htmlspecialchars(l('admin_addons'), ENT_QUOTES, 'UTF-8') . ': ';
	$addons = readAddons();
	if (empty($addons)) {
		echo htmlspecialchars(l('none'), ENT_QUOTES, 'UTF-8');
	} else {
		echo '<a href="' . _SITE . 'admin_addons/">' . htmlspecialchars(l('view'), ENT_QUOTES, 'UTF-8') . '</a>';
	}
	echo '</p>';
	echo '</div>';
	
	# UNAPPROVED COMMENTS (Veilig herschreven met Prepared Queries via PDO)
	$database = db();
	if ($database) {
		$query = "SELECT id, articleid, name FROM " . _PRE . "comments WHERE approved != 'True'";
		$unapproved = stats('comments', '', "approved != 'True'");
		if ($unapproved > 0) {
			echo '<div class="adminpanel"><p class="admintitle">' . htmlspecialchars(l('comments'), ENT_QUOTES, 'UTF-8') . '</p>';
			echo '<p><a onclick="toggle(\'sub1\')" style="cursor: pointer;" title="' . htmlspecialchars(l('unapproved'), ENT_QUOTES, 'UTF-8') . '">';
			echo $unapproved . ' ' . htmlspecialchars(l('wait_approval'), ENT_QUOTES, 'UTF-8') . '</a></p>';
			echo '<div id="sub1" class="innerpanel" style="display: none;">';
			
			try {
				$result = $database->query($query);
				if ($result) {
					while ($r = dbfetch($result)) {
						$articleTITLE_res = retrieve('title', 'articles', 'id', $r['articleid']);
						$articleTITLE = is_array($articleTITLE_res) ? ($articleTITLE_res['title'] ?? '') : (string)$articleTITLE_res;
						
						echo '<p class="spacelink">' . htmlspecialchars((string)$r['name'], ENT_QUOTES, 'UTF-8') . ' (<strong>' . htmlspecialchars($articleTITLE, ENT_QUOTES, 'UTF-8') . '</strong>) ' . l('divider') . '
						<a href="' . _SITE . '?action=editcomment&amp;commentid=' . (int)$r['id'] . '">' . l('edit') . '</a></p>';
					}
				}
			} catch (PDOException $e) {}
			echo '</div></div>';
		}
	}
	
	# SITE PANEL
	echo '<div class="message"><p class="admintitle">' . htmlspecialchars(l('site_settings'), ENT_QUOTES, 'UTF-8') . '</p>';
	echo '<p><a href="' . _SITE . 'snews_settings/">' . htmlspecialchars(l('settings'), ENT_QUOTES, 'UTF-8') . '</a>&nbsp;|&nbsp;
		<a href="' . _SITE . 'snews_files/">' . htmlspecialchars(l('files'), ENT_QUOTES, 'UTF-8') . '</a></p></div>';
	echo '<div class="message"><p class="admintitle">' . htmlspecialchars(l('login_status'), ENT_QUOTES, 'UTF-8') . '</p>';
	echo '<p><a href="' . _SITE . 'logout/">' . htmlspecialchars(l('logout'), ENT_QUOTES, 'UTF-8') . '</a></p>';
	echo '</div>';
}

// SHOW ADDONS IN ADMIN (PHP 8.1 safe array key checks)
function showAdmAddons(): void {
	global $l;
	echo '<div class="adminpanel">';
	echo '<p class="admintitle">' . htmlspecialchars(l('admin_addons'), ENT_QUOTES, 'UTF-8') . '</p>';
	$funcs = readAddons();
	if (!empty($funcs)) {
		$list = explode(',', $funcs);
		foreach ($list as $addon_func) {
			$addon_func = trim($addon_func);
			$field = str_replace('admin_', '', $addon_func) . '_title';
			// FIX: Null coalescing check voorkomt undefined key warnings
			if (isset($l[$field]) || !empty(l($field))) {
				echo '<p>' . htmlspecialchars(l($field), ENT_QUOTES, 'UTF-8') . ': <a href="' . _SITE . htmlspecialchars($addon_func, ENT_QUOTES, 'UTF-8') . '/">' . htmlspecialchars(l('administration'), ENT_QUOTES, 'UTF-8') . '</a></p>';
			}
		}
	}
	echo '</div>';
}

// HTML ENTITIES
function entity(mixed $item): string {
	$charset = !empty(s('charset')) ? s('charset') : 'UTF-8';
	return htmlspecialchars((string)$item, ENT_QUOTES, $charset);
}

// MAKE A CLEAN SEF URL
function cleanSEF(mixed $string): string {
	$string = str_replace(' ', '-', (string)$string);
	$string = (string)preg_replace('/[^0-9a-zA-Z-_]/', '', $string);
	$string = str_replace('-', ' ', $string);
	$string = (string)preg_replace('/^\s+|\s+$/', '', $string);
	$string = (string)preg_replace('/\s+/', ' ', $string);
	$string = str_replace(' ', '-', $string);
	return strtolower($string);
}

// CLEAN CHECK SEF
function cleancheckSEF(mixed $string): string {
	return !preg_match('/^[a-z0-9-_]+$/i', (string)$string) ? 'notok' : 'ok';
}

// PREPARE TEXT TO DATABASE (CRUCIALE FIX: get_magic_quotes_gpc volledig verwijderd wegens fatal error)
function clean_mysql(mixed $text): string {
	$text_str = (string)$text;
	// get_magic_quotes_gpc en magic_quotes_sybase logica zijn VERWIJDERD uit PHP.
	$text_str = urldecode($text_str);
	return str_replace('\\"', '"', $text_str);
}

// SETTINGS FORM (PHP 8.1 safe form initialization)
function settings(): void {
	echo '<div class="adminpanel"><p class="admintitle">' . htmlspecialchars(l('settings_title'), ENT_QUOTES, 'UTF-8') . '</p>';
	echo html_input('form', '', 'post', '', '', '', '', '', '', '', '', '', 'post', '?action=process&amp;task=save_settings', '');
		
		# Expandable Settings
		echo '<p><a onclick="toggle(\'sub1\')" style="cursor: pointer;" title="' . htmlspecialchars(l('a_openclose') . l('settings'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(l('settings'), ENT_QUOTES, 'UTF-8') . '</a></p>';
		echo '<div id="sub1" style="display: none;">';
		echo html_input('text', 'website_title', 'webtitle', s('website_title'), l('a_website_title'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('text', 'home_sef', 'webSEF', s('home_sef') === '' ? l('home_sef') : s('home_sef'), l('a_home_sef'), '', 'onkeypress="return SEFrestrict(event);"', '', '', '', '', '', '', '', '');
		echo html_input('text', 'website_description', 'wdesc', s('website_description'), l('a_description'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('text', 'website_keywords', 'wkey', s('website_keywords'), l('a_keywords'), '', '', '', '', '', '', '', '', '', '');
		echo '</div>';
		
		# Expandable Contact
		echo '<p><a onclick="toggle(\'sub2\')" style="cursor: pointer;" title="' . htmlspecialchars(l('a_openclose') . l('a_contact_info'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(l('a_contact_info'), ENT_QUOTES, 'UTF-8') . '</a></p>';
		echo '<div id="sub2" style="display: none;">';
		echo html_input('text', 'website_email', 'we', s('website_email'), l('a_website_email'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('text', 'contact_subject', 'cs', s('contact_subject'), l('a_contact_subject'), '', '', '', '', '', '', '', '', '', '');
		echo '</div>';
		
		# Expandable Time & Locale
		echo '<p><a onclick="toggle(\'sub3\')" style="cursor: pointer;" title="' . htmlspecialchars(l('a_openclose') . l('a_time_settings'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(l('a_time_settings'), ENT_QUOTES, 'UTF-8') . '</a></p>';
		echo '<div id="sub3" style="display: none;">';
		echo html_input('text', 'language', 'lang', s('language') === '' ? 'EN' : s('language'), l('a_language'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('text', 'charset', 'char', s('charset') === '' ? 'UTF-8' : s('charset'), l('charset'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('text', 'date_format', 'dt', s('date_format'), l('a_date_format'), '', '', '', '', '', '', '', '', '', '');
		echo '</div>';

				# Expandable Contents
		echo '<p><a onclick="toggle(\'sub4\')" style="cursor: pointer;" title="' . htmlspecialchars(l('a_openclose') . l('contents'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(l('contents'), ENT_QUOTES, 'UTF-8') . '</a></p>';
		echo '<div id="sub4" style="display: none;">';
		echo html_input('text', 'article_limit', 'artl', s('article_limit'), l('a_article_limit'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('text', 'rss_limit', 'rssl', s('rss_limit'), l('a_rss_limit'), '', '', '', '', '', '', '', '', '', '');
		echo '<p><label for="dp">' . htmlspecialchars(l('a_display_page'), ENT_QUOTES, 'UTF-8') . ':</label><br /> <select name="display_page" id="dp">';
		
		$display_page_val = (int)s('display_page');
		echo '<option value="0"' . ($display_page_val === 0 ? ' selected="selected"' : '') . '>' . htmlspecialchars(l('none'), ENT_QUOTES, 'UTF-8') . '</option>';
		
		$database = db();
		if ($database) {
			$query = "SELECT id, title FROM " . _PRE . "articles WHERE position = 3 ORDER BY id ASC";
			try {
				$result = $database->query($query);
				if ($result) {
					foreach ($result as $r) {
						$sel = ($display_page_val === (int)$r['id']) ? ' selected="selected"' : '';
						echo '<option value="' . (int)$r['id'] . '"' . $sel . '>' . htmlspecialchars((string)$r['title'], ENT_QUOTES, 'UTF-8') . '</option>';
					}
				}
			} catch (PDOException $e) {}
		}
		echo '</select></p>';
		
		echo html_input('checkbox', 'display_new_on_home', 'dnoh', '', l('a_display_new_on_home'), '', '', '', '', (s('display_new_on_home') === 'on' ? 'ok' : ''), '', '', '', '', '');
		echo html_input('checkbox', 'display_pagination', 'dpag', '', l('a_display_pagination'), '', '', '', '', (s('display_pagination') === 'on' ? 'ok' : ''), '', '', '', '', '');
		echo html_input('checkbox', 'num_categories', 'nc', '', l('a_num_categories'), '', '', '', '', (s('num_categories') === 'on' ? 'ok' : ''), '', '', '', '', '');
		echo html_input('checkbox', 'show_cat_names', 'scn', '', l('a_show_category_name'), '', '', '', '', (s('show_cat_names') === 'on' ? 'ok' : ''), '', '', '', '', '');
		echo html_input('checkbox', 'enable_extras', 'ee', '', l('enable_extras'), '', '', '', '', (s('enable_extras') === 'YES' ? 'ok' : ''), '', '', '', '', '');
		echo html_input('text', 'file_ext', 'fileext', s('file_extensions'), l('file_extensions'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('text', 'allowed_file', 'all_file', s('allowed_files'), l('allowed_files'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('text', 'allowed_img', 'all_img', s('allowed_images'), l('allowed_images'), '', '', '', '', '', '', '', '', '', '');
		echo '</div>';
		
		# Expandable Comments
		echo '<p><a onclick="toggle(\'sub5\')" style="cursor: pointer;" title="' . htmlspecialchars(l('a_openclose') . l('comments'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(l('comments'), ENT_QUOTES, 'UTF-8') . '</a></p>';
		echo '<div id="sub5" style="display: none;">';
		echo html_input('checkbox', 'approve_comments', 'ac', '', l('a_approve_comments'), '', '', '', '', (s('approve_comments') === 'on' ? 'ok' : ''), '', '', '', '', '');
		echo html_input('text', 'comment_repost_timer', 'crt', s('comment_repost_timer'), l('comment_repost_timer'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('checkbox', 'mail_on_comments', 'mc', '', l('a_mail_on_comments'), '', '', '', '', (s('mail_on_comments') === 'on' ? 'ok' : ''), '', '', '', '', '');
		echo html_input('checkbox', 'enable_comments', 'ec', '', l('enable_comments'), '', '', '', '', (s('enable_comments') === 'YES' ? 'ok' : ''), '', '', '', '', '');
		echo html_input('checkbox', 'freeze_comments', 'dc', '', l('freeze_comments'), '', '', '', '', (s('freeze_comments') === 'YES' ? 'ok' : ''), '', '', '', '', '');
		
		# ORDER
		echo '<p><label for="co">' . htmlspecialchars(l('a_comments_order'), ENT_QUOTES, 'UTF-8') . ':</label><br /><select id="co" name="comments_order">';
		echo '<option value="DESC"' . (s('comments_order') === 'DESC' ? ' selected="selected"' : '') . '>' . htmlspecialchars(l('newer_top'), ENT_QUOTES, 'UTF-8') . '</option>';
		echo '<option value="ASC"' . (s('comments_order') === 'ASC' ? ' selected="selected"' : '') . '>' . htmlspecialchars(l('newer_bottom'), ENT_QUOTES, 'UTF-8') . '</option></select>';
		echo '</p>';
		
		echo html_input('text', 'comment_limit', 'cl', s('comment_limit'), l('a_comment_limit'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('checkbox', 'word_filter_enable', 'wfe', '', l('a_word_filter_enable'), '', '', '', '', (s('word_filter_enable') === 'on' ? 'ok' : ''), '', '', '', '', '');
		echo html_input('text', 'word_filter_file', 'wff', s('word_filter_file'), l('a_word_filter_file'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('text', 'word_filter_change', 'wfc', s('word_filter_change'), l('a_word_filter_change'), '', '', '', '', '', '', '', '', '', '');
		echo '</div>';
		
		echo '<p>';
		# Save Settings button
		echo html_input('hidden', 'action', '', 'process', '', '', '', '', '', '', '', '', '', '', '');
		echo html_input('hidden', 'task', 'task', 'save_settings', '', '', '', '', '', '', '', '', '', '', '');
		echo html_input('submit', 'save', 'save', l('save'), '', 'button', '', '', '', '', '', '', '', '', '');
		echo '</p>';
	echo '</form>';
	echo '</div>';
	
	# Change Password panel (Gecorrigeerd: Structuur en nesting hersteld)
	echo '<div class="adminpanel">';
		echo html_input('form', '', 'post', '', '', '', '', '', '', '', '', '', 'post', '?action=process&amp;task=changeup', '');
		echo '<p><a onclick="toggle(\'sub6\')" style="cursor: pointer;" title="' . htmlspecialchars(l('a_openclose') . l('change_up'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(l('change_up'), ENT_QUOTES, 'UTF-8') . '</a></p>';
		echo '<div id="sub6" style="display: none;">';
		echo '<p>' . htmlspecialchars(l('login_limit'), ENT_QUOTES, 'UTF-8') . '</p>';
		echo html_input('text', 'uname', 'uname', '', l('a_username'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('password', 'pass1', 'pass1', '', l('a_password'), '', '', '', '', '', '', '', '', '', '');
		echo html_input('password', 'pass2', 'pass2', '', l('a_password2'), '', '', '', '', '', '', '', '', '', '');
		echo '<p>';
		# Save Password Change button
		echo html_input('hidden', 'action', '', 'process', '', '', '', '', '', '', '', '', '', '', '');
		echo html_input('hidden', 'task', '', 'changeup', '', '', '', '', '', '', '', '', '', '', '');
		echo html_input('submit', 'submit_pass', 'submit_pass', l('save'), '', 'button', '', '', '', '', '', '', '', '', '');
		echo '</p></div>';
		echo '</form>';
	echo '</div>';
}

// CATEGORIES - ADMIN LIST (Gecorrigeerd: array index string literal fix en prepared subqueries)
function admin_categories(): void {
	$database = db();
	if (!$database) {
		return;
	}

	$add = ' - <a href="' . _SITE . 'admin_category/">' . htmlspecialchars(l('add_new'), ENT_QUOTES, 'UTF-8') . '</a>';
	$link = '?action=admin_category';
	$tab = 1;
	
	echo '<div class="adminpanel">';
	echo '<p class="admintitle">' . l('categories') . $add . '</p>';
	echo html_input('form', '', '', '', '', '', '', '', '', '', '', '', 'post', '?action=process&amp;task=reorder', '');
	echo '<p><input type="hidden" name="order" id="order" value="snews_categories" /></p>';
	
	$num_cat = stats('categories', '', 'subcat = 0');
	if ($num_cat === 0) {
		echo '<p>' . htmlspecialchars(l('category_not_exist'), ENT_QUOTES, 'UTF-8') . '</p>';
	} else {
		$query = "SELECT id, name, description, published, catorder FROM " . _PRE . "categories WHERE subcat = 0 ORDER BY catorder, id ASC";
		try {
			$result = $database->query($query);
			if ($result) {
				// Bereid de subcategoriequery voor buiten de loop voor betere PHP 8.1 runtime prestaties
				$subquery = "SELECT id, name, description, published, catorder FROM " . _PRE . "categories WHERE subcat = :parent_id ORDER BY catorder, id ASC";
				$stmt_sub = $database->prepare($subquery);
				
				foreach ($result as $r) {
					$cat_id = (int)$r['id'];
					$cat_input = '<input type="text" name="cat_' . $cat_id . '" value="' . (int)$r['catorder'] . '" size="1" tabindex="' . $tab . '" /> &nbsp;';
					
					echo '<p>' . $cat_input . '<strong>' . htmlspecialchars((string)$r['name'], ENT_QUOTES, 'UTF-8') . '</strong>
						' . l('divider') . ' <a href="' . _SITE . $link . '&amp;id=' . $cat_id . '" title="' . htmlspecialchars((string)$r['description'], ENT_QUOTES, 'UTF-8') . '">' . l('edit') . '</a> ';
					echo $r['published'] !== 'YES' ? ' ' . l('divider') . ' [' . l('status') . ' ' . l('unpublished') . ']' : '';
					echo ' ' . l('divider') . ' <a href="' . _SITE . $link . '&amp;sub_id=' . $cat_id . '" title="' . htmlspecialchars((string)$r['description'], ENT_QUOTES, 'UTF-8') . '">' . l('add_subcategory') . '</a></p>';
					
					// FIX: Herschreven naar een veilige prepared execution om de $r[id] array string warning te elimineren
					$stmt_sub->execute(['parent_id' => $cat_id]);
					$tab2 = 1;
					
					while ($sub = $stmt_sub->fetch(PDO::FETCH_ASSOC)) {
						$sub_id = (int)$sub['id'];
						$subcat_input = '<input type="text" name="cat_' . $sub_id . '" value="' . (int)$sub['catorder'] . '" size="1" tabindex="' . $tab2 . '" /> &nbsp;';
						
						echo '<p class="subcat">' . $subcat_input . '<strong>' . htmlspecialchars((string)$sub['name'], ENT_QUOTES, 'UTF-8') . '</strong>' . l('divider') . ' ';
						echo '<a href="' . _SITE . $link . '&amp;id=' . $sub_id . '" title="' . htmlspecialchars((string)$sub['description'], ENT_QUOTES, 'UTF-8') . '">' . l('edit') . '</a> ';
						echo ($sub['published'] !== 'YES' ? ' ' . l('divider') . ' [' . l('status') . ' ' . l('unpublished') . ']' : '');
						echo '</p>';
						$tab2++;
					}
					$tab++;
				}
			}
		} catch (PDOException $e) {}
	}
	
	echo html_input('hidden', 'action', 'process', 'process', '', '', '', '', '', '', '', '', '', '', '');
	echo html_input('hidden', 'task', 'task', 'reorder', '', '', '', '', '', '', '', '', '', '', '');
	echo '';
	echo html_input('submit', 'reorder', 'reorder', l('order_content'), '', 'button', '', '', '', '', '', '', '', '', '');
	echo '';
	echo '';
	}

// CATEGORIES FORM (PHP 8.1 safe: prepared queries & undefined array key fixes)
function form_categories(string $subcat = 'cat'): void {
	$database = db();
	if (!$database) {
		return;
	}

	$categoryid = 0;
	$sub_cat = 0;
	$name = '';
	
	if (isset($_GET['id']) && is_numeric($_GET['id'])) {
		$categoryid = (int)$_GET['id'];
		
		// FIX: Naar prepared statements om SQL fouten en type errors te voorkomen
		$query = "SELECT id, name, seftitle, published, description, subcat, catorder FROM " . _PRE . "categories WHERE id = :id";
		try {
			$stmt = $database->prepare($query);
			$stmt->execute(['id' => $categoryid]);
			$r = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if ($r) {
				$parent_id = (int)$r['subcat'];
				$qwr = "SELECT name FROM " . _PRE . "categories WHERE id = :parent_id";
				$stmt_parent = $database->prepare($qwr);
				$stmt_parent->execute(['parent_id' => $parent_id]);
				if ($rr = $stmt_parent->fetch(PDO::FETCH_ASSOC)) {
					$name = (string)$rr['name'];
				}
				
				$frm_action = _SITE . '?action=process&amp;task=admin_category&amp;id=' . $categoryid;
				$frm_add_edit = ($parent_id === 0) ? l('edit') . ' ' . l('category') : l('edit') . ' ' . l('subcategory') . ' ' . $name;
				$frm_name = (string)$r['name'];
				$subcat = ($parent_id === 0) ? 'cat' : 'subcat';
				$frm_sef_title = (string)$r['seftitle'];
				$frm_description = (string)$r['description'];
				$frm_publish = ($r['published'] === 'YES') ? 'ok' : '';
				$catorder = isset($r['catorder']) ? (int)$r['catorder'] : 0;
				$frm_task = 'edit_category';
				$frm_submit = l('edit_button');
			} else {
				// Categorie id bestaat niet in database
				return;
			}
		} catch (PDOException $e) {
			return;
		}
	} else {
		$sub_cat = isset($_GET['sub_id']) ? (int)$_GET['sub_id'] : 0;
		if ($sub_cat !== 0) {
			$query = "SELECT name FROM " . _PRE . "categories WHERE id = :sub_cat";
			try {
				$stmt = $database->prepare($query);
				$stmt->execute(['sub_cat' => $sub_cat]);
				if ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$name = (string)$r['name'];
				}
			} catch (PDOException $e) {}
		}
		
		$frm_action = _SITE . '?action=process&amp;task=admin_category';
		$frm_add_edit = ($sub_cat === 0) ? l('add_category') : l('add_subcategory') . ' (' . $name . ')';
		
		$title = isset($_POST['title']) ? cleanSEF($_POST['seftitle'] ?? '') : '';
		$frm_sef_title = (isset($_POST['name']) && (string)$_POST['name'] === '') ? cleanSEF($_POST['name']) : $title;
		$frm_name = '';
		$catorder = 0;
		$frm_description = '';
		$frm_publish = 'ok';
		$frm_task = 'add_category';
		$frm_submit = l('add_category');
	}
	
	echo html_input('form', '', 'post', '', '', '', '', '', '', '', '', '', 'post', $frm_action, '');
	echo '<div class="adminpanel">';
	echo '<p class="admintitle">' . htmlspecialchars($frm_add_edit, ENT_QUOTES, 'UTF-8') . '</p>';
	echo html_input('text', 'name', 't', $frm_name, l('name'), '', 'onchange="genSEF(this,document.forms[\'post\'].seftitle)"', 'onkeyup="genSEF(this,document.forms[\'post\'].seftitle)"', '', '', '', '', '', '', '');
	echo html_input('text', 'seftitle', 's', $frm_sef_title, l('sef_title_cat'), '', '', '', '', '', '', '', '', '', '');
	echo html_input('text', 'description', 'desc', $frm_description, l('description'), '', '', '', '', '', '', '', '', '', '');
	
	if ($sub_cat === 0 && !empty($categoryid)) {
		echo '<p>' . htmlspecialchars(l('subcategory'), ENT_QUOTES, 'UTF-8') . ':<br />';
		if (function_exists('category_list')) {
			category_list($categoryid);
		}
		echo '</p>';
	}
	
	$publish_label = ($subcat === 'cat') ? l('publish_category') : l('publish_subcategory');
	echo html_input('checkbox', 'publish', 'pub', 'YES', $publish_label, '', '', '', '', $frm_publish, '', '', '', '', '');
	echo '</div><p>';
	
	if ($sub_cat !== 0) {
		echo html_input('hidden', 'subcat', 'subcat', (string)$sub_cat, '', '', '', '', '', '', '', '', '', '', '');
	}
	echo html_input('hidden', 'catorder', 'catorder', (string)$catorder, '', '', '', '', '', '', '', '', '', '', '');
	echo html_input('hidden', 'action', 'process', 'process', '', '', '', '', '', '', '', '', '', '', '');
	echo html_input('hidden', 'task', 'task', 'admin_category', '', '', '', '', '', '', '', '', '', '', '');
	echo html_input('submit', $frm_task, $frm_task, $frm_submit, '', 'button', '', '', '', '', '', '', '', '', '');
	
	if (!empty($categoryid)) {
		echo '&nbsp;&nbsp;';
		echo html_input('hidden', 'id', 'id', (string)$categoryid, '', '', '', '', '', '', '', '', '', '', '');
		echo html_input('submit', 'delete_category', 'delete_category', l('delete'), '', 'button', 'onclick="return pop(\'' . htmlspecialchars(l('js_delete2'), ENT_QUOTES, 'UTF-8') . '\')"', '', '', '', '', '', '', '', '');
	}
	echo '</p></form>';
}

// DELETE CATEGORY BY ID (CRUCIALE FIX: dbfetch na DELETE query wegehaald wegens fatal crash)
function delete_cat(mixed $id): void {
	$database = db();
	if (!$database) {
		return;
	}

	$id = (int)$id;
	$catdata_res = retrieve('catorder, subcat', 'categories', 'id', $id);
	
	if (is_array($catdata_res)) {
		$cat_order = (int)($catdata_res['catorder'] ?? 0);
		$cat_subcat = (int)($catdata_res['subcat'] ?? 0);
		
		try {
			// FIX: Herschreven naar execute() zonder loze dbfetch() call
			$query = "DELETE FROM " . _PRE . "categories WHERE id = :id";
			$stmt = $database->prepare($query);
			$stmt->execute(['id' => $id]);
			
			// Herbereken de volgorde van overige categorieën
			$sql = "SELECT id, catorder FROM " . _PRE . "categories WHERE catorder > :cat_order AND subcat = :cat_subcat";
			$stmt_select = $database->prepare($sql);
			$stmt_select->execute(['cat_order' => $cat_order, 'cat_subcat' => $cat_subcat]);
			
			$sq_update = "UPDATE " . _PRE . "categories SET catorder = :order WHERE id = :target_id";
			$stmt_update = $database->prepare($sq_update);
			
			while ($rr = $stmt_select->fetch(PDO::FETCH_ASSOC)) {
				$new_order = (int)$rr['catorder'] - 1;
				$stmt_update->execute([
					'order'     => $new_order,
					'target_id' => (int)$rr['id']
				]);
			}
		} catch (PDOException $e) {}
	}
}

// DELETE EXTRA/PAGE (PHP 8.1 safe prepared verification)
function delete_item(string $table, string $field, mixed $id): void {
	$database = db();
	if (!$database) {
		return;
	}
	
	// Valideer tabelnaam en veldnaam tegen SQL injection (whitelist)
	$table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
	$field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
	
	$query = "DELETE FROM " . _PRE . $table . " WHERE " . $field . " = :val";
	try {
		$stmt = $database->prepare($query);
		$stmt->execute(['val' => $id]);
	} catch (PDOException $e) {}
}

// FORM EXTRA GROUPINGS (PHP 8.1 safe: $_POST undefined keys afgevangen via null coalescing)
function form_groupings(): void {
	if (s('enable_extras') !== 'YES') {
		return;
	}
	
	$database = db();
	if (!$database) {
		return;
	}

	$extraid = 0;
	if (isset($_GET['id']) && is_numeric($_GET['id'])) {
		$extraid = (int)$_GET['id'];
		$query = "SELECT id, name, seftitle, description FROM " . _PRE . "extras WHERE id = :id";
		
		try {
			$stmt = $database->prepare($query);
			$stmt->execute(['id' => $extraid]);
			$r = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if ($r) {
				$frm_action = _SITE . '?action=process&amp;task=admin_groupings&amp;id=' . $extraid;
				$frm_add_edit = l('edit');
				$frm_name = (string)$r['name'];
				$frm_sef_title = (string)$r['seftitle'];
				$frm_description = (string)$r['description'];
				$frm_task = 'edit_groupings';
				$frm_submit = l('edit_button');
			} else {
				return;
			}
		} catch (PDOException $e) {
			return;
		}
	} else {
		$frm_action = _SITE . '?action=process&amp;task=admin_groupings';
		$frm_add_edit = l('add_groupings');
		
		// FIX: Null coalescing operator voorkomt undefined key warnings bij initialisatie
		$post_name = (string)($_POST['name'] ?? '');
		$post_sef = (string)($_POST['seftitle'] ?? '');
		
		$frm_name = $post_name;
		$frm_sef_title = ($post_name === '') ? cleanSEF($post_name) : cleanSEF($post_sef);
		$frm_description = '';
		$frm_task = 'add_groupings';
		$frm_submit = l('add_groupings');
	}
	
	echo html_input('form', '', 'post', '', '', '', '', '', '', '', '', '', 'post', $frm_action, '');
	echo '<div class="adminpanel">';
	echo '<p class="admintitle">' . htmlspecialchars($frm_add_edit, ENT_QUOTES, 'UTF-8') . '</p>';
	echo html_input('text', 'name', 't', $frm_name, l('name'), '', 'onchange="genSEF(this,document.forms[\'post\'].seftitle)"', 'onkeyup="genSEF(this,document.forms[\'post\'].seftitle)"', '', '', '', '', '', '', '');
	echo html_input('text', 'seftitle', 's', $frm_sef_title, l('extra_title'), '', '', '', '', '', '', '', '', '', '');
	echo html_input('text', 'description', 'desc', $frm_description, l('description'), '', '', '', '', '', '', '', '', '', '');
	echo '</div><p>';
	
	echo html_input('hidden', 'action', 'action', 'process', '', '', '', '', '', '', '', '', '', '', '');
	echo html_input('hidden', 'task', 'task', 'admin_groupings', '', '', '', '', '', '', '', '', '', '', '');
	echo html_input('submit', $frm_task, $frm_task, $frm_submit, '', 'button', '', '', '', '', '', '', '', '', '');
	
	if (!empty($extraid)) {
		echo '&nbsp;&nbsp;';
		echo html_input('hidden', 'id', 'id', (string)$extraid, '', '', '', '', '', '', '', '', '', '', '');
		if ($extraid !== 1) {
			echo html_input('submit', 'delete_groupings', 'delete_groupings', l('delete'), '', 'button', 'onclick="return pop(\'' . htmlspecialchars(l('js_delete2'), ENT_QUOTES, 'UTF-8') . '\')"', '', '', '', '', '', '', '', '');
		}
	}
	echo '</p></form>';
}

// ADMIN GROUPINGS
function admin_groupings(): void {
	if (s('enable_extras') !== 'YES') {
		return;
	}
	
	$database = db();
	if (!$database) {
		return;
	}

	$add = (stats('extras', '') > 0) ? ' - <a href="' . _SITE . 'admin_groupings/" title="' . htmlspecialchars(l('add_new'), ENT_QUOTES, 'UTF-8') . '">' . l('add_new') . '</a>' : '';
	
	echo '<div class="adminpanel">';
	echo '<p class="admintitle">' . l('groupings') . $add . '</p>';
	$num = stats('extras', '');
if ($num === 0) {
echo '' . htmlspecialchars(l('group_not_exist'), ENT_QUOTES, 'UTF-8') . '';
} else {
$query = "SELECT id, name, description FROM " . _PRE . "extras ORDER BY id ASC";
try {
$result = $database->query($query);
if ($result) {
foreach ($result as $r) {
echo '';
echo '' . htmlspecialchars((string)$r['name'], ENT_QUOTES, 'UTF-8') . ' ' . l('divider') . ' ';
echo '' . l('edit') . '';
echo '';
}
} else {
echo '' . htmlspecialchars(l('group_not_exist'), ENT_QUOTES, 'UTF-8') . '';
}
} catch (PDOException $e) {
echo '' . htmlspecialchars(l('group_not_exist'), ENT_QUOTES, 'UTF-8') . '';
}
}
echo '';
}



// BUTTONS (PHP 8.1 safe: strikte array check en veilige taal-fallbacks)
function buttons(): void {
	echo '<div class="clearer"></div>
	<p>' . htmlspecialchars(l('formatting'), ENT_QUOTES, 'UTF-8') . ':
	<br class="clearer" />';
	
	$formatting = array(
		'strong'    => '',
		'em'        => 'key',
		'underline' => 'key',
		'del'       => 'key',
		'p'         => '',
		'br'        => ''
	);
	
	foreach ($formatting as $key => $var) {
		$css = ($var === 'key') ? $key : 'buttons';
		echo '<input type="button" name="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars(l($key), ENT_QUOTES, 'UTF-8') . '" class="' . htmlspecialchars($css, ENT_QUOTES, 'UTF-8') . '" onclick="tag(\'' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '\',\'\',\'\')" value="' . htmlspecialchars(l($key . '_value'), ENT_QUOTES, 'UTF-8') . '" />';
	}
	
	echo '</p><br class="clearer" /><p>' . htmlspecialchars(l('insert'), ENT_QUOTES, 'UTF-8') . ': <br class="clearer" />';
	
	$insert = [
		'img'     => 'js_image1,js_image2',
		'link'    => 'js_link1,js_link2',
		'include' => 'js_file,on',
		'func'    => 'js_func1,js_func2',
		'intro'   => 'on,on'
	];
	
	foreach ($insert as $key => $val) {
		$lng = explode(',', $val);
		$lng0 = $lng[0] ?? '';
		$lng1 = $lng[1] ?? '';
		
		echo '<input type="button" name="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars(l($key), ENT_QUOTES, 'UTF-8') . '" class="buttons" onclick="tag(\'' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '\',\'' . htmlspecialchars(l($lng0), ENT_QUOTES, 'UTF-8') . '\',\'' . htmlspecialchars(l($lng1), ENT_QUOTES, 'UTF-8') . '\')" value="' . htmlspecialchars(l($key . '_value'), ENT_QUOTES, 'UTF-8') . '" />';
	}
	echo '<br class="clearer" /></p>';
}

// ARTICLES FORM (PHP 8.1 safe: Herschreven naar PDO Prepared Statements en explicit variable scopes)
function form_articles(string $contents): void {
	$database = db();
	if (!$database) {
		return;
	}

	// Initialiseer alle formuliervariabelen vooraf om PHP 8.1 Undefined Variable Warnings te voorkomen
	$article_category = 0;
	$edit_option = 1;
	$edit_page = 0;
	$extraid = '';
	$title = '';
	$seftitle = '';
	$text = '';
	$dmeta = '';
	$keywords = '';
	$dtitle = 'YES';
	$dinfo = 'YES';
	$published = 1;
	$showSub = 'NO';
	$showHome = 'YES';
	$artorder = 0;
	$com = '';
	
	$frm_position1 = '';
	$frm_position2 = '';
	$frm_position3 = '';
	$frm_fieldset = '';
	$toggle_div = '';
	$pos = 1;

	if (isset($_GET['id']) && is_numeric($_GET['id'])) {
		$id = (int)$_GET['id'];
		
		// FIX: Veilig herschreven naar Prepared Statement om SQL injection te elimineren
		$query = "SELECT * FROM " . _PRE . "articles WHERE id = :id";
		try {
			$stmt = $database->prepare($query);
			$stmt->execute(['id' => $id]);
			$r = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if ($r) {
				$article_category = (int)($r['category'] ?? 0);
				$edit_option      = (int)($r['position'] ?? 1) === 0 ? 1 : (int)$r['position'];
				$edit_page        = (int)($r['page_extra'] ?? 0);
				$extraid          = (string)($r['extraid'] ?? '');
				$title            = (string)($r['title'] ?? '');
				$seftitle         = (string)($r['seftitle'] ?? '');
				$text             = (string)($r['text'] ?? '');
				$dmeta            = (string)($r['description_meta'] ?? '');
				$keywords         = (string)($r['keywords_meta'] ?? '');
				$dtitle           = (string)($r['displaytitle'] ?? 'YES');
				$dinfo            = (string)($r['displayinfo'] ?? 'YES');
				$published        = (int)($r['published'] ?? 1);
				$showSub          = (string)($r['show_in_subcats'] ?? 'NO');
				$showHome         = (string)($r['show_on_home'] ?? 'YES');
				$artorder         = (int)($r['artorder'] ?? 0);
				$com              = (string)($r['commentable'] ?? '');
				
				switch ($edit_option) {
					case 1:
						$frm_fieldset = l('edit') . ' ' . l('article');
						$toggle_div = 'show';
						$frm_position1 = 'selected="selected"';
						break;
					case 2:
						$frm_fieldset = l('edit') . ' ' . l('extra_contents');
						$toggle_div = 'show';
						$frm_position2 = 'selected="selected"';
						break;
					case 3:
						$frm_fieldset = l('edit') . ' ' . l('page');
						$toggle_div = 'show';
						$frm_position3 = 'selected="selected"';
						break;
				}
			}
		} catch (PDOException $e) {
			return;
		}
		
		$frm_action = _SITE . '?action=process&task=admin_article&id=' . $id;
		$frm_title = $_SESSION[_SITE . 'temp']['title'] ?? $title;
		$frm_sef_title = isset($_SESSION[_SITE . 'temp']['seftitle']) ? cleanSEF($_SESSION[_SITE . 'temp']['seftitle']) : $seftitle;
		$frm_text = isset($_SESSION[_SITE . 'temp']['text']) ? str_replace('&', '&amp;', (string)$_SESSION[_SITE . 'temp']['text']) : $text;
		$frm_meta_desc = isset($_SESSION[_SITE . 'temp']['description_meta']) ? cleanSEF($_SESSION[_SITE . 'temp']['description_meta']) : $dmeta;
		$frm_meta_key = isset($_SESSION[_SITE . 'temp']['keywords_meta']) ? cleanSEF($_SESSION[_SITE . 'temp']['keywords_meta']) : $keywords;
		
		$frm_display_title = ($dtitle === 'YES') ? 'ok' : '';
		$frm_display_info = ($dinfo === 'YES') ? 'ok' : '';
		$frm_publish = ($published === 1) ? 'ok' : '';
		$show_in_subcats = ($showSub === 'YES') ? 'ok' : '';
		$frm_showonhome = ($showHome === 'YES') ? 'ok' : '';
		$frm_commentable = ($com === 'YES' || $com === 'FREEZ') ? 'ok' : '';
		$frm_task = 'edit_article';
		$frm_submit = l('edit_button');
		$pos = $edit_option;
	} else {
		switch ($contents) {
			case 'article_new':
				$edit_option = 1;
				$frm_fieldset = l('article_new');
				$toggle_div = '';
				$pos = 1;
				$frm_position1 = 'selected="selected"';
				break;
			case 'extra_new':
				$edit_option = 2;
				$edit_page = 0;
				$frm_fieldset = l('extra_new');
				$toggle_div = '';
				$pos = 2;
				$frm_position2 = 'selected="selected"';
				break;
			case 'page_new':
				$edit_option = 3;
				$frm_fieldset = l('page_new');
				$toggle_div = '';
				$pos = 3;
				$frm_position3 = 'selected="selected"';
				break;
		}
		
		if (empty($frm_fieldset)) {
			$frm_fieldset = l('article_new');
		}
		
		$artorder = 0;
		$frm_action = _SITE . '?action=process&task=admin_article';
		$frm_title = $_SESSION[_SITE . 'temp']['title'] ?? '';
		$frm_sef_title = isset($_SESSION[_SITE . 'temp']['seftitle']) ? cleanSEF($_SESSION[_SITE . 'temp']['seftitle']) : '';
		$frm_text = $_SESSION[_SITE . 'temp']['text'] ?? '';
		$frm_meta_desc = isset($_SESSION[_SITE . 'temp']['description_meta']) ? cleanSEF($_SESSION[_SITE . 'temp']['description_meta']) : '';
		$frm_meta_key = isset($_SESSION[_SITE . 'temp']['keywords_meta']) ? cleanSEF($_SESSION[_SITE . 'temp']['keywords_meta']) : '';
		
		$frm_display_title = 'ok';
		$frm_display_info = ($contents === 'extra_new') ? '' : 'ok';
		$frm_publish = 'ok';
		$show_in_subcats = 'ok';
		$frm_showonhome = (s('display_new_on_home') === 'on') ? 'ok' : '';
		$frm_commentable = ($contents === 'extra_new' || $contents === 'page_new' || s('enable_comments') !== 'YES') ? '' : 'ok';
		$frm_task = 'add_article';
		$frm_submit = l('submit');
	}
	
	$catnum = stats('categories', '');
	if ($contents === 'article_new' && (int)$catnum < 1) {
		echo htmlspecialchars(l('create_cat'), ENT_QUOTES, 'UTF-8');
	} else {
		echo html_input('form', '', 'post', '', '', '', '', '', '', '', '', '', 'post', $frm_action, '');
		echo '<div class="adminpanel">';
		
		if (!empty($toggle_div) && $toggle_div === 'show') {
			echo '<p class="admintitle"><a onclick="toggle(\'edit_article\')" style="cursor: pointer;" title="' . htmlspecialchars($frm_fieldset, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($frm_fieldset, ENT_QUOTES, 'UTF-8') . '</a></p>';
			echo '<div id="edit_article" style="display: none;">';
		} else {
			echo '<p class="admintitle">' . htmlspecialchars($frm_fieldset, ENT_QUOTES, 'UTF-8') . '</p>';
		}
		
		echo html_input('text', 'title', 'at', $frm_title, l('title'), '', 'onchange="genSEF(this,document.forms[\'post\'].seftitle)"', 'onkeyup="genSEF(this,document.forms[\'post\'].seftitle)"', '', '', '', '', '', '', '');
		
		if ($contents === 'extra_new' || $edit_option === 2) {
			echo '<div style="display: none;">';
			echo html_input('text', 'seftitle', 'as', $frm_sef_title, l('sef_title'), '', '', '', '', '', '', '', '', '', '');
			echo '</div>';
		} else {
			echo html_input('text', 'seftitle', 'as', $frm_sef_title, l('sef_title'), '', '', '', '', '', '', '', '', '', '');
		}
		
		echo html_input('textarea', 'text', 'txt', $frm_text, l('text'), '', '', '', '', '', '2', '100', '', '', '');
		buttons();

				# ARTICLE OR EXTRA (PHP 8.1 safe: prepared loops en scope-initialisatie)
		$contents_str = (string)($contents ?? '');
		$edit_option_int = (int)($edit_option ?? 1);
		$article_category_int = isset($article_category) ? (int)$article_category : -1;
		$edit_page_int = (int)($edit_page ?? 0);
		
		if ($contents_str !== 'page_new' && $edit_option_int !== 3) {
			echo '<p><label for="cat">';
			
			# NEW EXTRA OR ARTICLE TITELS
			echo ($contents_str === 'extra_new' || $edit_option_int === 2) ? htmlspecialchars(l('appear_category'), ENT_QUOTES, 'UTF-8') : htmlspecialchars(l('category'), ENT_QUOTES, 'UTF-8');
			
			if ($contents_str === 'extra_new' || $edit_option_int === 2) {
				echo ':</label><br /><select name="define_category" id="cat" onchange="dependancy(\'extra\');">';
				echo '<option value="-1"' . ($article_category_int === -1 ? ' selected="selected"' : '') . '>' . htmlspecialchars(l('all'), ENT_QUOTES, 'UTF-8') . '</option>';
				echo '<option value="-3"' . ($article_category_int === -3 ? ' selected="selected"' : '') . '>' . htmlspecialchars(l('page_only'), ENT_QUOTES, 'UTF-8') . '</option>';
			} else {
				echo ':</label><br /><select name="define_category" id="cat" onchange="dependancy(\'snews_articles\');">';
			}
			
			# CATEGORIES AND SUB-CATEGORIES (Herschreven naar veilige parameter binding loops)
			$database = db();
			if ($database) {
				$category_query = "SELECT id, name, subcat FROM " . _PRE . "categories WHERE published = 'YES' AND subcat = 0 ORDER BY catorder, id ASC";
				try {
					$cat_result = $database->query($category_query);
					if ($cat_result) {
						// Bereid de subquery vooraf voor om parser warnings in de loop te elimineren
						$subquery_str = "SELECT id, name, subcat FROM " . _PRE . "categories WHERE subcat = :parent_id ORDER BY catorder, id ASC";
						$stmt_sub = $database->prepare($subquery_str);
						
						foreach ($cat_result as $cat) {
							$cat_id = (int)$cat['id'];
							$sel = ($article_category_int === $cat_id) ? ' selected="selected"' : '';
							echo '<option value="' . $cat_id . '"' . $sel . '>' . htmlspecialchars((string)$cat['name'], ENT_QUOTES, 'UTF-8') . '</option>';
							
							// FIX: Veilig uitgevoerd via prepared parameter binding om de array string literal syntax error te tackelen
							$stmt_sub->execute(['parent_id' => $cat_id]);
							while ($s = $stmt_sub->fetch(PDO::FETCH_ASSOC)) {
								$sub_id = (int)$s['id'];
								$sub_sel = ($article_category_int === $sub_id) ? ' selected="selected"' : '';
								echo '<option value="' . $sub_id . '"' . $sub_sel . '>--' . htmlspecialchars((string)$s['name'], ENT_QUOTES, 'UTF-8') . '</option>';
							}
						}
					}
				} catch (PDOException $e) {}
			}
			echo '</select></p>';
			
			# EXTRA COMPONENT RENDERING
			if ($contents_str === 'extra_new' || $edit_option_int === 2) {
				$sub_display = ($article_category_int === -1) ? 'inline' : 'none';
				echo '<div id="def_subcat" style="display:' . $sub_display . ';"><p>';
				echo html_input('checkbox', 'show_in_subcats', 'asc', 'YES', l('show_in_subcats'), '', '', '', '', ($show_in_subcats ?? ''), '', '', '', '', '');
				echo '</p></div>';
				
				$none_display = ($article_category_int === -1) ? 'none' : 'inline';
				echo '<div id="def_page" style="display:' . $none_display . ';"><p>';
				echo '<label for="dp">' . htmlspecialchars(l('appear_page'), ENT_QUOTES, 'UTF-8') . ':</label><br /><select name="define_page" id="dp">';
				echo '<option value="0"' . ($edit_option_int !== 2 ? ' selected="selected"' : '') . '>' . htmlspecialchars(l('all'), ENT_QUOTES, 'UTF-8') . '</option>';
				
				if ($database) {
					$query = "SELECT id, title FROM " . _PRE . "articles WHERE position = 3 ORDER BY id ASC";
					try {
						$result = $database->query($query);
						if ($result) {
							$edit_page_int = (int)($edit_page ?? 0);
							foreach ($result as $r) {
								$sel = ($edit_page_int === (int)$r['id']) ? ' selected="selected"' : '';
								echo '<option value="' . (int)$r['id'] . '"' . $sel . '>' . htmlspecialchars((string)$r['title'], ENT_QUOTES, 'UTF-8') . '</option>';
							}
						}
					} catch (PDOException $e) {}
				}
				echo '</select><br /></p></div>';
			}
		}
		
		if ($contents_str === 'article_new' || $edit_option_int === 1) {
		 	echo html_input('checkbox', 'show_on_home', 'sho', 'YES', l('show_on_home'), '', '', '', '', ($frm_showonhome ?? ''), '', '', '', '', '');
		}
		echo html_input('checkbox', 'publish_article', 'pu', 'YES', l('publish_article'), '', '', '', '', ($frm_publish ?? ''), '', '', '', '', '');
		
		if (!empty($toggle_div) && $toggle_div === 'show') {
			echo '</div>';
		}
		echo '</div>';
		
		# PREVIEW PANEL
		echo '<div class="adminpanel">';
		echo '<p class="admintitle"><a onclick="toggle(\'preview\')" style="cursor: pointer;" title="' . htmlspecialchars(l('preview'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(l('preview'), ENT_QUOTES, 'UTF-8') . '</a></p>';
		echo '<div id="preview" style="display: none;"></div>';
		echo '</div>';
		
		# CUSTOMIZE PANEL
		echo '<div class="adminpanel">';
		echo '<p class="admintitle"><a onclick="toggle(\'customize\')" style="cursor: pointer;" title="' . htmlspecialchars(l('customize'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(l('customize'), ENT_QUOTES, 'UTF-8') . '</a></p>';
		echo '<div id="customize" style="display: none;">';
		
		# EXTRA OPTIONS
		if ($contents_str === 'extra_new' || $edit_option_int === 2) {
			if (s('enable_extras') === 'YES') {
				echo '<p><label for="ext">' . htmlspecialchars(l('define_extra'), ENT_QUOTES, 'UTF-8') . '</label><br /><select name="define_extra" id="ext">';
				if ($database) {
					$extra_query = "SELECT id, name FROM " . _PRE . "extras ORDER BY id ASC";
					try {
						$extra_result = $database->query($extra_query);
						if ($extra_result) {
							$extraid_str = (string)($extraid ?? '');
							foreach ($extra_result as $ex) {
								$sel = ($contents_str !== 'extra_new' && $extraid_str === (string)$ex['id']) ? ' selected="selected"' : '';
								echo '<option value="' . (int)$ex['id'] . '"' . $sel . '>' . htmlspecialchars((string)$ex['name'], ENT_QUOTES, 'UTF-8') . '</option>';
							}
						}
					} catch (PDOException $e) {}
				}
				echo '</select></p>';
			} else {
				echo html_input('hidden', 'define_extra', 'ext', '1', '', '', '', '', '', '', '', '', '', '', '');
			}
		}
		
		# NEW POSITION OPTIONS
		if (!empty($id)) {
			echo '<p><label for="pos">' . htmlspecialchars(l('position'), ENT_QUOTES, 'UTF-8') . ':</label><br /><select name="position" id="pos">';
			echo '<option value="1"' . ($frm_position1 ?? '') . '>' . htmlspecialchars(l('center'), ENT_QUOTES, 'UTF-8') . '</option>';
			echo '<option value="2"' . ($frm_position2 ?? '') . '>' . htmlspecialchars(l('side'), ENT_QUOTES, 'UTF-8') . '</option>';
			echo '<option value="3"' . ($frm_position3 ?? '') . '>' . htmlspecialchars(l('display_page'), ENT_QUOTES, 'UTF-8') . '</option>';
			echo '</select></p>';
		} else {
			$pos = $contents_str === 'article_new' ? 1 : ($contents_str === 'extra_new' ? 2 : 3);
			echo html_input('hidden', 'position', 'position', (string)$pos, '', '', '', '', '', '', '', '', '', '', '');
		}
		
		if ($contents_str !== 'extra_new' && $edit_option_int !== 2) {
			echo html_input('text', 'description_meta', 'dm', ($frm_meta_desc ?? ''), l('description_meta'), '', '', '', '', '', '', '', '', '', '');
			echo html_input('text', 'keywords_meta', 'km', ($frm_meta_key ?? ''), l('keywords_meta'), '', '', '', '', '', '', '', '', '', '');
		}
		echo html_input('checkbox', 'display_title', 'dti', 'YES', l('display_title'), '', '', '', '', ($frm_display_title ?? ''), '', '', '', '', '');
		
		if ($contents_str !== 'extra_new' && $edit_option_int !== 2) {
			echo html_input('checkbox', 'display_info', 'di', 'YES', l('display_info'), '', '', '', '', ($frm_display_info ?? ''), '', '', '', '', '');
			echo html_input('checkbox', 'commentable', 'ca', 'YES', l('enable_commenting'), '', '', '', '', ($frm_commentable ?? ''), '', '', '', '', '');
			
			// FIX: Voorkom PHP 8.1 undefined variable warnings op $r door eerst isset check uit te voeren
			if (!empty($id) && isset($r) && is_array($r)) {
				$comment_status_db = (string)($r['commentable'] ?? '');
				$checked_attr = ($comment_status_db === 'FREEZ') ? ' checked="checked"' : '';
				echo '<p><input name="freeze" type="checkbox" id="fc"' . $checked_attr . ' /> <label for="fc"> ' . htmlspecialchars(l('freeze_comments'), ENT_QUOTES, 'UTF-8') . '</label></p>';
			}
		}
		echo '</div></div>';
		
		# PUBLISH DATE SCHEDULER
		if ($contents_str === 'article_new' || $edit_option_int === 1) {
			echo '<div class="adminpanel">';
			echo '<p class="admintitle"><a onclick="toggle(\'admin_publish_date\')" style="cursor: pointer;" title="' . htmlspecialchars(l('publish_date'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(l('publish_date'), ENT_QUOTES, 'UTF-8') . '</a></p>';
			echo '<div id="admin_publish_date" style="display: none;">';

			$onoff_status = (isset($r) && is_array($r) && (string)($r['published'] ?? '') === '2') ? 'ok' : '';
			echo html_input('checkbox', 'fposting', 'fp', 'YES', l('enable'), '', '', '', '', $onoff_status, '', '', '', '', '');
			echo '<p>' . htmlspecialchars(l('server_time'), ENT_QUOTES, 'UTF-8') . ': ' . date('Y-m-d H:i:s') . '</p>';
			echo '<p>' . htmlspecialchars(l('article_date'), ENT_QUOTES, 'UTF-8') . '</p>';

			if (!empty($id) && isset($r) && is_array($r) && !empty($r['date'])) {
				posting_time((string)$r['date']);
			} else {
				posting_time();
			}
			echo '</div></div>';
		}

		echo '<p>';
		echo html_input('hidden', 'action', 'action', 'process', '', '', '', '', '', '', '', '', '', '', '');
		echo html_input('hidden', 'task', 'task', 'admin_article', '', '', '', '', '', '', '', '', '', '', '');
		echo html_input('submit', $frm_task, $frm_task, $frm_submit, '', 'button', '', '', '', '', '', '', '', '', '');

		if (!empty($id)) {
			echo html_input('hidden', 'artorder', 'artorder', (string)$artorder, '', '', '', '', '', '', '', '', '', '', '');
			echo html_input('hidden', 'article_category', 'article_category', (string)$article_category, '', '', '', '', '', '', '', '', '', '', '');
			echo html_input('hidden', 'id', 'id', (string)$id, '', '', '', '', '', '', '', '', '', '', '') . ' ';
			echo html_input('submit', 'delete_article', 'delete_article', l('delete'), '', 'button', 'onclick="return pop(\'' . htmlspecialchars(l('js_delete2'), ENT_QUOTES, 'UTF-8') . '\')"', '', '', '', '', '', '', '', '');
		}
		echo '</p></form>';
    	} // <-- VOEG DEZE TOE (sluit de 'else' op regel 812)
		} // <-- (sluit de functie 'form_articles' op regel 664)



// PRINT ARTICLE DETAILS (PHP 8.1 safe: strikte string casting en veilige datum parsing)
function printArticleDetails(array $articleData, string $articleClass, mixed $order): void {
	$order_input = '';
	if ($order !== null) {
		$order_input = '<input type="text" name="page_' . (int)$articleData['id'] . '" value="' . (int)$articleData['artorder'] . '" size="1" tabindex="' . (int)$order . '" /> &nbsp;';
	}
	
	$date_formatted = !empty($articleData['date']) ? date((string)s('date_format'), strtotime((string)$articleData['date'])) : '';
	echo '<p>' . $order_input . '<strong title="' . htmlspecialchars($date_formatted, ENT_QUOTES, 'UTF-8') . '">	' . htmlspecialchars((string)$articleData['title'], ENT_QUOTES, 'UTF-8') . '</strong> ';
	
	$url = isset($articleData['catSEF']) ? (string)$articleData['catSEF'] . '/' . (string)$articleData['seftitle'] : (string)$articleData['seftitle'];
	if ($articleClass !== 'extra_contents') {
		echo l('divider') . ' <a href="' . _SITE . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '/">' . l('view') . '</a> ';
	}
	
	if (isset($articleData['default_page']) && $articleData['default_page'] !== 'YES') {
		echo l('divider') . ' <a href="' . _SITE . '?action=admin_article&amp;id=' . (int)$articleData['id'] . '">' . l('edit') . '</a> ';
	}
	
	$visiblity = (string)($articleData['visible'] ?? 'YES') === 'YES' ?
		'<a href="' . _SITE . '?action=hide&amp;item=' . htmlspecialchars($articleClass, ENT_QUOTES, 'UTF-8') . '&amp;id=' . (int)$articleData['id'] . '">' . l('hide') . '</a>' :
		l('hidden') . ' ( <a href="' . _SITE . '?action=show&amp;item=' . htmlspecialchars($articleClass, ENT_QUOTES, 'UTF-8') . '&amp;id=' . (int)$articleData['id'] . '">' . l('show') . '</a> )';
		
	echo ' ' . l('divider') . ' ' . $visiblity;
	
	if ((int)($articleData['published'] ?? 1) === 2) {
		echo l('divider') . ' [' . l('status') . ' ' . l('future_posting') . ']';
	}
	if ((int)($articleData['published'] ?? 1) === 0) {
		echo l('divider') . ' [' . l('status') . ' ' . l('unpublished') . ']';
	}
	echo '</p>';
}

// ARTICLES (PHP 8.1 safe: Herschreven naar PDO Prepared Statements en herstelde array inspecties)
function admin_articles(string $contents): void {
	$database = db();
	if (!$database) {
		return;
	}

	global $categorySEF, $subcatSEF;
	$categorySEF_str = (string)($categorySEF ?? '');
	$subcatSEF_str = (string)($subcatSEF ?? '');
	
	$link = '<a href="' . _SITE . htmlspecialchars($categorySEF_str, ENT_QUOTES, 'UTF-8') . '/';
	$item = $contents === 'extra_view' ? 'extra_contents' : 'snews_articles';
	
	$filter_year = defined('DBTYPE') && DBTYPE === 'sqlite' ? "strftime('%Y', date)" : "YEAR(date)";
	$filter_month = defined('DBTYPE') && DBTYPE === 'sqlite' ? "strftime('%m', date)" : "MONTH(date)";
	
	$p = 1;
	$qw = 'position < 2 AND position > -1';
	$title = l('articles');
	$sef = 'article_new';
	$goto = 'snews_articles';

	switch ($contents) {
		case 'article_view':
			$title = l('articles');
			$sef = 'article_new';
			$goto = 'snews_articles';
			$p = 1;
			$qw = 'position < 2 AND position > -1';
			break;
		case 'extra_view':
			$title = l('extra_contents');
			$sef = 'extra_new';
			$goto = 'extra_contents';
			$p = 2;
			$qw = 'position = 2';
			break;
		case 'page_view':
			$title = l('pages');
			$sef = 'page_new';
			$p = 3;
			$goto = 'snews_pages';
			$qw = 'position = 3';
			break;
	}

	# PUT OPTIONS IN ARRAY (PHP 8.1 safe array initialisatie zonder loze pointers)
	$option = ['option' => '', 'extraid' => '', 'year' => '', 'month' => ''];
	if (!empty($subcatSEF_str) && $subcatSEF_str !== 'extras') {
		$array = explode(';', $subcatSEF_str);
		foreach ($array as $key_item) {
			$val = explode('=', $key_item);
			if (!isset($val[1])) {
				$option['option'] = $val[0];
			} else {
				$option[$val[0]] = $val[1];
			}
		}
	}
	
	$mextra = (s('enable_extras') === 'YES' && $p === 2 && empty($option['extraid'])) ? ' / ' . $link . 'extras/">' . l('extra') . '</a>' : '';
	$filter_extra = !empty($option['extraid']) ? ';extraid=' . $option['extraid'] : '';
	
	if (stats('articles', $p) > 0) {
		$add = ' - <a href="' . $sef . '/" title="' . htmlspecialchars(l('add_new'), ENT_QUOTES, 'UTF-8') . '">' . l('add_new') . '</a> - ' . l('see') . ' (' . $link . '">' . l('all') . '</a>) - ' . l('filter') . ' (' . $link . 'year' . $filter_extra . '">' . l('year') . '</a> / ' . $link . 'month' . $filter_extra . '">' . l('month') . '</a>' . $mextra . ')';
	} else {
		$add = '';
	}
	
	# MULTI-EXTRAS
	if ($subcatSEF_str === 'extras') {
		echo '<div class="adminpanel">';
		echo '<p class="admintitle">' . l('articles') . ' - ' . l('filter') . ' <span style="color: #0000FF">' . htmlspecialchars($subcatSEF_str, ENT_QUOTES, 'UTF-8') . '</span> - ' . l('see') . ' (' . $link . '">' . l('all') . '</a>) - ' . l('filter') . ' (' . $link . 'year">' . l('year') . '</a> / ' . $link . 'month">' . l('month') . '</a>)</p>';
		
		$xquery = "SELECT id, name, seftitle FROM " . _PRE . "extras";
		try {
			if ($xres = $database->query($xquery)) {
				foreach ($xres as $rpx) {
					echo '<p>' . $link . 'extraid=' . (int)$rpx['id'] . '">' . htmlspecialchars((string)$rpx['name'], ENT_QUOTES, 'UTF-8') . '</a></p>';
				}
			}
		} catch (PDOException $e) {}
		echo '</div>';
		return;
	}
	
	# OPTION YEAR AND MONTH
	if ($subcatSEF_str === 'year' || $subcatSEF_str === 'month' || !empty($option['option'])) {
		$filter = ($subcatSEF_str === 'year' || $option['option'] === 'year') ? l('year') : l('month');
		echo '<div class="adminpanel">';
		echo '<p class="admintitle">' . l('articles') . ' - ' . l('filter') . ' <span style="color: #0000FF">' . htmlspecialchars($filter, ENT_QUOTES, 'UTF-8') . '</span> - ' . l('see') . ' (' . $link . '">' . l('all') . '</a>) - ' . l('filter') . ' (' . $link . 'year' . $filter_extra . '">' . l('year') . '</a> / ' . $link . 'month' . $filter_extra . '">' . l('month') . '</a> ' . $mextra . ')</p><br /><p>';
		
		$month_names = explode(', ', l('month_names'));
		$query = "SELECT DISTINCT($filter_year) AS dyear FROM " . _PRE . "articles WHERE $qw ORDER BY date DESC";
		
		try {
			if ($sqr = $database->query($query)) {
				foreach ($sqr as $r) {
					$ryear = (string)$r['dyear'];
					echo ($subcatSEF_str === 'month' || $option['option'] === 'month') ?
						'<span style="color: #0000FF">' . htmlspecialchars($ryear, ENT_QUOTES, 'UTF-8') . '</span>' :
						$link . 'year=' . htmlspecialchars($ryear, ENT_QUOTES, 'UTF-8') . $filter_extra . '">' . htmlspecialchars($ryear, ENT_QUOTES, 'UTF-8') . '</a> ';
						
					if ($subcatSEF_str === 'month' || $option['option'] === 'month') {
						$qx = "SELECT DISTINCT(" . $filter_month . ") AS dmonth FROM " . _PRE . "articles WHERE $qw AND $filter_year = :ryear ORDER BY date ASC";
						$stmt_month = $database->prepare($qx);
						$stmt_month->execute(['ryear' => $ryear]);
						
						while ($rx = $stmt_month->fetch(PDO::FETCH_ASSOC)) {
							$m = (int)$rx['dmonth'] - 1;
							$month_label = $month_names[$m] ?? (string)$rx['dmonth'];
							echo ' ' . l('divider') . ' ' . $link . 'year=' . htmlspecialchars($ryear, ENT_QUOTES, 'UTF-8') . ';' . 'month=' . (int)$rx['dmonth'] . $filter_extra . '">' . htmlspecialchars($month_label, ENT_QUOTES, 'UTF-8') . '</a> ';
						}
					}
					echo '<br />';
				}
			}
		} catch (PDOException $e) {}
		echo '</p></div>';
		return;
	}
	
	$year = $option['year'] !== '' ? $option['year'] : '';
	$month = $option['month'] !== '' ? $option['month'] : '';
	
	$queryParams = [];
	$filterquery = '';
	if (!empty($year)) {
		$filterquery .= " AND $filter_year = :year";
		$queryParams['year'] = $year;
	}
	if (!empty($month)) {
		$filterquery .= " AND $filter_month = :month";
		$queryParams['month'] = $month;
	}
	
	$no_content = !empty($filterquery) ? '<p>' . htmlspecialchars(l('no_content_for_filter'), ENT_QUOTES, 'UTF-8') . '</p>' : '<p>' . htmlspecialchars(l('article_not_exist'), ENT_QUOTES, 'UTF-8') . '</p>';
	
	echo html_input('form', '', 'post', '', '', '', '', '', '', '', '', '', 'post', '?action=process&amp;task=reorder', '');
	echo '<div class="adminpanel">';
	echo '<p class="admintitle">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . $add . '</p>';
	echo '<p><input type="hidden" name="order" id="order" value="' . htmlspecialchars($goto, ENT_QUOTES, 'UTF-8') . '" /></p>';
	
	# EXTRA CONTENT RENDERING GECORRIGEERD
	if ($contents === 'extra_view') {
		$sqx_clause = '';
		if (!empty($option['extraid'])) {
			$sqx_clause = " AND extraid = :extraid";
			$queryParams['extraid'] = (int)$option['extraid'];
		}
		
		$cat_array_irregular = ['-1', '-3'];
		foreach ($cat_array_irregular as $cat_value) {
			$cat_value_int = (int)$cat_value;
			$legend_label = $cat_value_int === -3 ? l('pages') : l('all');
			$page_only_xsql = $cat_value_int === -3 ? 'page_extra ASC,' : '';
			
			$sql = "SELECT id, title, seftitle, date, published, artorder, visible, default_page, page_extra
				FROM " . _PRE . "articles
				WHERE category = :cat_value
					AND position = :position $filterquery $sqx_clause
				ORDER BY $page_only_xsql artorder ASC, date DESC";
				
			$loopParams = array_merge($queryParams, ['cat_value' => $cat_value_int, 'position' => $p]);
			
			$num_rows = stats('articles', '', "category = " . $cat_value_int . " AND position = " . $p . " " . (!empty($filterquery) ? "AND $filter_year = " . (int)$year : ''));
			$tab = 1;
			
			echo '<div class="innerpanel">';
			echo '<p class="admintitle">' . htmlspecialchars($legend_label, ENT_QUOTES, 'UTF-8') . '</p>';
			
			if ($num_rows === 0) {
				echo $no_content;
			} else {
				try {
					$stmt_art = $database->prepare($sql);
					$stmt_art->execute($loopParams);
					$lbl_filter = -5;
					
					while ($r = $stmt_art->fetch(PDO::FETCH_ASSOC)) {
						if ($cat_value_int === -3 && $lbl_filter !== (int)$r['page_extra']) {
							$assigned_page_res = retrieve('title', 'articles', 'id', $r['page_extra']);
							$assigned_page = is_array($assigned_page_res) ? ($assigned_page_res['title'] ?? '') : (string)$assigned_page_res;
							echo !$assigned_page ? htmlspecialchars(l('all_pages'), ENT_QUOTES, 'UTF-8') : htmlspecialchars($assigned_page, ENT_QUOTES, 'UTF-8');
							}


						printArticleDetails($r, $item, $tab);
						$tab++;
						$lbl_filter = (int)$r['page_extra'];
						}
						} catch (PDOException $e) {
						echo $no_content;
						}
						}
						echo '';
						}
						}


		# ARTICLE OR EXTRA (PHP 8.1 safe: prepared loops en strict arrays)
	if ($contents === 'article_view' || $contents === 'extra_view') {
		$num = stats('categories', '', 'subcat = 0');
		if ($num === 0) {
			echo '<p>' . htmlspecialchars(l('no_categories'), ENT_QUOTES, 'UTF-8') . '</p>';
		} else {
			$nsql = ($p === 1 ? "category < 1" : "") . ($p === 2 ? "category = 0" : "");
			
			// Initialiseer filters en fallbacks voor de database binding
			$filterquery = $filterquery ?? '';
			$subquery = $subquery ?? '';
			$no_content = $no_content ?? '';
			$tab = $tab ?? 1;
			
			$num_rows = stats('articles', '', $nsql . ' AND position = ' . $p . ' ' . $subquery);
			$sql = "SELECT id, title, seftitle, date, published, artorder, visible, default_page
				FROM " . _PRE . "articles
				WHERE " . $nsql . " AND position = :position
				ORDER BY artorder ASC, date DESC";
				
			try {
				if ($num_rows > 0) {
					echo '<div class="innerpanel">';
					echo '<p class="admintitle">' . htmlspecialchars(l('no_category_set'), ENT_QUOTES, 'UTF-8') . '</p>';
					
					$stmt = $database->prepare($sql);
					$stmt->execute(['position' => $p]);
					while ($O = $stmt->fetch(PDO::FETCH_ASSOC)) {
						printArticleDetails($O, $item, $tab);
						$tab++;
					}
					echo '</div>';
				}
				
				$cat_query = "SELECT id, name, seftitle FROM " . _PRE . "categories WHERE subcat = 0";
				$cat_res = $database->query($cat_query);
				
				if ($cat_res) {
					// Bereid de dynamische queries vooraf voor om loopprestaties in PHP 8.1 te optimaliseren
					$sql1 = "SELECT id, title, seftitle, date, published, artorder, visible, default_page
						FROM " . _PRE . "articles
						WHERE category = :cat_id AND position = :position $filterquery
						ORDER BY artorder ASC, date DESC";
					$stmt_sql1 = $database->prepare($sql1);
					
					$query2 = "SELECT id, name, seftitle FROM " . _PRE . "categories WHERE subcat = :cat_id ORDER BY catorder ASC";
					$stmt_query2 = $database->prepare($query2);
					
					$catart_sql2 = "SELECT id, title, seftitle, date, published, artorder, visible, default_page
						FROM " . _PRE . "articles
						WHERE category = :subcat_id $subquery $filterquery
						ORDER BY category ASC, artorder ASC, date DESC";
					$stmt_catart2 = $database->prepare($catart_sql2);
					
					foreach ($cat_res as $row) {
						$row_id = (int)$row['id'];
						echo '<div class="adminpanel">';
						echo '<p class="admintitle">' . htmlspecialchars((string)$row['name'], ENT_QUOTES, 'UTF-8') . '</p>';
						
						// FIX: Veilig uitgevoerd via prepared parameter binding om de array string literal syntax error te elimineren
						$stmt_sql1->execute(['cat_id' => $row_id, 'position' => $p]);
						$articles_row = $stmt_sql1->fetchAll(PDO::FETCH_ASSOC);
						
						$num_rows = stats('articles', '', 'category = ' . $row_id . ' AND position = ' . $p . ' ' . $subquery . ' ' . $filterquery);
						if ($num_rows === 0) {
							echo $no_content;
						} else {
							foreach ($articles_row as $r) {
								$r['catSEF'] = $row['seftitle'];
								printArticleDetails($r, $item, $tab);
								$tab++;
							}
						}
						
						$stmt_query2->execute(['cat_id' => $row_id]);
						$tab2 = 1;
						
						while ($row2 = $stmt_query2->fetch(PDO::FETCH_ASSOC)) {
							$row2_id = (int)$row2['id'];
							echo '<a class="subcat" onclick="toggle(\'subcat' . $row2_id . '\')" style="cursor: pointer;">' . htmlspecialchars((string)$row2['name'], ENT_QUOTES, 'UTF-8') . '</a><br />';
							echo '<div id="subcat' . $row2_id . '" style="display: none;" class="subcat">';
							
							$stmt_catart2->execute(['subcat_id' => $row2_id]);
							$num_rows2 = stats('articles', '', 'category = ' . $row2_id . ' ' . $subquery . ' ' . $filterquery);
							if ($num_rows2 === 0) {
								echo $no_content;
							}
							
							while ($ca_r2 = $stmt_catart2->fetch(PDO::FETCH_ASSOC)) {
								$ca_r2['catSEF'] = cat_rel($row2_id, 'seftitle');
								printArticleDetails($ca_r2, 'snews_articles', $tab2);
							}
							echo '</div>';
							$tab2++;
						}
						echo '</div>';
					}
				}
			} catch (PDOException $e) {}
		}
	# PAGE
	} elseif ($contents === 'page_view') {
		$tab = $tab ?? 1;
		$subquery = $subquery ?? '';
		$filterquery = $filterquery ?? '';
		
		$sql = "SELECT id, title, seftitle, date, published, artorder, visible, default_page
			FROM " . _PRE . "articles
			WHERE position = 3 $subquery $filterquery
			ORDER BY artorder ASC, date DESC";
			
		$num_rows = stats('articles', '', 'position = 3 ' . $subquery);
		if ($num_rows === 0) {
			echo '<p>' . htmlspecialchars(l('article_not_exist'), ENT_QUOTES, 'UTF-8') . '</p>';
		} else {
			try {
				$result = $database->query($sql);
				if ($result) {
					foreach ($result as $r) {
						printArticleDetails($r, 'snews_pages', $tab);
						$tab++;
					}
				}
			} catch (PDOException $e) {}
		}
	}
	
	echo html_input('hidden', 'action', 'action', 'process', '', '', '', '', '', '', '', '', '', '', '');
	echo html_input('hidden', 'task', 'task', 'reorder', '', '', '', '', '', '', '', '', '', '', '');
	echo '<p>';
	echo html_input('submit', 'reorder', 'reorder', l('order_content'), '', 'button', '', '', '', '', '', '', '', '', '');
	echo '</p></div></form>';
}

// COMMENTS - EDIT (PHP 8.1 safe: prepared selection & explicit string casting fallbacks)
function edit_comment(): void {
	$database = db();
	if (!$database || !isset($_GET['commentid'])) {
		return;
	}

	$commentid = (int)$_GET['commentid'];
	$query = "SELECT id, articleid, name, url, comment, approved FROM " . _PRE . "comments WHERE id = :id";
	
	try {
		$stmt = $database->prepare($query);
		$stmt->execute(['id' => $commentid]);
		$r = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($r) {
			$articleid_val = (int)$r['articleid'];
			$articleTITLE_res = retrieve('title', 'articles', 'id', $articleid_val);
			$articleTITLE = is_array($articleTITLE_res) ? ($articleTITLE_res['title'] ?? '') : (string)$articleTITLE_res;
			
			// FIX: String-casting voorkomt type errors onder PHP 8.1 mocht de waarde NULL zijn
			$comment = stripslashes((string)$r['comment']);
			$comment = str_replace('<br />', '', $comment);
			
			echo html_input('form', '', 'post', '', '', '', '', '', '', '', '', '', 'post', '?action=process&amp;task=editcomment&amp;id=' . $commentid, '');
			echo '<div class="adminpanel">';
			echo '<p class="admintitle">' . l('edit_comment') . ' (<strong> ' . htmlspecialchars($articleTITLE, ENT_QUOTES, 'UTF-8') . '</strong> )</p>';
			echo html_input('textarea', 'editedcomment', 'ec', $comment, l('comment'), '', '', '', '', '', '2', '100', '', '', '');
			echo html_input('text', 'name', 'n', (string)$r['name'], l('name'), '', '', '', '', '', '', '', '', '', '');
			echo html_input('text', 'url', 'url', (string)$r['url'], l('url'), '', '', '', '', '', '', '', '', '', '');
			echo html_input('checkbox', 'approved', 'a', '', l('approved'), '', '', '', '', (string)$r['approved'] === 'True' ? 'ok' : '', '', '', '', '', '');
			echo '</div><p>';
			
			echo html_input('hidden', 'id', 'id', (string)$articleid_val, '', '', '', '', '', '', '', '', '', '', '');
			echo html_input('hidden', 'action', 'action', 'process', '', '', '', '', '', '', '', '', '', '', '');
			echo html_input('hidden', 'task', 'task', 'editcomment', '', '', '', '', '', '', '', '', '', '', '');
			echo html_input('submit', 'submit_text', 'submit_text', l('edit'), '', 'button', '', '', '', '', '', '', '', '', '') . ' ';
			echo html_input('hidden', 'commentid', 'commentid', (string)$r['id'], '', '', '', '', '', '', '', '', '', '', '');
			echo html_input('submit', 'delete_text', 'delete_text', l('delete'), '', 'button', 'onclick="return pop(\'' . htmlspecialchars(l('js_delete2'), ENT_QUOTES, 'UTF-8') . '\')"', '', '', '', '', '', '', '', '');
			echo '</p></form>';
		}
	} catch (PDOException $e) {}
}

// FILES (PHP 8.1 safe: move_uploaded_file fix, strict type vergelijkingen & variable scopes)
function files(): void {
	$upload_file = isset($_POST['upload']) ? $_POST['upload'] : null;
	$remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
	
	$ip = (isset($_POST['ip']) && $_POST['ip'] === $remote_addr) ? $_POST['ip'] : null;
	$time = (isset($_POST['time']) && (time() - (int)$_POST['time']) > 4) ? $_POST['time'] : null;
	
	if ($ip && $time && $upload_file && _ADMIN) {
		$ignore = explode(',', l('ignored_items'));
		$file_types = explode(',', (string)s('allowed_files'));
		$image_types = explode(',', (string)s('allowed_images'));
		$extension = array_merge($file_types, $image_types);
		
		if (!empty($_FILES['imagefile']['name']) && !empty($_FILES['imagefile']['tmp_name'])) {
			$filetemp = $_FILES['imagefile']['tmp_name'];
			$filename = strtolower(basename($_FILES['imagefile']['name']));
			$filetype = $_FILES['imagefile']['type'];
			
			$ext_check = strrchr($filename, '.');
			$file_ext = $ext_check !== false ? substr($ext_check, 1) : '';
			
			if (!in_array($file_ext, $extension, true) || in_array($filename, $ignore, true)) {
				die(notification(2, l('file_error'), 'snews_files'));
			} else {
				$upload_dir = rtrim((string)($_POST['upload_dir'] ?? '.'), '/');
				
				// Beveiliging: zorg dat de directory validatie klopt
				if (!preg_match('/^[a-z0-9_\-.\/]+$/i', $upload_dir)) {
					die(notification(2, l('error_file_name'), 'snews_files'));
				}
				
				$target_path = $upload_dir . '/' . $filename;
				
				// FIX: copy() vervangen door de verplichte move_uploaded_file() standaard
				if (move_uploaded_file($filetemp, $target_path)) {
					echo notification(0, '', 'snews_files');
					$kb_size = round(($_FILES['imagefile']['size'] / 1024), 1);
					echo '<p><a href="' . htmlspecialchars($target_path, ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8') . '</a> [' . $kb_size . ' KB] [' . htmlspecialchars($filetype, ENT_QUOTES, 'UTF-8') . ']</p>';
				} else {
					die(l('file_error'));
				}
			}
		} else {
			die(notification(2, l('file_error'), 'snews_files'));
		}
	} else {
		// FIX: isset check en taakvergelijking opgesplitst om PHP 8.1 logic warnings te voorkomen
		if (isset($_GET['task']) && $_GET['task'] === 'delete' && isset($_GET['folder']) && isset($_GET['file'])) {
			$folder_clean = preg_replace('/[^a-zA-Z0-9_\-.\/]/', '', $_GET['folder']);
			$file_clean = preg_replace('/[^a-zA-Z0-9_\-.]/', '', $_GET['file']);
			
			$file_to_delete = rtrim($folder_clean, '/') . '/' . $file_clean;
			if (file_exists($file_to_delete) && is_file($file_to_delete)) {
				@unlink($file_to_delete);
			}
			echo notification(0, '', 'snews_files');
		} else {
			$upload_dir_post = (string)($_POST['upload_dir'] ?? '');
			
			echo '<div class="adminpanel">';
			echo '<p class="admintitle">' . l('upload') . '</p>';
			echo '<form method="post" action="' . _SITE . 'snews_files/" enctype="multipart/form-data">';
			echo '<p>' . l('uploadto') . '&nbsp;&nbsp;&nbsp;<select name="upload_dir" id="ud1">';
			echo '<option value=".">..</option>';
			filelist('option', ".");
			echo '</select></p><p>' . l('uploadfrom') . '&nbsp;&nbsp;&nbsp;<input type="file" name="imagefile" /></p><p>';
			echo html_input('hidden', 'ip', 'ip1', $remote_addr, '', '', '', '', '', '', '', '', '', '', '');
			echo html_input('hidden', 'time', 'time1', (string)time(), '', '', '', '', '', '', '', '', '', '', '');
			echo html_input('submit', 'upload', 'upload', l('upload'), '', 'button', '', '', '', '', '', '', '', '', '');
			echo '</p></form></div>';
			
			echo '<div class="adminpanel">';
			$dir_title = empty($upload_dir_post) ? ' root' : ' ' . str_replace('.', 'root', htmlspecialchars($upload_dir_post, ENT_QUOTES, 'UTF-8'));
			echo '<p class="admintitle">' . l('view_files') . $dir_title . '</p>';
			echo '<form method="post" action="' . _SITE . 'snews_files/" enctype="multipart/form-data">';
			echo '<p><select name="upload_dir" id="ud2"><option value=".">..</option>';
			filelist('option', ".");
			echo '</select>';
			
			// FIX: Loze variabele $file gedefinieerd als lege string om warnings te voorkomen
			echo html_input('hidden', 'file', 'file', '', '', '', '', '', '', '', '', '', '', '', '');
			echo html_input('hidden', 'ip', 'ip2', $remote_addr, '', '', '', '', '', '', '', '', '', '', '');
			echo html_input('hidden', 'time', 'time2', (string)time(), '', '', '', '', '', '', '', '', '', '', '');
			echo html_input('submit', 'show', 'show', l('view'), '', 'button', '', '', '', '', '', '', '', '', '');
			
			$handle = (strlen($upload_dir_post) > 2) ? $upload_dir_post : ".";
			echo '</p><p>';
			filelist('list', $handle);
			echo '</p></form></div>';
		}
	}
}

// FILELIST FUNCTION (PHP 8.1 safe: opendir en strict types check)
function filelist(string $mode, string $path, int $depth = 0): void {
	$ignore = explode(',', l('ignored_items'));
	$file_types = explode(',', (string)s('allowed_files'));
	$image_types = explode(',', (string)s('allowed_images'));
	$types = array_merge($file_types, $image_types);
	
	$view_dir_post = (string)($_POST['view_dir'] ?? '');
	
	if (is_dir($path) && ($dh = @opendir($path))) {
		while (false !== ($file = readdir($dh))) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			$target = rtrim($path, '/') . '/' . $file;
			if (!in_array($file, $ignore, true)) {
				$spaces = str_repeat(l('divider') . ' ', $depth);
				switch (true) {
					case ($mode === 'option' && is_dir($target)):
						$selected = $view_dir_post === $target ? ' selected="selected"' : '';
						echo '<option value="' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . $spaces . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '</option>';
						filelist('option', $target, ($depth + 1));
						break;
					case ($mode === 'list' && is_file($target)):
						$ext_check = strrchr($target, '.');
						$ext = $ext_check !== false ? substr($ext_check, 1) : '';
						if (in_array($ext, $types, true)) {
							echo '<a href="' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '" title="' . l('view') . ' ' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '</a> ' . l('divider') . ' ';
							echo '<a href="' . _SITE . '?action=snews_files&amp;task=delete&amp;folder=' . urlencode($path) . '&amp;file=' . urlencode($file) . '" title="' . l('delete') . ' ' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '" onclick="return pop(\'' . htmlspecialchars(l('js_delete2'), ENT_QUOTES, 'UTF-8') . '\')"> ' . l('delete') . '</a><br />';
						}
						break;
				}
			}
		}
		closedir($dh);
	}
}

// CHECK IF UNIQUE (Volledig herschreven naar veilige dynamische PDO Prepared Statements!)
function check_if_unique(string $what, mixed $text, mixed $not_id = 'x', mixed $subcat = 0): bool {
	$database = db();
	if (!$database) {
		return false;
	}

	$text = clean($text);
	$query = "";
	$params = [':text' => $text];

	switch ($what) {
		case 'article_seftitle':
			$query = "SELECT count(DISTINCT id) as total FROM " . _PRE . "articles WHERE seftitle = :text";
			if (!empty($not_id) && $not_id !== 'x') {
				$query .= " AND category = :not_id";
				$params['not_id'] = (int)$not_id;
			}
			break;
		case 'article_title':
			$query = "SELECT count(DISTINCT id) as total FROM " . _PRE . "articles WHERE title = :text";
			if (!empty($not_id) && $not_id !== 'x') {
				$query .= " AND category = :not_id";
				$params['not_id'] = (int)$not_id;
			}
			break;
		case 'subcat_seftitle':
			$query = "SELECT count(DISTINCT id) as total FROM " . _PRE . "categories WHERE seftitle = :text AND subcat = :subcat";
			$params['subcat'] = (int)$subcat;
			break;
		case 'subcat_name':
			$query = "SELECT count(DISTINCT id) as total FROM " . _PRE . "categories WHERE name = :text AND subcat = :subcat";
			$params['subcat'] = (int)$subcat;
			break;
		case 'cat_seftitle_edit':
			$query = "SELECT count(DISTINCT id) as total FROM " . _PRE . "categories WHERE seftitle = :text AND id != :not_id";
			$params['not_id'] = (int)$not_id;
			break;
		case 'cat_name_edit':
			$query = "SELECT count(DISTINCT id) as total FROM " . _PRE . "categories WHERE name = :text AND id != :not_id";
			$params['not_id'] = (int)$not_id;
			break;
		case 'subcat_seftitle_edit':
			$query = "SELECT count(DISTINCT id) as total FROM " . _PRE . "categories WHERE seftitle = :text AND subcat = :subcat AND id != :not_id";
			$params['subcat'] = (int)$subcat;
			$params['not_id'] = (int)$not_id;
			break;
		case 'subcat_name_edit':
			$query = "SELECT count(DISTINCT id) as total FROM " . _PRE . "categories WHERE name = :text AND subcat = :subcat AND id != :not_id";
			$params['subcat'] = (int)$subcat;
			$params['not_id'] = (int)$not_id;
			break;
		case 'group_seftitle':
			$query = "SELECT count(DISTINCT id) as total FROM " . _PRE . "extras WHERE seftitle = :text";
			if (!empty($not_id) && $not_id !== 'x') {
				$query .= " AND id != :not_id";
				$params['not_id'] = (int)$not_id;
			}
			break;
		case 'group_name':
			$query = "SELECT count(DISTINCT id) as total FROM " . _PRE . "extras WHERE name = :text";
			if (!empty($not_id) && $not_id !== 'x') {
				$query .= " AND id != :not_id";
				$params['not_id'] = (int)$not_id;
			}
			break;
		default:
			return false;
	}

	$rows = 0;
	try {
		$stmt = $database->prepare($query);
		$stmt->execute($params);
		if ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$rows = (int)$r['total'];
		}
	} catch (PDOException $e) {
		return false;
	}
	
	return ($rows > 0);
}


// HIDE/SHOW (PHP 8.1 safe: prepared queries, undefined index validation & modern routing headers)
function visibility(string $mode): void {
	$database = db();
	if (!$database) {
		return;
	}

	$get_id = isset($_GET['id']) ? (string)$_GET['id'] : '0';
	$id = (int)cleanXSS($get_id);
	
	$get_item = isset($_GET['item']) ? (string)$_GET['item'] : '';
	$item = cleanXSS($get_item);
	
	$back = isset($_GET['back']) ? (string)$_GET['back'] : '';
	$no_yes = ($mode === 'hide') ? 'NO' : 'YES';
	
	// Initialiseer variabelen vooraf om PHP 8.1 Undefined Variable warnings te voorkomen
	$order = 'artorder';
	$link = !empty($back) ? $back : 'snews_articles';

	switch ($item) {
		case 'snews_articles':
			$order = 'artorder';
			$link = empty($back) ? 'snews_articles' : $back;
			break;
		case 'extra_contents':
			$order = 'artorder';
			$link = empty($back) ? 'extra_contents' : $back;
			break;
		case 'snews_pages':
			$order = 'artorder';
			$link = empty($back) ? 'snews_pages' : $back;
			break;
		default:
			// Fallback als er een onbekend item wordt meegegeven via de GET request
			$link = !empty($back) ? $back : '';
			break;
	}
	
	$query = "UPDATE " . _PRE . "articles SET visible = :visible WHERE id = :id";
	try {
		$stmt = $database->prepare($query);
		$stmt->execute([
			':visible' => $no_yes,
			':id'      => $id
		]);
	} catch (PDOException $e) {
		// Foutopvang tijdens database runtime
	}
	
	if (function_exists('notification')) {
		echo notification(0, l('please_wait'));
	}
	
	$link_destination = $link !== 'home' ? $link : '';
	$redirect_url = _SITE . $link_destination . '/';
	
	// Moderne PHP header-omleiding in plaats van uitsluitend meta-refresh
	if (!headers_sent()) {
		header('Refresh: 1; url=' . $redirect_url);
	}
	echo '<meta http-equiv="refresh" content="1; url=' . htmlspecialchars($redirect_url, ENT_QUOTES, 'UTF-8') . '">';
}

// *** PROCESSING (CATEGORIES, CONTENTS, COMMENTS) *** (PHP 8.1 safe: each() verwijderd, strict array verification)
function processing(): void {
	if (!defined('_ADMIN') || !_ADMIN) {
		if (function_exists('notification')) {
			echo notification(1, l('error_not_logged_in'), 'home');
		}
		return;
	}

	$database = db();
	if (!$database) {
		return;
	}

	$get_action = isset($_GET['action']) ? (string)$_GET['action'] : '';
	$action = cleanXSS($get_action);
	
	$get_id = isset($_GET['id']) ? (string)$_GET['id'] : '0';
	$id = (int)cleanXSS($get_id, true);
	
	$post_commentid = isset($_POST['commentid']) ? (string)$_POST['commentid'] : '0';
	$commentid = (int)cleanXSS($post_commentid, true);
	
	$approved = (isset($_POST['approved']) && $_POST['approved'] === 'on') ? 'True' : '';
	$name = isset($_POST['name']) ? clean(entity($_POST['name'])) : '';
	
	$post_def_cat = isset($_POST['define_category']) ? (string)$_POST['define_category'] : '0';
	$category = !empty($_POST['define_category']) ? (int)cleanXSS($post_def_cat, true) : 0;
	
	$post_subcat = isset($_POST['subcat']) ? (string)$_POST['subcat'] : '0';
	$subcat = isset($_POST['subcat']) ? (int)cleanXSS($post_subcat, true) : 0;
	
	$page = isset($_POST['define_page']) ? (string)$_POST['define_page'] : '';
	$def_extra = isset($_POST['define_extra']) ? (string)$_POST['define_extra'] : '';
	$description = isset($_POST['description']) ? clean(entity($_POST['description'])) : '';
	$title = isset($_POST['title']) ? clean(entity($_POST['title'])) : '';
	$seftitle = isset($_POST['seftitle']) ? clean((string)$_POST['seftitle']) : '';
	$url = isset($_POST['url']) ? cleanXSS((string)$_POST['url']) : '';
	$comment = isset($_POST['editedcomment']) ? cleanComment((string)$_POST['editedcomment']) : '';
	
	if (function_exists('clean_mysql')) {
		$text = isset($_POST['text']) ? clean_mysql((string)$_POST['text']) : '';
	} else {
		$text = isset($_POST['text']) ? urldecode((string)$_POST['text']) : '';
	}
	
	$date = date('Y-m-d H:i:s');
	$description_meta = isset($_POST['description_meta']) ? entity($_POST['description_meta']) : '';
	$keywords_meta = isset($_POST['keywords_meta']) ? entity($_POST['keywords_meta']) : '';
	
	$display_title = (isset($_POST['display_title']) && $_POST['display_title'] === 'on') ? 'YES' : 'NO';
	$display_info = (isset($_POST['display_info']) && $_POST['display_info'] === 'on') ? 'YES' : 'NO';
	$commentable = (isset($_POST['commentable']) && $_POST['commentable'] === 'on') ? 'YES' : 'NO';
	$freez = (isset($_POST['freeze']) && $_POST['freeze'] === 'on') ? 'YES' : 'NO';
	
	if ($freez === 'YES' && $commentable === 'YES') {
		$commentable = 'FREEZ';
	}
	
	$position = (isset($_POST['position']) && (int)$_POST['position'] > 0) ? (int)$_POST['position'] : 1;
	$publish_article = (isset($_POST['publish_article']) && $_POST['publish_article'] === 'on') ? 1 : 0;
	$show_in_subcats = (isset($_POST['show_in_subcats']) && $_POST['show_in_subcats'] === 'on') ? 'YES' : 'NO';
	$show_on_home = ((isset($_POST['show_on_home']) && $_POST['show_on_home'] === 'on') || $position > 1) ? 'YES' : 'NO';
	$publish_category = (isset($_POST['publish']) && $_POST['publish'] === 'on') ? 'YES' : 'NO';
	
	$fpost_enabled = (isset($_POST['fposting']) && $_POST['fposting'] === 'on');
	if ($fpost_enabled) {
		$f_year   = (int)($_POST['fposting_year'] ?? date('Y'));
		$f_month  = (int)($_POST['fposting_month'] ?? date('m'));
		$f_day    = (int)($_POST['fposting_day'] ?? date('d'));
		$f_hour   = (int)($_POST['fposting_hour'] ?? date('H'));
		$f_minute = (int)($_POST['fposting_minute'] ?? date('i'));
		
		// Veilig formattering dwingen met voorloopnullen om database parsingfouten te tackelen
		$date = sprintf('%04d-%02d-%02d %02d:%02d:00', $f_year, $f_month, $f_day, $f_hour, $f_minute);
		if (date('Y-m-d H:i:s') < $date) {
			$publish_article = 2;
		}
	}
	
	$task = isset($_POST['task']) ? cleanXSS((string)$_POST['task']) : '';
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
		switch ($task) {
			case 'save_settings':
				if (isset($_POST['save'])) {
					$website_title = (string)($_POST['website_title'] ?? '');
					$home_sef = (string)($_POST['home_sef'] ?? '');
					$website_description = (string)($_POST['website_description'] ?? '');
					$website_keywords = (string)($_POST['website_keywords'] ?? '');
					
					$email_input = (string)($_POST['website_email'] ?? '');
					$website_email = filter_var($email_input, FILTER_VALIDATE_EMAIL) !== false ? $email_input : '';
					
					$contact_subject = (string)($_POST['contact_subject'] ?? '');
					$language = (string)($_POST['language'] ?? 'EN');
					$charset = (string)($_POST['charset'] ?? 'UTF-8');
					$date_format = (string)($_POST['date_format'] ?? 'd.m.Y. H:i');
					$article_limit = (string)($_POST['article_limit'] ?? '3');
					$rss_limit = (string)($_POST['rss_limit'] ?? '5');
					$display_page = (string)($_POST['display_page'] ?? '0');
					
					$display_new_on_home = (string)($_POST['display_new_on_home'] ?? '');
					$display_pagination = (string)($_POST['display_pagination'] ?? '');
					$num_categories = (string)($_POST['num_categories'] ?? '');
					$show_cat_names = !empty($_POST['show_cat_names']) ? (string)$_POST['show_cat_names'] : '';
					$approve_comments = (string)($_POST['approve_comments'] ?? '');
					$mail_on_comments = (string)($_POST['mail_on_comments'] ?? '');
					$comments_order = (string)($_POST['comments_order'] ?? 'ASC');
					$comment_limit = (string)($_POST['comment_limit'] ?? '30');
					
					$word_filter_enable = (string)($_POST['word_filter_enable'] ?? '');
					$word_filter_file = (string)($_POST['word_filter_file'] ?? '');
					$word_filter_change = (string)($_POST['word_filter_change'] ?? '');
					
					$enable_extras = (isset($_POST['enable_extras']) && $_POST['enable_extras'] === 'on') ? 'YES' : 'NO';
					$enable_comments = (isset($_POST['enable_comments']) && $_POST['enable_comments'] === 'on') ? 'YES' : 'NO';
					
					$comment_repost_timer = (isset($_POST['comment_repost_timer']) && is_numeric($_POST['comment_repost_timer'])) ? (int)$_POST['comment_repost_timer'] : 15;
					$freeze_comments = (isset($_POST['freeze_comments']) && $_POST['freeze_comments'] === 'on') ? 'YES' : 'NO';
					
					$file_ext = (string)($_POST['file_ext'] ?? '');
					$allowed_file = (string)($_POST['allowed_file'] ?? '');
					$allowed_img = (string)($_POST['allowed_img'] ?? '');
					
					$ufield = array(
						'website_title'        => $website_title,
						'home_sef'             => $home_sef,
						'website_description'  => $website_description,
						'website_keywords'     => $website_keywords,
						'website_email'        => $website_email,
						'contact_subject'      => $contact_subject,
						'language'             => $language,
						'charset'              => $charset,
						'date_format'          => $date_format,
						'article_limit'        => $article_limit,
						'rss_limit'            => $rss_limit,
						'display_page'         => $display_page,
						'comments_order'       => $comments_order,
						'comment_limit'        => $comment_limit,
						'word_filter_file'     => $word_filter_file,
						'word_filter_change'   => $word_filter_change,
						'display_new_on_home'  => $display_new_on_home,
						'display_pagination'   => $display_pagination,
						'num_categories'       => $num_categories,
						'show_cat_names'       => $show_cat_names,
						'approve_comments'     => $approve_comments,
						'mail_on_comments'     => $mail_on_comments,
						'word_filter_enable'   => $word_filter_enable,
						'enable_extras'        => $enable_extras,
						'enable_comments'      => $enable_comments,
						'freeze_comments'      => $freeze_comments,
						'comment_repost_timer' => (string)$comment_repost_timer,
						'file_extensions'      => $file_ext,
						'allowed_files'        => $allowed_file,
						'allowed_images'       => $allowed_img
					);
					
					// FIX: De brekende list() = each() lus vervangen door een up-to-date foreach-constructie
					$sql = "UPDATE " . _PRE . "settings SET value = :val WHERE name = :name";
					try {
						$stmt = $database->prepare($sql);
						foreach ($ufield as $key => $value) {
							$stmt->execute([
								':val'  => $value,
								':name' => $key
							]);
						}
					} catch (PDOException $e) {
						// database error tijdens opslaan settings
					}
					
					if (function_exists('notification')) {
						echo notification(0, '', 'snews_settings');
					}
				}
				break;

				
						case 'changeup':
					if (isset($_POST['submit_pass'])) {
						$user = checkUserPass($_POST['uname'] ?? '');
						$pass1 = checkUserPass($_POST['pass1'] ?? '');
						$pass2 = checkUserPass($_POST['pass2'] ?? '');
						
						if ($user && $pass1 && $pass2 && $pass1 === $pass2) {
							$uname = md5($user);
							$pass = md5($pass2);
							
							# USERNAME UPDATE via zuivere PDO execute
							$q1 = "UPDATE " . _PRE . "settings SET value = :val WHERE name = :name";
							try {
								$stmt1 = $database->prepare($q1);
								$stmt1->execute([
									':val'  => $uname,
									':name' => 'username'
								]);
							} catch (PDOException $e) {}
							
							# PASSWORD UPDATE via zuivere PDO execute
							$q2 = "UPDATE " . _PRE . "settings SET value = :val WHERE name = :name";
							try {
								$stmt2 = $database->prepare($q2);
								$stmt2->execute([
									':val'  => $pass,
									':name' => 'password'
								]);
							} catch (PDOException $e) {}
							
							echo notification(0, '', 'administration');
						} else {
							// FIX: Voorkom een harde script-crash via die(), gebruik return
							echo notification(2, l('pass_mismatch'), 'snews_settings');
							return;
						}
					}
					break;

				case 'admin_groupings':
					// Strikte typecasting initialisatie voor veilig gebruik in de geneste switch
					$name = (string)($name ?? '');
					$seftitle = (string)($seftitle ?? '');
					$description = (string)($description ?? '');
					$id = (int)($id ?? 0);

					switch (true) {
						case (empty($name)):
							echo notification(1, l('err_TitleEmpty') . l('errNote'));
							form_groupings();
							break;
						case (empty($seftitle)):
							echo notification(1, l('err_SEFEmpty') . l('errNote'));
							form_groupings();
							break;
						case (check_if_unique('group_name', $name, $id, '')):
							echo notification(1, l('err_TitleExists') . l('errNote'));
							form_groupings();
							break;
						case (check_if_unique('group_seftitle', $seftitle, $id, '')):
							echo notification(1, l('err_SEFExists') . l('errNote'));
							form_groupings();
							break;
						case (cleancheckSEF($seftitle) === 'notok'):
							echo notification(1, l('err_SEFIllegal') . l('errNote'));
							form_groupings();
							break;
						default:
							switch (true) {
								case (isset($_POST['add_groupings'])):
									$sql1 = "INSERT INTO " . _PRE . "extras (name, seftitle, description) VALUES (:name, :sef, :desc)";
									try {
										$stmt = $database->prepare($sql1);
										$stmt->execute([
											':name' => $name,
											':sef'  => $seftitle,
											':desc' => $description
										]);
									} catch (PDOException $e) {}
									break;
								case (isset($_POST['edit_groupings'])):
									$sql2 = "UPDATE " . _PRE . "extras SET name = :name, seftitle = :sef, description = :desc WHERE id = :id";
									try {
										$stmt = $database->prepare($sql2);
										$stmt->execute([
											':name' => $name,
											':sef'  => $seftitle,
											':desc' => $description,
											':id'   => $id
										]);
									} catch (PDOException $e) {}
									break;
								case (isset($_POST['delete_groupings'])):
									$sql3 = "DELETE FROM " . _PRE . "extras WHERE id = :id";
									try {
										$stmt = $database->prepare($sql3);
										$stmt->execute([
											':id' => $id
										]);
									} catch (PDOException $e) {}
									break;
							}
							echo notification(0, '', 'groupings');
					}
					break;

				case 'admin_category':
				case 'admin_subcategory':
					$name = (string)($name ?? '');
					$seftitle = (string)($seftitle ?? '');
					$description = (string)($description ?? '');
					$id = (int)($id ?? 0);
					$subcat = (int)($subcat ?? 0);
					$publish_category = (string)($publish_category ?? 'YES');

					switch (true) {
						case (empty($name)):
							echo notification(1, l('err_TitleEmpty') . l('errNote'));
							form_categories();
							break;
						case (empty($seftitle)):
							echo notification(1, l('err_SEFEmpty') . l('errNote'));
							form_categories();
							break;
						case (isset($_POST['add_category']) && check_if_unique('subcat_name', $name, '', $subcat)):
							echo notification(1, l('err_TitleExists') . l('errNote'));
							form_categories();
							break;
						case (isset($_POST['add_category']) && check_if_unique('subcat_seftitle', $seftitle, '', $subcat)):
							echo notification(1, l('err_SEFExists') . l('errNote'));
							form_categories();
							break;
						case (isset($_POST['edit_category']) && $subcat === 0 && check_if_unique('cat_name_edit', $name, $id, '')):
							echo notification(1, l('err_TitleExists') . l('errNote'));
							form_categories();
							break;
						case (isset($_POST['edit_category']) && $subcat === 0 && check_if_unique('cat_seftitle_edit', $seftitle, $id, '')):
							echo notification(1, l('err_SEFExists') . l('errNote'));
							form_categories();
							break;
						case (isset($_POST['edit_category']) && $subcat !== 0 && check_if_unique('subcat_name_edit', $name, $id, $subcat)):
							echo notification(1, l('err_TitleExists') . l('errNote'));
							form_categories();
							break;
						case (isset($_POST['edit_category']) && $subcat !== 0 && check_if_unique('subcat_seftitle_edit', $seftitle, $id, $subcat)):
							echo notification(1, l('err_SEFExists') . l('errNote'));
							form_categories();
							break;
						case (cleancheckSEF($seftitle) === 'notok'):
							echo notification(1, l('err_SEFIllegal') . l('errNote'));
							form_categories();
							break;
						case ($subcat === $id && $id !== 0):
							echo ' ' . $id;
							echo notification(1, l('errNote'));
							form_categories();
							break;
						default:
							switch(true) {
								case(isset($_POST['add_category'])):
									$catorder = stats('categories', '', 'subcat = ' . $subcat, false);
									$catorder = $catorder + 1;
									$query = "INSERT INTO " . _PRE . "categories (name, seftitle, description, published, catorder, subcat) 
										VALUES (:name, :sef, :desc, :published, :catorder, :subcat)";
									try {
										$stmt = $database->prepare($query);
										$stmt->execute([
											':name'       => $name,
											':sef'        => $seftitle,
											':desc'       => $description,
											':published'  => $publish_category,
											':catorder'   => $catorder,
											':subcat'     => $subcat
										]);
									} catch (PDOException $e) {}
									break;

																# EDIT CATEGORY (PHP 8.1 safe execution loop)
								case (isset($_POST['edit_category'])):
									$catorder = stats('categories', '', 'subcat = ' . (int)$subcat, false);
									$catorder = isset($_POST['catorder']) ? (int)$_POST['catorder'] : $catorder + 1;
									
									$query = "UPDATE " . _PRE . "categories
										SET name = :name, seftitle = :sef, description = :desc, published = :pub, subcat = :subcat, catorder = :cat_order
										WHERE id = :id";
									try {
										$stmt = $database->prepare($query);
										$stmt->execute([
											':name'      => $name,
											':sef'       => $seftitle,
											':desc'      => $description,
											':pub'       => $publish_category,
											':subcat'    => (int)$subcat,
											':cat_order' => (int)$catorder,
											':id'        => (int)$id
										]);
									} catch (PDOException $e) {}
									break;

								# DELETE CATEGORY
								case (isset($_POST['delete_category'])):
									$any_subcats = stats('categories', '', 'subcat = ' . (int)$id);
									$any_articles = stats('articles', '', 'category = ' . (int)$id);
									if ($any_subcats > 0 || $any_articles > 0) {
										echo notification(1, l('warn_catnotempty'), '');
										echo '<p><a href="' . _SITE . 'administration/" title="' . htmlspecialchars(l('administration'), ENT_QUOTES, 'UTF-8') . '">' . l('administration') . '</a>  OR  ';
										echo '<a href="' . _SITE . '?action=process&amp;task=delete_category_all&amp;id=' . (int)$id . '" ';
										echo 'onclick="return pop(\'' . htmlspecialchars(l('js_delete1'), ENT_QUOTES, 'UTF-8') . '\')" title="' . htmlspecialchars(l('administration'), ENT_QUOTES, 'UTF-8') . '">';
										echo htmlspecialchars(l('empty_cat'), ENT_QUOTES, 'UTF-8') . '</a></p>';
										$no_success = true;
									} else {
										if (function_exists('delete_cat')) {
											delete_cat($id);
										}
									}
									break;
							}
						$success = isset($no_success) ? '' : notification(0, '', 'snews_categories');
						echo $success;
					}
					break;

				case 'reorder':
					if (isset($_POST['reorder']) && isset($_POST['order'])) {
						$table = 'articles';
						$order_type = 'artorder';
						$remove = 'page_';
						
						switch ($_POST['order']){
							case 'snews_articles':
							case 'extra_contents':
							case 'snews_pages':
								$table = 'articles';
								$order_type = 'artorder';
								$remove = 'page_';
								break;
							case 'snews_categories':
								$table = 'categories';
								$order_type = 'catorder';
								$remove = 'cat_';
								break;
						}
						
						// FIX: Whitelist de database kolommen en tabelnamen tegen SQL injectie
						$table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
						$order_type = preg_replace('/[^a-zA-Z0-9_]/', '', $order_type);
						
						$query = "UPDATE " . _PRE . $table . " SET $order_type = :type WHERE id = :id";
						try {
							$stmt = $database->prepare($query);
							foreach ($_POST as $key => $value) {
								$type_id = (int)str_replace($remove, '', $key);
								$clean_val = (int)trim((string)$value);
								
								if ($key !== 'reorder' && $key !== 'order' && $key !== $table && $key !== l('order_content') && $key !== $_POST['order']) {
									$stmt->execute([
										':type' => $clean_val,
										':id'   => $type_id
									]);
								}
							}
						} catch (PDOException $e) {}
						
						if (function_exists('notification')) {
							echo notification(0, l('please_wait'));
						}
						
						$redirect_url = _SITE . (string)$_POST['order'] . '/';
						if (!headers_sent()) {
							header('Refresh: 1; url=' . $redirect_url);
						}
						echo '<meta http-equiv="refresh" content="1; url=' . htmlspecialchars($redirect_url, ENT_QUOTES, 'UTF-8') . '">';
					}
					break;

				case 'admin_article':
					$_SESSION[_SITE . 'temp']['title'] = $_POST['title'] ?? '';
					$_SESSION[_SITE . 'temp']['seftitle'] = $_POST['seftitle'] ?? '';
					$_SESSION[_SITE . 'temp']['text'] = $_POST['text'] ?? '';
					
					$position = (int)($position ?? 1);
					$from = '';
					$link = 'snews_articles';
					
					switch ($position) {
						case 1: $from = $id !== 0 ? '' : 'article_new'; $link = 'snews_articles'; break;
						case 2: $from = $id !== 0 ? '' : 'extra_new';   $link = 'extra_contents'; break;
						case 3: $from = $id !== 0 ? '' : 'page_new';    $link = 'snews_pages'; break;
					}
					
					$title = (string)($title ?? '');
					$seftitle = (string)($seftitle ?? '');
					$category = (int)($category ?? 0);
					
					switch (true) {
						case (empty($title)):
							echo notification(1, l('err_TitleEmpty') . l('errNote'), $link);
							form_articles($from);
							unset($_SESSION[_SITE . 'temp']);
							break;
						case (empty($seftitle)):
							echo notification(1, l('err_SEFEmpty') . l('errNote'));
							$_SESSION[_SITE . 'temp']['seftitle'] = $_SESSION[_SITE . 'temp']['title'];
							form_articles($from);
							unset($_SESSION[_SITE . 'temp']);
							break;
						case (cleancheckSEF($seftitle) === 'notok'):
							echo notification(1, l('err_SEFIllegal') . l('errNote'));
							form_articles($from);
							unset($_SESSION[_SITE . 'temp']);
							break;
						case ($position === 1 && isset($_POST['article_category']) && (int)$_POST['article_category'] !== $category && isset($_POST['edit_article'])
								&& check_if_unique('article_title', $title, $category, '')):
							echo notification(1, l('err_TitleExists') . l('errNote'));
							form_articles($from);
							unset($_SESSION[_SITE . 'temp']);
							break;
						case ($position === 1 && isset($_POST['article_category']) && (int)$_POST['article_category'] !== $category && isset($_POST['edit_article'])
								&& check_if_unique('article_seftitle', $seftitle, $category, '')):
							echo notification(1, l('err_SEFExists') . l('errNote'));
							form_articles($from);
							unset($_SESSION[_SITE . 'temp']);
							break;
						case (!isset($_POST['delete_article']) && !isset($_POST['edit_article'])
								&& check_if_unique('article_title', $title, $category, '')):
							echo notification(1, l('err_TitleExists') . l('errNote'));
							form_articles($from);
							unset($_SESSION[_SITE . 'temp']);
							break;
						case (!isset($_POST['delete_article']) && !isset($_POST['edit_article'])
								&& check_if_unique('article_seftitle', $seftitle, $category, '')):
							echo notification(1, l('err_SEFExists') . l('errNote'));
							form_articles($from);
							unset($_SESSION[_SITE . 'temp']);
							break;
						default:
							$artorder = (isset($_POST['artorder']) && (int)$_POST['artorder'] > 0) ?
								(int)$_POST['artorder'] :
								stats('articles', '', ' category = ' . $category . ' AND position = ' . $position) + 1;
								
							switch (true) {
								case (isset($_POST['add_article'])):
									$query = "INSERT INTO " . _PRE . "articles (
										title, seftitle, text, date, category,
										position, extraid, page_extra, displaytitle,
										displayinfo, commentable, published, description_meta,
										keywords_meta, show_on_home, show_in_subcats, artorder)
									VALUES (:title, :sef, :text, :date, :cat,
										:pos, :extraid, :page_extra, :disp_title,
										:disp_info, :comment, :pub, :dmeta,
										:keywords, :shome, :ssub, :order)";
									try {
										$stmt = $database->prepare($query);
										$stmt->execute([
											':title'       => $title,
											':sef'         => $seftitle,
											':text'        => $text ?? '',
											':date'        => $date,
											':cat'         => $category,
											':pos'         => $position,
											':extraid'     => $def_extra ?? '',
											':page_extra'  => $page ?? '',
											':disp_title'  => $display_title ?? 'YES',
											':disp_info'   => $display_info ?? 'YES',
											':comment'     => $commentable ?? 'YES',
											':pub'         => (int)($publish_article ?? 1),
											':dmeta'       => $description_meta ?? '',
											':keywords'    => $keywords_meta ?? '',
											':shome'       => $show_on_home ?? 'YES',
											':ssub'        => $show_in_subcats ?? 'NO',
											':order'       => (int)$artorder
										]);
									} catch (PDOException $e) {}
									break;

																# EDIT ARTICLE (PHP 8.1 safe database processing)
								case (isset($_POST['edit_article'])):
									$database = db();
									if (!$database) {
										return;
									}

									// Forceer strikte typering op de positie- en ID-variabelen
									$position = (int)($position ?? 1);
									$id = (int)($id ?? 0);
									$category = ($position === 3) ? 0 : (int)($category ?? 0);
									
									$old_pos_res = retrieve('position', 'articles', 'id', $id);
									$old_pos = (int)(is_array($old_pos_res) ? ($old_pos_res['position'] ?? 1) : $old_pos_res);
									
									// Alleen uitvoeren als een pagina wordt gewijzigd naar een artikel of extra content blok
									if ($position !== $old_pos && $old_pos === 3) {
										// FIX: Gecorrigeerd naar Prepared Statement om de rauwe $id variabele te elimineren
										$chk_extra_query = "SELECT id FROM " . _PRE . "articles WHERE position = 2 AND category = -3 AND page_extra = :page_id";
										try {
											$stmt_chk = $database->prepare($chk_extra_query);
											$stmt_chk->execute(['page_id' => $id]);
											
											$sql_update_extra = "UPDATE " . _PRE . "articles SET category = :cat, page_extra = :pg_extra WHERE id = :xtra_id";
											$stmt_up_extra = $database->prepare($sql_update_extra);
											
											while ($xtra = $stmt_chk->fetch(PDO::FETCH_ASSOC)) {
												$xtra_id = (int)$xtra['id'];
												// FIX: Uitgevoerd via execute() zonder loze, brekende dbfetch() call
												$stmt_up_extra->execute([
													':cat'      => '-1',
													':pg_extra' => '',
													':xtra_id'  => $xtra_id
												]);
											}
										} catch (PDOException $e) {}
									}
									
									# CHANGE TO -> ARTICLE / PAGE
									if ($position === 1 || $position === 3) {
										$def_extra = '';
										$page = '';
									}
									# CHANGE TO -> EXTRA
									if ($position === 2) {
										$def_extra = empty($def_extra) ? '1' : (string)$def_extra;
										$page = empty($page) ? '0' : (string)$page;
									}
									
									// Bereid de parameters voor de hoofd-update query voor
									$params_art = [
										':title'       => (string)($title ?? ''),
										':sef'         => (string)($seftitle ?? ''),
										':text'        => (string)($text ?? ''),
										':cat'         => $category,
										':pos'         => $position,
										':extraid'     => (string)$def_extra,
										':page_extra'  => (string)$page,
										':disp_title'  => (string)($display_title ?? 'YES'),
										':disp_info'   => (string)($display_info ?? 'YES'),
										':comment'     => (string)($commentable ?? 'YES'),
										':pub'         => (int)($publish_article ?? 1),
										':dmeta'       => (string)($description_meta ?? ''),
										':keyw'        => (string)($keywords_meta ?? ''),
										':shome'       => (string)($show_on_home ?? 'YES'),
										':ssubcat'     => (string)($show_in_subcats ?? 'NO'),
										':order'       => (int)($artorder ?? 0),
										':id'          => $id
									];

									// FIX: In plaats van rauwe SQL string concatenatie ($future), houden we de query statisch
									if (isset($fpost_enabled) && $fpost_enabled) {
										$publish_article = strtotime((string)$date) < time() ? 1 : (int)($publish_article ?? 1);
										$params_art[':pub'] = $publish_article;
										$params_art[':date_val'] = (string)$date;
										
										$art_qwr = "UPDATE " . _PRE . "articles SET
											title = :title, seftitle = :sef, text = :text, date = :date_val, category = :cat, position = :pos,
											extraid = :extraid, page_extra = :page_extra, displaytitle = :disp_title, displayinfo = :disp_info,
											commentable = :comment, published = :pub, description_meta = :dmeta, keywords_meta = :keyw, show_on_home = :shome,
											show_in_subcats = :ssubcat, artorder = :order
											WHERE id = :id";
									} else {
										$art_qwr = "UPDATE " . _PRE . "articles SET
											title = :title, seftitle = :sef, text = :text, category = :cat, position = :pos,
											extraid = :extraid, page_extra = :page_extra, displaytitle = :disp_title, displayinfo = :disp_info,
											commentable = :comment, published = :pub, description_meta = :dmeta, keywords_meta = :keyw, show_on_home = :shome,
											show_in_subcats = :ssubcat, artorder = :order
											WHERE id = :id";
									}
									
									try {
										$res_art = $database->prepare($art_qwr);
										// FIX: Uitgevoerd via execute() zonder loze, brekende dbfetch() call
										$res_art->execute($params_art);
									} catch (PDOException $e) {}
									break;

																# DELETE ARTICLE (PHP 8.1 safe prepared statements)
								case (isset($_POST['delete_article'])):
									$position = (int)($position ?? 1);
									$id = (int)($id ?? 0);
									
									if ($position === 3) {
										// FIX: Prepared statement om de rauwe $id variabele te elimineren
										$chk_extra_query = "SELECT id FROM " . _PRE . "articles WHERE position = 2 AND category = -3 AND page_extra = :page_id";
										try {
											$stmt_chk = $database->prepare($chk_extra_query);
											$stmt_chk->execute(['page_id' => $id]);
											
											$extra2 = "UPDATE " . _PRE . "articles SET category = :cat, page_extra = :extra_id WHERE id = :id";
											$stmt_up_xtra = $database->prepare($extra2);
											
											while ($xtra = $stmt_chk->fetch(PDO::FETCH_ASSOC)) {
												$xtra_id = (int)$xtra['id'];
												// FIX: Uitgevoerd via execute() zonder loze, brekende dbfetch() call
												$stmt_up_xtra->execute([
													':cat'      => '0',
													':extra_id' => '',
													':id'       => $xtra_id
												]);
											}
										} catch (PDOException $e) {}
									}
									
									if (function_exists('delete_item')) {
										delete_item('articles', 'id', $id);
										delete_item('comments', 'articleid', $id);
									}
									
									if ($id === (int)s('display_page')) {
										// FIX: SQL-syntax gecorrigeerd van VALUE naar value
										$qwr1 = "UPDATE " . _PRE . "settings SET value = :val WHERE name = :name";
										try {
											$stmt_sett = $database->prepare($qwr1);
											$stmt_sett->execute([
												':val'  => '0',
												':name' => 'display_page'
											]);
										} catch (PDOException $e) {}
									}
									break;
							}
						
						$link = (string)($link ?? 'snews_articles');
						echo notification(0, '', $link);
						unset($_SESSION[_SITE . 'temp']);
					}
					break;

				case 'editcomment':
					$commentid = (int)($commentid ?? 0);
					$name = (string)($name ?? '');
					$url = (string)($url ?? '');
					$comment = (string)($comment ?? '');
					$approved = (string)($approved ?? '');

					// FIX: Veilig controleren van de database resultaten via retrieve() om PHP 8.1 type errors te voorkomen
					$articleID_res = retrieve('articleid', 'comments', 'id', $commentid);
					$articleID = (int)(is_array($articleID_res) ? ($articleID_res['articleid'] ?? 0) : $articleID_res);
					
					$articleSEF_res = retrieve('seftitle', 'articles', 'id', $articleID);
					$articleSEF = (string)(is_array($articleSEF_res) ? ($articleSEF_res['seftitle'] ?? '') : $articleSEF_res);
					
					$articleCAT_res = retrieve('category', 'articles', 'seftitle', $articleSEF);
					$articleCAT = (int)(is_array($articleCAT_res) ? ($articleCAT_res['category'] ?? 0) : $articleCAT_res);
					
					$postCat = function_exists('cat_rel') ? cat_rel($articleCAT, 'seftitle') : '';
					$link = (!empty($postCat) ? $postCat . '/' : '') . $articleSEF;
					
					if (isset($_POST['submit_text'])) {
						$q2 = "UPDATE " . _PRE . "comments SET name = :name, url = :url, comment = :comment, approved = :approved WHERE id = :id";
						try {
							$stmt_comm = $database->prepare($q2);
							// FIX: Uitgevoerd via execute() zonder loze, brekende dbfetch() call
							$stmt_comm->execute([
								':name'     => $name,
								':url'      => $url,
								':comment'  => $comment,
								':approved' => $approved,
								':id'       => $commentid
							]);
						} catch (PDOException $e) {}
					} elseif (isset($_POST['delete_text'])) {
						if (function_exists('delete_item')) {
							delete_item('comments', 'id', $commentid);
						}
					}
					echo notification(0, '', $link);
					break;
			}
		} elseif (!empty($_GET) && isset($action) && $action === 'process') {
			$get_task = isset($_GET['task']) ? (string)$_GET['task'] : '';
			$task = cleanXSS($get_task);
			switch ($task) {

								# DELETE CATEGORY AND ALL ARTICLES (PHP 8.1 safe: prepared queries & array initialization)
				case 'delete_category_all':
					$get_id = isset($_GET['id']) ? (string)$_GET['id'] : '0';
					$id = (int)cleanXSS($get_id);
					if ($id === 0) {
						return;
					}
					
					$list = []; // FIX: Voorkom undefined variable waarschuwingen
					
					// FIX: Herschreven naar Prepared Statement om SQL injection te elimineren
					$query = "SELECT c.id AS subcat, a.id
						FROM " . _PRE . "categories AS c
						LEFT JOIN " . _PRE . "articles AS a ON (a.category = c.id OR a.category = :id) AND a.position = 1
						WHERE c.subcat = :id_parent
						GROUP BY a.id";
						
					try {
						$stmt = $database->prepare($query);
						$stmt->execute(['id' => $id, 'id_parent' => $id]);
						$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
						
						foreach ($rows as $r) {
							if (isset($r['id']) && (int)$r['id'] > 0) {
								if (function_exists('delete_item')) {
									delete_item('comments', 'articleid', (int)$r['id']);
									delete_item('articles', 'id', (int)$r['id']);
								}
							}
							if (isset($r['subcat'])) {
								$list[] = $r['subcat'];
							}
						}
						
						if (function_exists('delete_item')) {
							delete_item('categories', 'subcat', $id);
						}
						if (function_exists('delete_cat')) {
							delete_cat($id);
						}
					} catch (PDOException $e) {}
					
					if (function_exists('notification')) {
						echo notification(0, '', 'snews_categories');
					}
					break;

				# DELETE COMMENT (PHP 8.1 safe type forcing & strict retrieve controls)
				case 'deletecomment':
					$get_commentid = isset($_GET['commentid']) ? (string)$_GET['commentid'] : '0';
					$commentid = (int)cleanXSS($get_commentid);
					if ($commentid === 0) {
						return;
					}
					
					// FIX: Strikte typeafhandeling en controle op databaseresultaten
					$articleid_res = retrieve('articleid', 'comments', 'id', $commentid);
					$articleid = (int)(is_array($articleid_res) ? ($articleid_res['articleid'] ?? 0) : $articleid_res);
					
					$articleSEF_res = retrieve('seftitle', 'articles', 'id', $articleid);
					$articleSEF = (string)(is_array($articleSEF_res) ? ($articleSEF_res['seftitle'] ?? '') : $articleSEF_res);
					
					$articleCAT_res = retrieve('category', 'articles', 'id', $articleid);
					$articleCAT = (int)(is_array($articleCAT_res) ? ($articleCAT_res['category'] ?? 0) : $articleCAT_res);
					
					$postCat = function_exists('cat_rel') ? cat_rel($articleCAT, 'seftitle') : '';
					$link = (!empty($postCat) ? $postCat . '/' : '') . $articleSEF;
					
					if (function_exists('delete_item')) {
						delete_item('comments', 'id', $commentid);
					}
					
					if (function_exists('notification')) {
						echo notification(0, '', $link);
					}
					
					$redirect_url = _SITE . (!empty($postCat) ? $postCat . '/' : '') . $articleSEF . '/';
					if (!headers_sent()) {
						header('Refresh: 1; url=' . $redirect_url);
					}
					echo '<meta http-equiv="refresh" content="1; url=' . htmlspecialchars($redirect_url, ENT_QUOTES, 'UTF-8') . '">';
					break;
			}
		}
	}


// ADMIN CENTER (PHP 8.1 safe strong typed strict scaling switcher)
function admin_center(mixed $action): void {
	$action_str = trim((string)$action);
	switch ($action_str) {
		case 'snews_categories' : if (function_exists('admin_categories')) { admin_categories(); } break;
		case 'admin_category'   : if (function_exists('form_categories')) { form_categories(); } break;
		case 'admin_subcategory': if (function_exists('form_categories')) { form_categories('sub'); } break;
		case 'groupings'        : if (function_exists('admin_groupings')) { admin_groupings(); } break;
		case 'admin_groupings'  : if (function_exists('form_groupings')) { form_groupings(); } break;
		case 'snews_articles'   : if (function_exists('admin_articles')) { admin_articles('article_view'); } break;
		case 'extra_contents'   : if (function_exists('admin_articles')) { admin_articles('extra_view'); } break;
		case 'snews_pages'      : if (function_exists('admin_articles')) { admin_articles('page_view'); } break;
		case 'admin_article'    : if (function_exists('form_articles')) { form_articles(''); } break;
		case 'article_new'      : if (function_exists('form_articles')) { form_articles('article_new'); } break;
		case 'extra_new'        : if (function_exists('form_articles')) { form_articles('extra_new'); } break;
		case 'page_new'         : if (function_exists('form_articles')) { form_articles('page_new'); } break;
		case 'editcomment'      : if (function_exists('edit_comment')) { edit_comment(); } break;
		case 'process'          : if (function_exists('processing')) { processing(); } break;
		case 'hide'             : if (function_exists('visibility')) { visibility('hide'); } break;
		case 'show'             : if (function_exists('visibility')) { visibility('show'); } break;
	}
}

// LOGOUT (Gemoderniseerd naar de geldende standaarden van PHP 8.1+)
function logout(): void {
	// Wis de actieve sessie-array veilig
	$_SESSION = [];
	
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}
	
	if (session_status() === PHP_SESSION_ACTIVE) {
		session_destroy();
	}
	
	$redirect_url = _SITE;
	if (!headers_sent()) {
		header('Refresh: 2; url=' . $redirect_url);
	}
	echo '<meta http-equiv="refresh" content="2; url=' . htmlspecialchars($redirect_url, ENT_QUOTES, 'UTF-8') . '">';
	echo '<h2>' . htmlspecialchars(l('log_out'), ENT_QUOTES, 'UTF-8') . '</h2>';
}

// ROUTES INITIALIZATION
if (function_exists('addRoute')) {
	addRoute('administration', l('administration'), 'administration');
	addRoute('logout', l('logout'), 'logout');
	addRoute('snews_settings', l('settings'), 'settings');
	addRoute('snews_files', l('files'), 'files');
	addRoute('admin_addons', l('admin_addons'), 'showAdmAddons');
}

?>
