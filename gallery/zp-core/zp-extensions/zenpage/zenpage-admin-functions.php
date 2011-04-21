<?php
/**
 * zenpage admin functions
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 * @subpackage zenpage
 */

/**
 * Calls the Zenpage class
 *
 */
require_once("zenpage-class-page.php");
require_once("zenpage-class-news.php");
require_once("zenpage-functions.php");

global $_zp_current_zenpage_news, $_zp_current_zenpage_page;

/**
 * Retrieves posted expiry date and checks it against the current date/time
 * Returns the posted date if it is in the future
 * Returns NULL if the date is past
 *
 * @return string
 */
function getExpiryDatePost() {
	$expiredate = sanitize($_POST['expiredate']);
	if ($expiredate > date(date('Y-m-d H:i:s'))) return $expiredate;
	return NULL;
}

/**
 * processes the taglist save
 *
 * @param object $object the object on which the save happened
 */
function processTags($object) {
	$tagsprefix = 'tags_';
	$tags = array();
	$l = strlen($tagsprefix);
	foreach ($_POST as $key => $value) {
		$key = postIndexDecode($key);
		if (substr($key, 0, $l) == $tagsprefix) {
			if ($value) {
				$tags[] = substr($key, $l);
			}
		}
	}
	$tags = array_unique($tags);
	$object->setTags(sanitize($tags, 3));
}

/**************************
/* page functions
***************************/

/**
 * processes password saves
 * returns error indicating mismatch state
 * @param object $page
 * @return string
 */
function processPasswordSave($page) {
	$notify = $fail = '';
	if (sanitize($_POST['password_enabled'])) {
		$olduser = $page->getUser();
		$newuser = $_POST['page_user'];
		$pwd = trim($_POST['pagepass']);
		if (($olduser != $newuser)) {
			if (!empty($newuser) && empty($pwd) && empty($pwd2)) {
				$fail = 'user';
			}
		}
		if (!$fail && $_POST['pagepass'] == $_POST['pagepass_2']) {
			$page->setUser($newuser);
			$page->setPasswordHint(process_language_string_save('page_hint', 3));
			if (empty($pwd)) {
				if (empty($_POST['pagepass'])) {
					$page->setPassword(NULL);  // clear the password
				}
			} else {
				$page->setPassword($pwd);
			}
		} else {
			if (empty($fail)) {
				$notify = 'pass';
			} else {
				$notify = $fail;
			}
		}
	}
	return $notify;
}

/**
 * Updates a new page to that database and returns the object of that page
 *
 * @return object
 */
function addPage(&$reports) {
	$date = date('Y-m-d_H-i-s');
	$title = process_language_string_save("title",2);
	$titlelink = seoFriendly(get_language_string($title));
	if (empty($titlelink)) $titlelink = seoFriendly($date);

	$author = sanitize($_POST['author']);
	$content = process_language_string_save("content",0); // TinyMCE already clears unallowed code
	$extracontent = process_language_string_save("extracontent",0); // TinyMCE already clears unallowed code
	$show = getcheckboxState('show');
	$date = sanitize($_POST['date']);
	$expiredate = getExpiryDatePost();
	$commentson = getcheckboxState('commentson');
	$permalink = getcheckboxState('permalink');
	$codeblock1 = sanitize($_POST['codeblock1'], 0);
	$codeblock2 = sanitize($_POST['codeblock2'], 0);
	$codeblock3 = sanitize($_POST['codeblock3'], 0);
	$codeblock = serialize(array("1" => $codeblock1, "2" => $codeblock2, "3" => $codeblock3));
	$locked = getcheckboxState('locked');

	$sql = 'SELECT `id` FROM '.prefix('zenpage_pages').' WHERE `titlelink`="'.zp_escape_string($titlelink).'"';
	$rslt = query_single_row($sql,true);
	if ($rslt) {
		$titlelink .= '_'.seoFriendly($date); // force unique so that data may be saved.
	}
	$page = new ZenpagePage($titlelink);
	$notice = processPasswordSave($page);
	$page->set('title',$title);
	$page->set('content',$content);
	$page->set('extracontent',$extracontent);
	$page->set('show',$show);
	$page->set('parentid',NULL);
	$page->set('codeblock',$codeblock);
	$page->set('author',$author);
	$page->set('date',$date);
	$page->set('commentson',$commentson);
	$page->set('permalink',$permalink);
	$page->set('locked',$locked);
	$page->set('expiredate',$expiredate);
	processTags($page);
	$msg = zp_apply_filter('new_page', '', $page);
	$page->save();
	if(empty($title)) {
		$reports[] =  "<p class='errorbox' id='fade-message'>".sprintf(gettext("Page <em>%s</em> added but you need to give it a <strong>title</strong> before publishing!"),get_language_string($titlelink)).'</p>';
	} else if ($notice == 'user') {
		$reports[] =  "<p class='errorbox' id='fade-message'>".gettext('You must supply a password for the Protected Page user').'</p>';
	} else if ($notice == 'pass') {
		$reports[] =  "<p class='errorbox' id='fade-message'>".gettext('Your passwords were empty or did not match').'</p>';
	} else {
		$reports[] =  "<p class='messagebox' id='fade-message'>".sprintf(gettext("Page <em>%s</em> added"),$titlelink).'</p>';
	}
	$reports[] =  $msg;
	return $page;
}



/**
 * Updates a page and returns the object of that page
 *
 * @return object
 */
function updatePage(&$reports) {
	$title = process_language_string_save("title",2);
	$author = sanitize($_POST['author']);
	$content = process_language_string_save("content",0); // TinyMCE already clears unallowed code
	$extracontent = process_language_string_save("extracontent",0); // TinyMCE already clears unallowed code
	$show = getcheckboxState('show');
	$date = sanitize($_POST['date']);
	$lastchange = sanitize($_POST['lastchange']);
	$lastchangeauthor = sanitize($_POST['lastchangeauthor']);
	$expiredate = getExpiryDatePost();
	$commentson = getcheckboxState('commentson');
	$permalink = getcheckboxState('permalink');
	$codeblock1 = sanitize($_POST['codeblock1'], 0);
	$codeblock2 = sanitize($_POST['codeblock2'], 0);
	$codeblock3 = sanitize($_POST['codeblock3'], 0);
	$codeblock = serialize(array("1" => $codeblock1, "2" => $codeblock2, "3" => $codeblock3));
	$locked = getcheckboxState('locked');

	if (getcheckboxState('edittitlelink')) {
		$titlelink = sanitize($_POST['titlelink'],3);
	} else if($permalink) {
		$titlelink = sanitize($_POST['titlelink-old'],3);
	} else {
		$titlelink = seoFriendly(get_language_string($title));
	}
	if(empty($titlelink)) $titlelink = seoFriendly($result['date']);
	$id = sanitize($_POST['id']);
	$rslt = true;
	$oldtitlelink = sanitize($_POST['titlelink-old']);
	if (getcheckboxState('edittitlelink')) { // title link change must be reflected in DB before any other updates
		$rslt = query('UPDATE '.prefix('zenpage_pages').' SET `titlelink`="'.zp_escape_string($titlelink).'" WHERE `id`="'.$id.'"',true);
		if (!$rslt) {
			$titlelink = $oldtitlelink; // force old link so data gets saved
		}
	}
	// update page
	$page = new ZenpagePage($titlelink);
	$notice = processPasswordSave($page);
	$page->set('title',$title);
	$page->set('content',$content);
	$page->set('extracontent',$extracontent);
	$page->set('show',$show);
	$page->set('codeblock',$codeblock);
	$page->set('author',$author);
	$page->set('date',$date);
	$page->set('lastchange',$lastchange);
	$page->set('lastchangeauthor',$lastchangeauthor);
	$page->set('commentson',$commentson);
	$page->set('permalink',$permalink);
	$page->set('locked',$locked);
	$page->set('expiredate',$expiredate);
	if (getcheckboxState('resethitcounter')) {
		$page->set('hitcounter',0);
	}
	processTags($page);
	$msg = zp_apply_filter('update_page', '', $page, $oldtitlelink);
	$page->save();

	if (!$rslt) {
		$reports[] = "<p class='errorbox' id='fade-message'>".sprintf(gettext("A page with the title/titlelink <em>%s</em> already exists!"),$titlelink).'</p>';
	} else 	if(empty($title)) {
		$reports[] =  "<p class='errorbox' id='fade-message'>".sprintf(gettext("Page <em>%s</em> updated but you need to give it a <strong>title</strong> before publishing!"),get_language_string($titlelink)).'</p>';
	} else if ($notice == 'user') {
		$reports[] =  "<p class='errorbox' id='fade-message'>".gettext('You must supply a password for the Protected Page user').'</p>';
	} else if ($notice == 'pass') {
		echo "<p class='errorbox' id='fade-message'>".gettext('Your passwords were empty or did not match').'</p>';
	} else {
		$reports[] =  "<p class='messagebox' id='fade-message'>".sprintf(gettext("Page <em>%s</em> updated"),$titlelink).'</p>';
	}
	$reports[] =  $msg;
	return $page;
}


/**
 * Deletes a page (and also if existing its subpages) from the database
 *
 */
function deletePage($id=NULL,$sortorder=NULL) {
	if(is_null($id)) {
		$id = sanitize_numeric($_GET['del']);
	} else {
		$id = sanitize_numeric($id); 
	}
	if(is_null($sortorder)) {
		$sortorder = sanitize($_GET['sortorder']);
	} else {
		$sortorder = sanitize($sortorder); 
	}
	query("DELETE FROM " . prefix('obj_to_tag') . "WHERE `type`='zenpage_pages' AND `objectid`=" . $id);
	$result = query_full_array('SELECT `id` FROM '.prefix('zenpage_pages')." WHERE `sort_order` like '".$sortorder."-%'");
	if (is_array($result)) {
		foreach ($result as $row) {
			query("DELETE FROM " . prefix('obj_to_tag') . "WHERE `type`='zenpage_pages' AND `objectid`=" . $row['id']);
		}
	}
	query("DELETE FROM ".prefix('zenpage_pages')." WHERE id = ".$id." OR `sort_order` like '".$sortorder."-%'"); // Delete the actual page
	//query("DELETE FROM ".prefix('zenpage_pages')." WHERE OR `sort_order` like '".$sortorder."%'"); // delete subpages if there are some
	if(is_null($id)) return "<p class='messagebox' id='fade-message'>".gettext("Page successfully deleted!")."</p>";
	return '';
}


/**
 * Updates the sortorder of the pages list in the database
 *
 */
function updatePageSortorder(&$reports) {
	if(!empty($_POST['order'])) { // if someone didn't sort anything there are no values!
		parse_str($_POST['order'],$orderarray);
		$order = array();
		processOrder($orderarray['left-to-right'], $order);
		$parents = array('NULL');
		foreach ($order as $id=>$orderlist) {
			$id = str_replace('id_','',$id);
			$level = count($orderlist);
			$parents[$level] = $id;
			$myparent = $parents[$level-1];
			$sql = "UPDATE " . prefix('zenpage_pages') . " SET `sort_order` = '".implode('-',$orderlist)."', `parentid`= ".$myparent." WHERE `id`=" . $id;
			query($sql);
		}
	}
	$reports[] = "<br clear=\"all\"><p class='messagebox' id='fade-message'>".gettext("Sort order saved.")."</p>";
}


/**
 * Prints the table part of a single page item for the sortable pages list
 *
 * @param object $page The array containing the single page
 * @param bool $flag set to true to flag the element as having a problem with nesting level
 */
function printPagesListTable($page, $flag) {
	if ($flag) {
		$img = '../../images/drag_handle_flag.png';
	} else {
		$img = '../../images/drag_handle.png';
	}
	?>
 <table class='bordered2'>
	 <tr>
		<td class='sort-handle' style="padding-bottom: 15px; ">
			 <img src="<?php echo $img; ?>" alt="" style="position: relative; top: 7px; margin-right: 4px; width:14px; height:14px" />
		<?php if(checkIfLockedPage($page)) {
			echo "<a href='admin-edit.php?page&amp;titlelink=".urlencode($page->getTitlelink())."'> "; checkForEmptyTitle($page->getTitle(),"page"); echo "</a>".checkHitcounterDisplay($page->getHitcounter());
		} else {
			echo $page->getTitle(); checkHitcounterDisplay($page->getShow());
		}	?>
		</td>
		<td class="icons3">
			<?php
			 checkIfScheduled($page);
			 checkIfExpires($page);
			?>
		</td>
		<td class="icons3" style="text-align: left">
			<?php echo htmlspecialchars($page->getAuthor()) ;?>
		</td>
	<td class="icons">
	<?php
	if (isProtectedPage($page)) {
		echo '<img src="../../images/lock.png" style="border: 0px;" alt="'.gettext('Password protected').'" title="'.gettext('Password protected').'" />';
	}
	?>
	</td>
	<?php if(checkIfLockedPage($page)) { ?>
	<td class="icons">
		<?php printPublishIconLink($page,"page"); ?>
	</td>
	<td class="icons">
		<a href="?commentson=<?php echo $page->getCommentsAllowed(); ?>&amp;id=<?php echo $page->getID(); ?>&amp;add&amp;XSRFToken=<?php echo getXSRFToken('update')?>" title="<?php echo gettext("Enable or disable comments"); ?>">
		<?php echo checkIfCommentsAllowed($page->getCommentsAllowed(),true); ?></a>
	</td>
	<?php } else { ?>
	<td class="icons">
		<img src="../../images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
	</td>
	<td class="icons">
		<img src="../../images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
	</td>
	<?php } ?>

		<td class="icons">
			<a href="../../../index.php?p=<?php echo ZENPAGE_PAGES; ?>&amp;title=<?php echo $page->getTitlelink() ;?>" title="<?php echo gettext("View page"); ?>">
			<img src="images/view.png" alt="view" />
			</a>
		</td>

	<?php if(checkIfLockedPage($page)) { ?>
	<td class="icons">
		<a href="?hitcounter=1&amp;id=<?php echo $page->getID(); ?>&amp;add&amp;XSRFToken=<?php echo getXSRFToken('hitcounter')?>" title="<?php echo gettext("Reset hitcounter"); ?>">
		<img src="../../images/reset.png" alt="<?php echo gettext("Reset hitcounter"); ?>" /></a>
	</td>
	<td class="icons">
		<a href="javascript:confirmDelete('admin-pages.php?del=<?php echo $page->getID(); ?>&amp;sortorder=<?php echo $page->getSortorder(); ?>&amp;add&amp;XSRFToken=<?php echo getXSRFToken('delete')?>',deletePage)" title="<?php echo gettext("Delete page"); ?>">
		<img src="../../images/fail.png" alt="delete" /></a>
	</td>
	<td class="icons">
		<input type="checkbox" name="ids[]" value="<?php echo $page->getID(); ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
	</td>
	<?php } else { ?>
	<td class="icons">
		<img src="../../images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
	</td>
	<td class="icons">
		<img src="../../images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
	</td>
	<td class="icons">
		<img src="../../images/icon_inactive.png" alt="<?php gettext('locked'); ?>" />
	</td>
	<?php } ?>

	</tr>
 </table>
<?php
}


/**
 * Prints the sortable pages list
 * returns true if nesting levels exceede the database container
 *
 * @param array $pages The array containing all pages
 *
 * @return bool
 */
function printPagesList($pages) {
	$indent = 1;
	$open = array(1=>0);
	$rslt = false;
	foreach ($pages as $page) {
		$pageobj = new ZenpagePage($page['titlelink']);
		if (isMyPage($pageobj,ZENPAGE_PAGES_RIGHTS)) {
			$order = explode('-', $pageobj->getSortOrder());
			$level = max(1,count($order));
			if ($toodeep = $level>1 && $order[$level-1] === '') {
				$rslt = true;
			}
			if ($level > $indent) {
				echo "\n".str_pad("\t",$indent,"\t")."<ul class=\"page-list\">\n";
				$indent++;
				$open[$indent] = 0;
			} else if ($level < $indent) {
					while ($indent > $level) {
						$open[$indent]--;
						$indent--;
						echo "</li>\n".str_pad("\t",$indent,"\t")."</ul>\n";
					}
			} else { // indent == level
				if ($open[$indent]) {
					echo str_pad("\t",$indent,"\t")."</li>\n";
					$open[$indent]--;
				} else {
					echo "\n";
				}
			}
			if ($open[$indent]) {
				echo str_pad("\t",$indent,"\t")."</li>\n";
				$open[$indent]--;
			}
			echo str_pad("\t",$indent-1,"\t")."<li id=\"id_".$pageobj->getID()."\" class=\"clear-element page-item1 left\">";
			echo printPagesListTable($pageobj, $toodeep);
			$open[$indent]++;
		}
	}
	while ($indent > 1) {
		echo "</li>\n";
		$open[$indent]--;
		$indent--;
		echo str_pad("\t",$indent,"\t")."</ul>";
	}
	if ($open[$indent]) {
		echo "</li>\n";
	} else {
		echo "\n";
	}
	return $rslt;
}


/**************************
/* news article functions
***************************/

/**
 * Adds a new news article to the database from $_POST data and returns the object of that article
 *
 * @return object
 */
function addArticle(&$reports) {
	$date = date('Y-m-d_H-i-s');
	$title = process_language_string_save("title",2);
	$titlelink = seoFriendly(get_language_string($title));
	if (empty($titlelink)) $titlelink = seoFriendly($date);

	$author = sanitize($_POST['author']);
	$content = process_language_string_save("content",0); // TinyMCE already clears unallowed code
	$extracontent = process_language_string_save("extracontent",0); // TinyMCE already clears unallowed code
	$show = getcheckboxState('show');
	$date = sanitize($_POST['date']);
	$expiredate = getExpiryDatePost();
	$permalink = getcheckboxState('permalink');
	$commentson = getcheckboxState('commentson');
	$codeblock1 = sanitize($_POST['codeblock1'], 0);
	$codeblock2 = sanitize($_POST['codeblock2'], 0);
	$codeblock3 = sanitize($_POST['codeblock3'], 0);
	$codeblock = serialize(array("1" => $codeblock1, "2" => $codeblock2, "3" => $codeblock3));
	$locked = getcheckboxState('locked');

	$rslt = query_single_row('SELECT `id` FROM '.prefix('zenpage_news').' WHERE `titlelink`="'.zp_escape_string($titlelink).'"',true);
	if ($rslt) {
		$titlelink .= '_'.seoFriendly($date); // force unique so that data may be saved.
	}
	// create new article
	$article = new ZenpageNews($titlelink);
	$article->set('title',$title);
	$article->set('content',$content);
	$article->set('extracontent',$extracontent);
	$article->set('show',$show);
	$article->set('date',$date);
	$article->set('commentson',$commentson);
	$article->set('codeblock',$codeblock);
	$article->set('author',$author);
	$article->set('permalink',$permalink);
	$article->set('locked',$locked);
	$article->set('expiredate',$expiredate);
	$article->setSticky(sanitize_numeric($_POST['sticky']));
	processTags($article);
	$msg = zp_apply_filter('new_article', '', $article);
	$article->save();
	// create news2cat rows
	$result2 = query_full_array("SELECT * FROM ".prefix('zenpage_news_categories')." ORDER BY cat_name");
	foreach ($result2 as $cat) {
		if (isset($_POST["cat".$cat['id']])) {
			query("INSERT INTO ".prefix('zenpage_news2cat')." (cat_id, news_id) VALUES ('".$cat['id']."', '".$article->get('id')."')");
		}
	}
	if(empty($title)) {
		$reports[] =  "<p class='errorbox' id='fade-message'>".sprintf(gettext("Article <em>%s</em> added but you need to give it a <strong>title</strong> before publishing!"),get_language_string($titlelink)).'</p>';
	} else {
		$reports[] =  "<p class='messagebox' id='fade-message'>".sprintf(gettext("Article <em>%s</em> added"),$titlelink).'</p>';
	}
	$reports[] =  $msg;
	return $article;
}


/**
 * Updates a news article and returns the object of that article
 *
 * @return object
 */
function updateArticle(&$reports) {
	$date = date('Y-m-d_H-i-s');
	$title = process_language_string_save("title",2);
	$author = sanitize($_POST['author']);
	$content = process_language_string_save("content",0); // TinyMCE already clears unallowed code
	$extracontent = process_language_string_save("extracontent",0); // TinyMCE already clears unallowed code
	$show = getcheckboxState('show');
	$date = sanitize($_POST['date']);
	$expiredate = getExpiryDatePost();
	$permalink = getcheckboxState('permalink');
	$lastchange = sanitize($_POST['lastchange']);
	$lastchangeauthor = sanitize($_POST['lastchangeauthor']);
	$commentson = getcheckboxState('commentson');
	$codeblock1 = sanitize($_POST['codeblock1'], 0);
	$codeblock2 = sanitize($_POST['codeblock2'], 0);
	$codeblock3 = sanitize($_POST['codeblock3'], 0);
	$codeblock = serialize(array("1" => $codeblock1, "2" => $codeblock2, "3" => $codeblock3));
	$locked = getcheckboxState('locked');

	if (getcheckboxState('edittitlelink')) {
		$titlelink = sanitize($_POST['titlelink'],3);
	} else if($permalink) {
		$titlelink = sanitize($_POST['titlelink-old'],3);
	} else {
		$titlelink = seoFriendly(get_language_string($title));
	}
	if(empty($titlelink)) $titlelink = seoFriendly($date);

	$id = sanitize($_POST['id']);
	$rslt = true;
	$oldtitlelink = sanitize($_POST['titlelink-old']);
	if (getcheckboxState('edittitlelink')) { // title link change must be reflected in DB before any other updates
		$rslt = query('UPDATE '.prefix('zenpage_news').' SET `titlelink`="'.zp_escape_string($titlelink).'" WHERE `id`="'.$id.'"',true);
		if (!$rslt) {
			$titlelink = $oldtitlelink; // force old link so data gets saved
		}
	}
	// update article
	$article = new ZenpageNews($titlelink);
	$article->set('title',$title);
	$article->set('content',$content);
	$article->set('extracontent',$extracontent);
	$article->set('show',$show);
	$article->set('date',$date);
	$article->set('commentson',$commentson);
	$article->set('codeblock',$codeblock);
	$article->set('author',$author);
	$article->set('lastchange',$lastchange);
	$article->set('lastchangeauthor',$lastchangeauthor);
	$article->set('permalink',$permalink);
	$article->set('locked',$locked);
	$article->set('expiredate',$expiredate);
	$article->setSticky(sanitize_numeric($_POST['sticky']));
	if(getcheckboxState('resethitcounter')) {
		$page->set('hitcounter',0);
	}
	processTags($article);
	$msg = zp_apply_filter('update_article', '', $article, $oldtitlelink);
	$article->save();
	// create news2cat rows
	$result2 = query_full_array("SELECT * FROM ".prefix('zenpage_news_categories')." ORDER BY id");
	foreach($result2 as $cat) {

		// if category is sent
		if(isset($_POST["cat".$cat['id']])) {
			// check if category is already set in db, if not add it to news2cat
			$checkcat = query_single_row("SELECT cat_id, news_id FROM ".prefix('zenpage_news2cat')." WHERE cat_id = ".$cat['id']. " AND news_id = ".$article->get('id'));
			if(!$checkcat) {
				query("INSERT INTO ".prefix('zenpage_news2cat')." (cat_id, news_id) VALUES ('".$cat['id']."', '".$article->get('id')."')");
			}
			// if category is not sent, delete it from news2cat
		} else {
			query("DELETE FROM ".prefix('zenpage_news2cat')." WHERE cat_id = ".$cat['id']." AND news_id = ".$article->get('id'));
		}
	}
	if (!$rslt) {
		$reports[] =  "<p class='errorbox' id='fade-message'>".sprintf(gettext("An article with the title/titlelink <em>%s</em> already exists!"),$titlelink).'</p>';
	} else if(empty($title)) {
		$reports[] =  "<p class='errorbox' id='fade-message'>".sprintf(gettext("Article <em>%s</em> updated but you need to give it a <strong>title</strong> before publishing!"),get_language_string($titlelink)).'</p>';
	} else {
		$reports[] =  "<p class='messagebox' id='fade-message'>".sprintf(gettext("Article <em>%s</em> updated"),$titlelink).'</p>';
	}
	$reports[] =  $msg;
	return $article;
}


/**
 * Deletes an news article from the database
 *
 */
function deleteArticle($id=NULL) {
	if(is_null($id)) {
		$id = sanitize_numeric($_GET['del']);
	} else {
		$id = sanitize_numeric($id);
	}
	query("DELETE FROM " . prefix('obj_to_tag') . "WHERE `type`='zenpage_news' AND `objectid`=" . $id);
	query("DELETE FROM ".prefix('zenpage_news')." WHERE id = $id");  // remove the article
	query("DELETE FROM ".prefix('zenpage_news2cat')." WHERE news_id = $id"); // delete the category association
	if(is_null($id)) return "<p class='messagebox' id='fade-message'>".gettext("Article successfully deleted!")."</p>";
	return '';
}


/**
 * Print the categories of a news article for the news articles list
 *
 * @param obj $obj object of the news article
 */
function printArticleCategories($obj) {
	 $cat = $obj->getCategories();
	$number = 0;
	foreach ($cat as $cats) {
		$number++;
		if($number != 1) {
			echo ", ";
		}
		echo get_language_string($cats['cat_name']);
	}
}

/**
 * Print the categories of a news article for the news articles list
 *
 * @param obj $obj object of the news article
 */
function printPageArticleTags($obj) {
	 $tags = $obj->getTags();
	$number = 0;
	foreach ($tags as $tag) {
		$number++;
		if($number != 1) {
			echo ", ";
		}
		echo get_language_string($tag);
	}
}


/**
 * Prints the checkboxes to select and/or show the category of an news article on the edit or add page
 *
 * @param int $id ID of the news article if the categories an existing articles is assigned to shall be shown, empty if this is a new article to be added.
 * @param string $option "all" to show all categories if creating a new article without categories assigned, empty if editing an existing article that already has categories assigned.
 */
function printCategorySelection($id='', $option='') {
	global $_zp_current_zenpage_news;
	$all_cats = getAllCategories();
	$selected = '';
	echo "<ul class='zenpagechecklist'>\n";
	foreach ($all_cats as $cats) {
		if($option != "all") {
			$cat2news = query_single_row("SELECT cat_id FROM ".prefix('zenpage_news2cat')." WHERE news_id = ".$id." AND cat_id = ".$cats['id']);
			if($cat2news['cat_id'] != "") {
				$selected ="checked ='checked'";
			} else {
				$selected ="";
			}
		}
		$catname = get_language_string($cats['cat_name']);
		$catlink = $cats['cat_link'];
		if(isProtectedNewsCategory($cats['cat_link'])) {
			$protected = gettext('<em>(password protected)</em>');
		} else {
			$protected = '';
		}
		$catid = $cats['id'];
		echo "<li><label for='cat".$catid."'><input name='cat".$catid."' id='cat".$catid."' type='checkbox' value='".$catid."' ".$selected." />".$catname." ".$protected."</label></li>\n";
	}
	echo "</ul>\n";
}


/**
 * Prints the dropdown menu for the date archive selector for the news articles list
 *
 */
function printArticleDatesDropdown() {
	global $_zp_current_zenpage_news;
	$datecount = getAllArticleDates();
	$currentpage = getCurrentAdminNewsPage();
	$lastyear = "";
	$nr = "";
 ?>
	<form name="AutoListBox1" style="float:left; margin-left: 10px;" action="#" >
	<select name="ListBoxURL" size="1" onchange="gotoLink(this.form)">
 <?php
		if(!isset($_GET['date'])) {
			$selected = 'selected="selected"';
		 } else {
				$selected = "";
			}
		 echo "<option $selected value='admin-news-articles.php?pagenr=".$currentpage.getNewsAdminOptionPath(true,false,true)."'>".gettext("View all months")."</option>";
		while (list($key, $val) = each($datecount)) {
		$nr++;
		if ($key == '0000-00-01') {
			$year = "no date";
			$month = "";
		} else {
			$dt = strftime('%Y-%B', strtotime($key));
			$year = substr($dt, 0, 4);
			$month = substr($dt, 5);
		}
		if(isset($_GET['category'])) {
				$catlink = "&amp;category=".$_GET['category'];
			} else {
				$catlink = "";
			}
		$check = $month."-".$year;
		 if(isset($_GET['date']) AND $_GET['date'] == substr($key,0,7)) {
				$selected = "selected='selected'";
		 } else {
				$selected = "";
			}
			if(isset($_GET['date'])) {
				echo "<option $selected value='admin-news-articles.php?pagenr=".$currentpage.getNewsAdminOptionPath(true,false,true)."'>$month $year ($val)</option>\n";
			} else {
				echo "<option $selected value='admin-news-articles.php?pagenr=".$currentpage."&amp;date=".substr($key,0,7).getNewsAdminOptionPath(true,false,true)."'>$month $year ($val)</option>\n";
			}
	}
?>
	</select>
	<script language="JavaScript" type="text/javascript" >
		// <!-- <![CDATA[
		function gotoLink(form) {
		var OptionIndex=form.ListBoxURL.selectedIndex;
		parent.location = form.ListBoxURL.options[OptionIndex].value;}
		// ]]> -->
	</script>
	</form>
<?php
}


/**
 * Prints news articles list page navigation
 *
 */
function printArticlesPageNav() {
	global $_zp_zenpage_total_pages;
	$current = getCurrentAdminNewsPage();
	$total = $_zp_zenpage_total_pages;
	if($total > 1) {
		echo "<ul class=\"pagelist\">";
		if ($current != 1) {
			echo "<li class='prev'><a href='admin-news-articles.php?pagenr=".($current - 1).getNewsAdminOptionPath(true,true,true)."' title='".gettext("Prev Page")." ".($current - 1)."' >&laquo; ".gettext("prev")."</a></li>\n";
		} else {
			echo "<li class='prev'><span class='disabledlink'>&laquo; ".gettext("prev")."</span></li>\n";
		}
		$j=max(1, min($current-3, $total-6));
		if ($j != 1) {
			$p = max($j-4,1);
		echo "\n <li><a href='admin-news-articles.php?pagenr=".$p.' title="page '.$p.'">...</a></li>';
	}
	for ($i=$j; $i <= min($total, $j+6); $i++) {
			if($i == $current) {
				echo "<li>".$i."</li>\n";
			} else {
				echo "<li><a href='admin-news-articles.php?pagenr=".$i.getNewsAdminOptionPath(true,true,true)."' title='Page ".$i."'>".$i."</a></li>\n";
			}
		}
		if ($i < $total) {
			$p = min($j+10,$total);
			echo "\n <li><a href='admin-news-articles.php?pagenr=".$p.' title="page '.$p.'">...</a></li>';
		}
		echo "<li class=\"next\">";

		if ($current != $total)	{
			echo "<li class='next'><a href='admin-news-articles.php?pagenr=".min($j+10,$total).getNewsAdminOptionPath(true,true,true)."' title='".gettext("Next page")." ".min($j+10,$total)."'>".gettext("next")." &raquo;</a></li>\n";
		} else {
			echo "<li class='next'><span class='disabledlink'>".gettext("next")." &raquo;</span></li>\n";
		}
		echo "</ul>";
	}
}


/**
 * Prints the dropdown menu for the category selector for the news articles list
 *
 */
function printCategoryDropdown() {
	global $_zp_current_zenpage_news;
	$currentpage = getCurrentAdminNewsPage();
	$result = getAllCategories();
	if(isset($_GET['date'])) {
		$datelink = "&amp;date=".$_GET['date'];
		$datelinkall = "?date=".$_GET['date'];
	} else {
		$datelink = "";
		$datelinkall ="";
	}
?>
	<form name ="AutoListBox2" style="float:left" action="#" >
	<select name="ListBoxURL" size="1" onchange="gotoLink(this.form)">
<?php
if(!isset($_GET['category'])) {
			$selected = "selected='selected'";
		} else {
			$selected ="";
		}
		echo "<option $selected value='admin-news-articles.php?pagenr=".$currentpage.getNewsAdminOptionPath(false,true,true)."'>".gettext("All categories")."</option>";
	foreach ($result as $cat) {
		// check if there are articles in this category. If not don't list the category.
		$count = countArticles($cat['cat_link'],false);
		$count = " (".$count.")";
		if(isset($_GET['category']) AND $_GET['category'] === $cat['cat_link']) {
			$selected = "selected='selected'";
		} else {
			$selected ="";
		}
		if ($count != " (0)") {
			echo "<option $selected value='admin-news-articles.php?pagenr=".$currentpage."&amp;category=".$cat['cat_link'].getNewsAdminOptionPath(false,true,true)."'>".get_language_string($cat['cat_name']).$count."</option>\n";
		}
	}

?>
		</select>
		<script language="JavaScript" type="text/javascript" >
			// <!-- <![CDATA[
			function gotoLink(form) {
			var OptionIndex=form.ListBoxURL.selectedIndex;
			parent.location = form.ListBoxURL.options[OptionIndex].value;}
			// ]]> -->
	</script>
	</form>
<?php
}


/**
 * Creates the admin paths for news articles if you use the dropdowns for category, published and date together
 *
 * @param bool $categorycheck true or false if 'category' should be included in the url
 * @param bool $postedcheck true or false if 'date' should be included in the url
 * @param bool $publishedcheck true or false if 'published' should be included in the url
 * @return string
 */
function getNewsAdminOptionPath($categorycheck='', $postedcheck='',$publishedcheck='') {
	global $_zp_current_zenpage_news;
	$category = "";
	$posted = "";
	$published = "";
	if(isset($_GET['category']) AND $categorycheck === true) {
		$category = "&amp;category=".$_GET['category'];
	}
	if(isset($_GET['date']) AND $postedcheck === true) {
		$posted = "&amp;date=".$_GET['date'];
	}
	if(isset($_GET['published']) AND $publishedcheck === true) {
		$published = "&amp;published=".$_GET['published'];
	}
	$optionpath = $category.$posted.$published;
	return $optionpath;
}


/**
 * Prints the dropdown menu for the published/un-publishd selector for the news articles list
 *
 */
function printUnpublishedDropdown() {
	global $_zp_current_zenpage_news;
	$currentpage = getCurrentAdminNewsPage();
?>
<form name="AutoListBox3" style="float: left; margin-left: 10px;"	action="#">
	<select name="ListBoxURL" size="1"	onchange="gotoLink(this.form)">
	<?php
	$all="";
	$published="";
	$unpublished="";
	$sticky = '';
	if(isset($_GET['published'])) {
		switch ($_GET['published']) {
			case "no":
				$unpublished="selected='selected'";
				break;
			case "yes":
				$published="selected='selected'";
				break;
			case 'sticky':
				$sticky="selected='selected'";
				break;
		}
	} else {
		$all="selected='selected'";
	}
	echo "<option $all value='admin-news-articles.php?pagenr=".$currentpage.getNewsAdminOptionPath(true,true,false)."'>".gettext("All articles")."</option>\n";
	echo "<option $published value='admin-news-articles.php?pagenr=".$currentpage.getNewsAdminOptionPath(true,true,false)."&amp;published=yes'>".gettext("Published")."</option>\n";
	echo "<option $unpublished value='admin-news-articles.php?pagenr=".$currentpage.getNewsAdminOptionPath(true,true,false)."&amp;published=no'>".gettext("Un-published")."</option>\n";
	echo "<option $sticky value='admin-news-articles.php?pagenr=".$currentpage.getNewsAdminOptionPath(true,true,false)."&amp;published=sticky'>".gettext("Sticky")."</option>\n";
	?>
</select>
	<script language="JavaScript" type="text/javascript">
		// <!-- <![CDATA[
		function gotoLink(form) {
		var OptionIndex=form.ListBoxURL.selectedIndex;
		parent.location = form.ListBoxURL.options[OptionIndex].value;}
		// ]]> -->
	</script>
</form>
<?php
}


/**************************
/* Category functions
***************************/

/**
 * Handles saving of News Category passwords
 */
function processCategoryPasswordSave() {
	global $_zp_authority;
	$notify = $fail = '';
	$result = '';
	if (sanitize($_POST['password_enabled'])) {
		$result = array();
		$olduser = $_POST['olduser'];
		$newuser = $_POST['category_user'];
		$pwd = trim($_POST['categorypass']);
		if (($olduser != $newuser)) {
			if (!empty($newuser) && empty($pwd) && empty($pwd2)) {
				$fail = 'user';
			}
		}
		if (!$fail && $_POST['categorypass'] == $_POST['categorypass_2']) {
			$result['user'] = $newuser;
			$result['password_hint'] = process_language_string_save('category_hint', 3);
			if (empty($pwd)) {
				if (empty($_POST['categorypass'])) {
					$result['password'] = '';  // clear the password
				}
			} else {
				$result['password'] = $_zp_authority->passwordHash($newuser, $pwd);
			}
		} else {
			if (empty($fail)) {
				$notify = 'pass';
			} else {
				$notify = $fail;
			}
		}
	}
	if ($notify) return $notify;
	return $result;
}

/**
 * Adds a category to the database
 *
 */
function addCategory(&$reports) {
	$catname = process_language_string_save("category",2); // so that no \ are shown in the 'Category x added' message
	$catlink = seoFriendly(get_language_string($catname));
	$result = processCategoryPasswordSave();
	if (!is_array($result)) {
		$notice = $result;
		$result = array('user'=>'','password'=>'','password_hint'=>'');
	} else {
		$notice = false;
	}
	if(empty($catlink) OR empty($catname)) {
		$reports[] =  "<p class='errorbox' id='fade-message'>".gettext("You forgot to give your category a <strong>title or titlelink</strong>!")."</p>";
	} else if ($notice == 'user') {
		$reports[] =  "<p class='errorbox' id='fade-message'>".gettext('You must supply a password for the Protected Category user').'</p>';
	} else if ($notice == 'pass') {
		$reports[] =  "<p class='errorbox' id='fade-message'>".gettext('Your passwords were empty or did not match').'</p>';
	} else {
		$sql = "INSERT INTO ".prefix('zenpage_news_categories')." (cat_name, cat_link, permalink, user, password, password_hint) VALUES ('".
				zp_escape_string($catname)."', '".zp_escape_string(seoFriendly($catlink))."','".getcheckboxState('permalink')."', '".
				zp_escape_string($result['user'])."','".zp_escape_string($result['password'])."','".zp_escape_string($result['password_hint']).
				"')";
		if (query($sql, true)) {
			$reports[] =  "<p class='messagebox' id='fade-message'>".sprintf(gettext("Category <em>%s</em> added"),$catlink)."</p>";
		} else {
			$reports[] =  "<p class='errorbox' id='fade-message'>".sprintf(gettext("A category with the title/titlelink <em>%s</em> already exists!"),htmlspecialchars($catlink))."</p>";
		}
	}
}


/**
 * Updates a category
 *
 */
function updateCategory(&$reports) {
	global $_zp_current_zenpage_news;
	$result['id'] = sanitize_numeric($_POST['id']);
	$result['cat_name'] = process_language_string_save("category",2);
	$result['permalink'] = getcheckboxState('permalink');
	if($result['permalink']) {
		$result['cat_link'] = sanitize($_POST['catlink-old']);
	}
	if(getcheckboxState('edittitlelink')) {
		$result['cat_link'] = sanitize($_POST['catlink']);
	}
	if(!$result['permalink'] AND !getcheckboxState('edittitlelink')) {
		$result['cat_link'] = process_language_string_save("category",2);
		$result['cat_link'] = zp_escape_string(seoFriendly(get_language_string($result['cat_link'])));
	}
	$pwdresult = processCategoryPasswordSave();
	if (is_array($pwdresult)) {
		$notice = false;
		$passwordpart = ", user = '".zp_escape_string($pwdresult['user'])."', password = '".zp_escape_string($pwdresult['password']).
				"', password_hint = '".zp_escape_string($pwdresult['password_hint'])."'";
	} else {
		$notice = $pwdresult;
		$passwordpart = '';
	}
	// update the category in the category table
	$sql = "UPDATE ".prefix('zenpage_news_categories')." SET cat_name = '".$result['cat_name'].
				"', cat_link = '".zp_escape_string($result['cat_link'])."', permalink = ".$result['permalink'].
				$passwordpart." WHERE id = ".$result['id'];
	if(query($sql,true)) {
		if(empty($result['cat_name']) OR empty($result['cat_link'])) {
			$reports[] =  "<p class='errorbox' id='fade-message'>".gettext("You forgot to give your category a <strong>title or titlelink</strong>!")."</p>";
		} else if ($notice == 'user') {
			$reports[] =  "<p class='errorbox' id='fade-message'>".gettext('You must supply a password for the Protected Category user').'</p>';
		} else if ($notice == 'pass') {
			$reports[] =  "<p class='errorbox' id='fade-message'>".gettext('Your passwords were empty or did not match').'</p>';
		} else {
			$reports[] =  "<p class='messagebox' id='fade-message'>".gettext("Category updated!")."</p>";
		}
	} else {
		$reports[] =  "<p class='errorbox' id='fade-message'>".sprintf(gettext("A category with the title/titlelink <em>%s</em> already exists!"),htmlspecialchars($result['cat_link']))."</p>";
	}
	$result = getCategory($result['id']);
	return $result;
}


/**
 * Delets a category from the database
 *
 */
function deleteCategory(&$reports) {
	global $_zp_current_zenpage_news;
	if(isset($_GET['delete'])) {
		// check if the category is in use, don't delete
		$count = countArticles(sanitize($_GET['cat_link'],3),false);
		$delete = zp_escape_string(sanitize($_GET['delete'],3));
		if ($count != 0) {
			query("DELETE FROM ".prefix('zenpage_news2cat')." WHERE cat_id = '{$delete}'");
		}
		query("DELETE FROM ".prefix('zenpage_news_categories')." WHERE id = '{$delete}'");
		$reports[] =  "<p class='messagebox' id='fade-message'>".gettext("Category successfully deleted!")."</p>";
	}
}


function printCategoryList() {
	global $_zp_current_zenpage_news;
	$result = getAllCategories();
	foreach($result as $cat) {
		$count = countArticles($cat['cat_link'],false);
			if(get_language_string($cat['cat_name'])) {
				$catname = get_language_string($cat['cat_name']);
			} else {
				$catname = "<span style='color:red; font-weight: bold'>".gettext("Untitled category")."</span>";
			}
?>
 <tr class="newstr">
	<td><?php echo "<a href='admin-categories.php?edit&amp;id=".$cat['id']."&amp;tab=categories' title='".gettext('Edit this category')."'>".$catname."</a>".checkHitcounterDisplay($cat['hitcounter']); ?></td>
	<td class="icons3"><?php echo $count; ?> <?php echo gettext("articles"); ?></td>
	<td class="icons">
	<?php
	if (!empty($cat['password'])) {
		echo '<img src="../../images/lock.png" style="border: 0px;" alt="'.gettext('Password protected').'" title="'.gettext('Password protected').'" />';
	}
	?>
	</td>
	<td class="icons">
		<a href="?hitcounter=1&amp;id=<?php echo $cat['id'];?>&amp;tab=categories&amp;XSRFToken=<?php echo getXSRFToken('hitcounter')?>" title="<?php echo gettext("Reset hitcounter"); ?>">
		<img src="../../images/reset.png" alt="<?php echo gettext("Reset hitcounter"); ?>" />
		</a>
	</td>
	<td class="icons">
	<a href="javascript:confirmDelete('admin-categories.php?delete=<?php echo $cat['id']; ?>&amp;cat_link=<?php echo js_encode($cat['cat_link']); ?>&amp;tab=categories&amp;XSRFToken=<?php echo getXSRFToken('delete_category')?>',deleteCategory)" title="<?php echo gettext("Delete Category"); ?>"><img src="../../images/fail.png" alt="<?php echo gettext("Delete"); ?>" title="<?php echo gettext("Delete Category"); ?>" /></a>
	</td>
		<td class="icons">
			<input type="checkbox" name="ids[]" value="<?php echo $cat['id']; ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
		</td>
	</tr>
 <?php
	}
}


/**************************
/* General functions
***************************/

function checkForEmptyTitle($titlefield,$type) {
	switch($type) {
		case "page":
			$text = gettext("Untitled page");
		 break;
		case "news":
			$text = gettext("Untitled article");
			break;
		case "category":
			$text = gettext("Untitled category");
			break;
	}
	if($titlefield) {
		$title = strip_tags($titlefield);
	} else {
		$title = "<span style='color:red; font-weight: bold'>".$text."</span>";
	}
	echo $title;
}


/**
 * Publishes a page or news article
 *
 * @param string $option "page" or "news"
 * @param int $id the id of the article or page
 * @return string
 */
function publishPageOrArticle($option,$id) {
	switch ($option) {
		case "news":
			$dbtable = prefix('zenpage_news');
			break;
		case "page":
			$dbtable = prefix('zenpage_pages');
			break;
	}
	$show = sanitize_numeric($_GET['publish']);
	if ($show > 1) {
		query('UPDATE '.$dbtable.' SET `show` = "1", `expiredate`=NULL WHERE id = '.$id);
	} else {
		query("UPDATE ".$dbtable." SET `show` = ".$show." WHERE id = ".$id);
	}
}

/**
 * Skips the scheduled publishing by setting the date of a page or article to the current date to publish it immediately
 *
 * @param string $option "page" or "news"
 * @param int $id the id of the article or page
 * @return string
 */
function skipScheduledPublishing($option,$id) {
	switch ($option) {
		case "news":
			$dbtable = prefix('zenpage_news');
			break;
		case "page":
			$dbtable = prefix('zenpage_pages');
			break;
	}
	query("UPDATE ".$dbtable." SET `date` = '".date('Y-m-d H:i:s')."', `show`= 1 WHERE id = ".$id);
}

/**
 * Resets the hitcounter for a page, article or category
 *
 * @param string $option "news", "page" or "cat"
 */
function resetPageOrArticleHitcounter($option='') {
	switch ($option) {
		case "news":
			$dbtable = prefix('zenpage_news');
			break;
		case "page":
			$dbtable = prefix('zenpage_pages');
			break;
		case "cat":
			$dbtable = prefix('zenpage_news_categories');
			break;
	}
	$id = sanitize_numeric($_GET['id']);
	if($_GET['hitcounter']) {
		query("UPDATE ".$dbtable." SET `hitcounter` = 0 WHERE id = ".$id);
	}
}


/**
 * Checks if there are hitcounts and if they are displayed behind the news article, page or category title
 *
 * @param string $item The array of the current news article, page or category in the list.
 * @return string
 */
function checkHitcounterDisplay($item) {
	if($item == 0) {
		$hitcount = "";
	} else {
		if($item == 1) {
			$hits = gettext("hit");
		} else {
			$hits = gettext("hits");
		}
		$hitcount = " (".$item." ".$hits.")";
	}
	return $hitcount;
}


/**
 * returns an array of how many pages, articles, categories and news or pages comments we got.
 *
 * @param string $option What the statistic should be shown of: "news", "pages", "categories"
 */
function getNewsPagesStatistic($option) {
	global $_zp_current_zenpage_page, $_zp_current_zenpage_news;
	switch($option) {
		case "news":
			$items = getNewsArticles("","");
			$type = gettext("Articles");
			break;
		case "pages":
			$items = getPages(false);
			$type = gettext("Pages");
			break;
		case "categories":
			$type = gettext("Categories");
			$cats = getAllCategories();
			$total = count($cats);
			$unpub = 0;
			break;
	}
	if($option == "news" OR $option == "pages") {
		$total = count($items);
		$pub = 0;
		foreach($items as $item) {
			switch ($option) {
				case "news":
					$itemobj = new ZenpageNews($item['titlelink']);
					$show = $itemobj->getShow();
					break;
				case "pages":
					$itemobj = new ZenpagePage($item['titlelink']);
					$show = $itemobj->getShow();
					break;
				case "categories":
					$show = $item['show'];
					break;
			}
			if($show == 1) {
				$pub++;
			}
		}
		//echo " (un-published: ";
		$unpub = $total - $pub;
	}
	return array($total,$type,$unpub);
}

function printPagesStatistic() {
	list($total,$type,$unpub) = getNewsPagesStatistic("pages");
	if (empty($unpub)) {
		printf(ngettext('(<strong>%1$u</strong> page)','(<strong>%1$u</strong> pages)',$total),$total);
	} else {
		printf(ngettext('(<strong>%1$u</strong> page, <strong>%2$u</strong> un-published)','(<strong>%1$u</strong> pages, <strong>%2$u</strong> un-published)',$total),$total,$unpub);
	}
}
function printNewsStatistic() {
	list($total,$type,$unpub) = getNewsPagesStatistic("news");
	if (empty($unpub)) {
		printf(ngettext('(<strong>%1$u</strong> news)','(<strong>%1$u</strong> news)',$total),$total);
	} else {
		printf(ngettext('(<strong>%1$u</strong> news, <strong>%2$u</strong> un-published)','(<strong>%1$u</strong> news, <strong>%2$u</strong> un-published)',$total),$total,$unpub);
	}
}
function printCategoriesStatistic() {
	list($total,$type,$unpub) = getNewsPagesStatistic("categories");
	printf(ngettext('(<strong>%1$u</strong> category)','(<strong>%1$u</strong> categories)',$total),$total);
}

/**
 * Prints the links to JavaScript and CSS files zenpage needs.
 * Actually the same as for zenphoto but with different paths since we are in the plugins folder.
 *
 * @param bool $sortable set to true for tabs with sorts.
 *
 */
function zenpageJSCSS() {
	?>
	<link rel="stylesheet" href="zenpage.css" type="text/css" />
	<script type="text/javascript">
		// <!-- <![CDATA[
		$(document).ready(function(){
			$("#tip a").click(function() {
				$("#tips").toggle("slow");
			});
		});
		// ]]> -->
	</script>
<?php
}


function printZenpageIconLegend() { ?>
	<ul class="iconlegend">
	<li><img src="../../images/lock.png" alt="" /><?php echo gettext("Has Password"); ?></li>	<li><img src="../../images/pass.png" alt="" /><img	src="../../images/action.png" alt="" /><img src="images/clock.png" alt="" /><?php echo gettext("Published/Not published/Scheduled for publishing"); ?></li>
	<li><img src="../../images/comments-on.png" alt="" /><img src="../../images/comments-off.png" alt="" /><?php echo gettext("Comments on/off"); ?></li>
	<li><img src="../../images/view.png" alt="" /><?php echo gettext("View"); ?></li>
	<li><img src="../../images/reset.png" alt="" /><?php echo gettext("Reset hitcounter"); ?></li>
	<li><img src="../../images/fail.png" alt="" /><?php echo gettext("Delete"); ?></li>
	</ul>
<?php
}

/**
 * Prints a dropdown to select the author of a page or news article (Admin rights users only)
 *
 * @param string $currentadmin The current admin is selected if adding a new article, otherwise the original author
 */
function authorSelector($author=NULL) {
	global $_zp_authority,$_zp_current_admin_obj;
	if (empty($author)) {
		$author = $_zp_current_admin_obj->getUser();
	}
	?>
	<select size='1' name="author" id="author">
	<?php
	if (zp_loggedin(MANAGE_ALL_PAGES_RIGHTS)) {
		$admins = $_zp_authority->getAdministrators();
		foreach($admins as $admin) {
			if($admin['valid'] && $admin['rights'] & (ADMIN_RIGHTS | ZENPAGE_PAGES_RIGHTS)) {
				if($author == $admin['user']) {
					echo "<option selected='selected' value='".$admin['user']."'>".$admin['user']."</option>";
				} else {
					echo "<option value='".$admin['user']."'>".$admin['user']."</option>";
				}
			}
		}
	} else {
		?>
		<option selected='selected' value='<?php echo $author; ?>'><?php echo $author; ?></option>"
		<?php
	}
?>
</select>
<?php
}

/**
 * Checks if a page or articles has an expiration date set and prints out this date and a message about it or if it already is expired
 *
 * @param string $object Object of the page or news article to check
 * @return string
 */
function checkIfExpires($object) {
	$dt = $object->getExpireDate();
	if(!empty($dt)) {
		$expired = $dt < date('Y-m-d H:i:s');
		echo "<br /><small>";
		if ($expired) {
			echo '<strong class="expired">';printf(gettext('Expired: %s'),$dt); echo "</strong>";
		} else {
			echo '<strong class="expiredate">';printf(gettext("Expires: %s"),$dt); echo "</strong>";
		}
		echo "</small>";
	}
}

/**
 * Checks if a page or articles is scheduled for publishing and prints out a message and the future date or the publishing date if not scheduled.
 *
 * @param string $object Object of the page or news article to check
 * @return string
 */
function checkIfScheduled($object) {
	$dt = $object->getDateTime();
	if($dt > date('Y-m-d H:i:s')) {
		if($object->getShow() != 1) {
			echo '<strong class="inactivescheduledate">'.$dt.'</strong>';
		} else {
			echo '<strong class="scheduledate">'.$dt.'</strong>';
		}
	} else {
		echo $dt;
	}
}

/**
 * Prints the publish/un-published/scheduled publishing icon with a link for the pages and news articles list.
 *
 * @param string $object Object of the page or news article to check
 * @return string
 */
function printPublishIconLink($object,$type) {
	$urladd1 = "";$urladd2 = "";$urladd3 = "";
	if($type == "news") {
		if(isset($_GET['page'])) { $urladd1 = "&amp;page=".$_GET['page']; }
		if(isset($_GET['date'])) { $urladd2 = "&amp;date=".$_GET['date']; }
		if(isset($_GET['category'])) { $urladd3 = "&amp;category=".$_GET['category']; }
	}
	if ($object->getDateTime() > date('Y-m-d H:i:s')) {
		if ($object->getShow()) {
			$title = gettext("Publish immediately (skip scheduling)");
			?>
			<a href="?skipscheduling=1&amp;id=<?php echo $object->getID().$urladd1.$urladd2.$urladd3; ?>&amp;XSRFToken=<?php echo getXSRFToken('update')?>" title="<?php echo $title; ?>">
			<img src="images/clock.png" alt="<?php gettext("Scheduled for published"); ?>" title="<?php echo $title; ?>" /></a>
			<?php
		} else {
			$title = gettext("Enable scheduled publishing");
			?>
			<a href="?publish=1&amp;id=<?php echo $object->getID().$urladd1.$urladd2.$urladd3; ?>&amp;XSRFToken=<?php echo getXSRFToken('update')?>" title="<?php echo $title; ?>">
			<img src="../../images/action.png" alt="<?php echo gettext("Un-published"); ?>" title="<?php echo $title; ?>" /></a>
			<?php
		}
	} else {
		if ($object->getShow()) {
			$title = gettext("Un-publish");
			?>
			<a href="?publish=0&amp;id=<?php echo $object->getID().$urladd1.$urladd2.$urladd3; ?>&amp;XSRFToken=<?php echo getXSRFToken('update')?>" title="<?php echo $title; ?>">
			<img src="../../images/pass.png" alt="<?php echo gettext("Published"); ?>" title="<?php echo $title; ?>" /></a>
			<?php
		} else {
			$dt = $object->getExpireDate();
			if(empty($dt)) {
				$title = gettext("Publish");
				?>
				<a href="?publish=1&amp;id=<?php echo $object->getID().$urladd1.$urladd2.$urladd3; ?>&amp;XSRFToken=<?php echo getXSRFToken('update')?>">
				<?php
			} else {
				$title = gettext("Publish (override expiration)");
				?>
				<a href="?publish=2&amp;id=<?php echo $object->getID().$urladd1.$urladd2.$urladd3; ?>&amp;XSRFToken=<?php echo getXSRFToken('update')?>">
				<?php
			}
			?>
			<img src="../../images/action.png" alt="<?php echo gettext("Un-published"); ?>" title= "<?php echo $title; ?>" /></a>
			<?php
		}
	}
}

/**
 * Checks if a checkbox is selected and checks it if.
 *
 * @param string $field the array field of an item array to be checked (for example "permalink" or "comments on")
 */
function checkIfChecked($field) {
	if ($field) {
		echo 'checked="checked"';
	}
}

/**
 * Gets the db field $field of the $object (page or news article) if $object is an object.
 * Used to share the same page for page/news article add (no object) and edit (object available)
 *
 * @param object $object If this is an object the function returns the value of the db field $field
 * @param string $field The db field to get (Note: "parentid" and "sortorder" are not availabe for news articles!)
 * @return string
 */
function getIfObject($object,$field) {
 if(is_object($object)) {
		return $object->get($field);
	} else {
		return "";
	}
}

/**
 * Prints the db field $field of the $object (page or news article) if $object is an object.
 * Used to share the same page for page/news article add (no object) and edit (object available)
 *
 * @param object $object If this is an object the function returns the value of the db field $field
 * @param string $field The db field to get (Note: "parent id" is not availabe for news articles!)
 * @return string
 */
function printIfObject($object,$field) {
	echo getIfObject($object,$field);
}

/**
 * Checks if the current logged in admin user is the author that locked the page/article.
 * Only that author or any user with admin rights will be able to edit or unlock.
 *
 * @param object $page The array of the page or article to check
 * @return bool
 */
function checkIfLockedPage($page) {
	if (zp_loggedin(ADMIN_RIGHTS)) return true;
	if($page->getLocked()) {
		 return isMyPage($page,ZENPAGE_PAGES_RIGHTS);
	} else {
		return true;
	}
}

/**
 * Checks if the current logged in admin user is the author that locked the article.
 * Only that author or any user with admin rights will be able to edit or unlock.
 *
 * @param object $page The array of the page or article to check
 * @return bool
 */
function checkIfLockedNews($news) {
	if (zp_loggedin(ADMIN_RIGHTS)) return true;
	if($news->getLocked()) {
		 return isMyNews($news, ZENPAGE_NEWS_RIGHTS);
	} else {
		return true;
	}
}

/**
 * Checks if the current admin-edit.php page is called for news articles or for pages.
 *
 * @param string $page What you want to check for, "page" or "newsarticle"
 * @return bool
 */
function is_AdminEditPage($page) {
	switch ($page) {
		case "page":
			if(isset($_GET['page'])) {
				return TRUE;
			} else {
				return FALSE;
			}
			break;
		case "newsarticle":
			if(isset($_GET['newsarticle'])) {
				return TRUE;
			} else {
				return FALSE;
			}
			break;
	}
}


// test of the hack of shortschoolbus
function print_language_string_list_zenpage($dbstring, $name, $textbox=false, $locale=NULL) {
	global $_zp_active_languages, $_zp_current_locale;
	if (is_null($locale)) {
		if (is_null($_zp_current_locale)) {
			$_zp_current_locale = getUserLocale();
			if (empty($_zp_current_locale)) $_zp_current_locale = 'en_US';
		}
		$locale = $_zp_current_locale;
	}
	if($name == "content") { // for the different sizes of content and extracontent textareas
		$rows = "rows='35'";
	} else {
		$rows = "rows='10'";
	}
	$locale = $_zp_current_locale;
	if (preg_match('/^a:[0-9]+:{/', $dbstring)) {
		$strings =unserialize($dbstring);
	} else {
		$strings = array($locale=>$dbstring);
	}
	if (getOption('multi_lingual')) {
		if (is_null($_zp_active_languages)) {
			$_zp_active_languages = generateLanguageList();
		}
		$emptylang = array_flip($_zp_active_languages);
		unset($emptylang['']);
		natcasesort($emptylang);
		if ($textbox) $class = 'box'; else $class = '';
		echo "<ul class=\"zenpage_language_string_list".$class."\">\n";
		$empty = true;
		foreach ($emptylang as $key=>$lang) {
			if (isset($strings[$key])) {
				$string = $strings[$key];
				if (!empty($string)) {
					unset($emptylang[$key]);
					$empty = false;
					echo '<li><label for="'.$name.'_'.$key.'">';
					if ($textbox) {
						echo $lang;
						echo '<textarea id="'.$name.'_'.$key.'" name="'.$name.'_'.$key.'" class="mceEditor" cols="60"	'.$rows.' style="width:575px;">'.htmlentities($string,ENT_QUOTES,getOption("charset")).'</textarea><a href="javascript:toggleEditor(\''.$name.'_'.$key.'\');">' . gettext('Toggle Editor') . '</a><br /><br />';
					} else {
						echo '<input id="'.$name.'_'.$key.'" name="'.$name.'_'.$key.'" type="text" value="'.$string.'" size="96" style="width:400px;"/>'.$lang;
					}
					echo "</label></li>\n";
				}
			}
		}
		if ($empty) {
			$element = $emptylang[$locale];
			unset($emptylang[$locale]);
			$emptylang = array_merge(array($locale=>$element), $emptylang);
		}
		foreach ($emptylang as $key=>$lang) {
			echo '<li><label for="'.$name.'_'.$key.'">';
			if ($textbox) {
				echo $lang;
				echo '<textarea id="'.$name.'_'.$key.'" name="'.$name.'_'.$key.'" class="mceEditor" cols="60"	'.$rows.' style="width:575px;"></textarea><a href="javascript:toggleEditor(\''.$name.'_'.$key.'\');">' . gettext('Toggle Editor') . '</a><br /><br />';
			} else {
				echo '<input id="'.$name.'_'.$key.'" name="'.$name.'_'.$key.'" type="text" value="" size="96" style="width:400px;"/>'.$lang;
			}
			echo "</label></li>\n";

		}
		echo "</ul>\n";
	} else {
		if (empty($locale)) $locale = 'en_US';
		if (isset($strings[$locale])) {
			$dbstring = $strings[$locale];
		} else {
			$dbstring = array_shift($strings);
		}
		if ($textbox) {
			echo '<textarea id="'.$name.'_'.$locale.'" name="'.$name.'" class="mceEditor" cols="60"	'.$rows.' style="width:600px;">'.htmlentities($dbstring,ENT_QUOTES,getOption("charset")).'</textarea><a href="javascript:toggleEditor(\''.$name.'_'.$locale.'\');">' . gettext('Toggle Editor') . '</a><br /><br />';
		} else {
			echo '<input id="'.$name.'_'.$locale.'" name="'.$name.'_'.$locale.'" type="text" value="'.$dbstring.'" size="96" style="width:600px;"/>';
		}
	}
}


/**
 * Processes the check box bulk actions
 *
 */
function processZenpageBulkActions($type,&$reports) {
	if (isset($_POST['ids'])) {
		//echo "action for checked items:". $_POST['checkallaction'];
		$action = sanitize($_POST['checkallaction']);
		$ids = $_POST['ids'];
		$total = count($ids);
		$message = NULL;
		switch($type) {
			case 'pages':
				$dbtable = prefix('zenpage_pages');
				break;
			case 'news':
				$dbtable = prefix('zenpage_news');
				break;
			case 'newscategories':
				$dbtable = prefix('zenpage_news_categories');
				break;
		}
		if($action != 'noaction') {
			if ($total > 0) {
				$n = 0;
				switch($action) {
					case 'deleteall':
						$message = gettext('Selected items deleted');
						break;
					case 'showall':
						$sql = "UPDATE ".$dbtable." SET `show` = 1 WHERE ";
						$message = gettext('Selected items published');
						break;
					case 'hideall':
						$sql = "UPDATE ".$dbtable." SET `show` = 0 WHERE ";
						$message = gettext('Selected items unpublished');
						break;
					case 'commentson':
						$sql = "UPDATE ".$dbtable." SET `commentson` = 1 WHERE ";
						$message = gettext('Comments enabled for selected items');
						break;
					case 'commentsoff':
						$sql = "UPDATE ".$dbtable." SET `commentson` = 0 WHERE ";
						$message = gettext('Comments disabled for selected items');
						break;
					case 'resethitcounter':
						$sql = "UPDATE ".$dbtable." SET `hitcounter` = 0 WHERE ";
						$message = gettext('Hitcounter for selected items');
						break;
				}
				foreach ($ids as $id) {
					if($type != 'newscategories' && $action == 'deleteall') {
						switch($type) {
							case 'pages':
								// subpage deletion in deletePage() requires the sortorder so we need to get it via SQL
								$result = query_single_row('SELECT sort_order FROM '.$dbtable.' WHERE id = '.$id);
								if($result) {
									deletePage($id,$result['sort_order']);
								}
								break;
							case 'news':
								deleteArticle($id);
								break;
						}
					} else {
						$n++;
						$sql .= " id='".sanitize_numeric($id)."' ";
						if ($n < $total) $sql .= "OR ";
					}
				}
				if(($type != 'news' || $type != 'pages') && $action != 'deleteall') {
					query($sql);
				} 
				if(!is_null($message)) $reports[] = "<p class='messagebox fade-message'>".$message."</p>";
			}
		}
	}
}
?>