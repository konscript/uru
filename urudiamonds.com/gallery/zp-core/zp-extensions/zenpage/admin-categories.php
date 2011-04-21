<?php
/**
 * zenpage admin-categories.php
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */
define("OFFSET_PATH",4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-functions.php');
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');
require_once("zenpage-admin-functions.php");

admin_securityChecks(ZENPAGE_NEWS_RIGHTS, currentRelativeURL(__FILE__));

$reports = array();
if(isset($_POST['processcheckeditems'])) {
	XSRFdefender('checkeditems');
	processZenpageBulkActions('newscategories',$reports);
}
if(isset($_GET['delete'])) {
	XSRFdefender('delete_category');
	deleteCategory($reports);
}
if(isset($_GET['hitcounter'])) {
	XSRFdefender('hitcounter');
	resetPageOrArticleHitcounter('cat');
}
if(isset($_GET['save'])) {
	XSRFdefender('save_categories');
	addCategory($reports);
}
if(isset($_GET['id'])){
	$result = getCategory($_GET['id']);
} else if(isset($_GET['update'])) {
	XSRFdefender('update_categories');
	$result = updateCategory($reports);
}

printAdminHeader();
zenpageJSCSS();
?>
<script type="text/javascript">
	//<!-- <![CDATA[
	var deleteCategory = "<?php echo gettext("Are you sure you want to delete this category? THIS CANNOT BE UNDONE!"); ?>";
	function confirmAction() {
		if ($('#checkallaction').val() == 'deleteall') {
			return confirm('<?php echo js_encode(gettext("Are you sure you want to delete the checked items?")); ?>');
		} else {
			return true;
		}
	}
	<?php if(isset($_GET["edit"])) { // prevent showing the message when adding page or article ?>
	$(document).ready(function() {
		if(jQuery('#edittitlelink:checked').val() != 1) {
			$('#catlink').attr("disabled", true);
		}
		$('#edittitlelink').change(function() {
			if(jQuery('#edittitlelink:checked').val() == 1) {
				$('#catlink').removeAttr("disabled");
			} else {
				$('#catlink').attr("disabled", true);
			}
		});
		$('form [name=checkeditems] #checkallaction').change(function(){
			if($(this).val() == 'deleteall') {
				// general text about "items" so it can be reused!
				alert('<?php echo js_encode(gettext('Are you sure you want to delete all selected items? THIS CANNOT BE UNDONE!')); ?>');
			}
		});
	});
	<?php } ?>
	// ]]> -->
</script>
</head>
<body>
<?php
printLogoAndLinks();
?>
<div id="main">
	<?php
	printTabs('articles');
	?>
	<div id="content">
		<?php
		printSubtabs('articles');
		?>
		<div id="tab_articles" class="tabbox">
			<?php
			foreach ($reports as $report) {
				echo $report;
			}
			?>
			<h1>
			<?php
			if(isset($_GET['edit'])) {
				echo gettext('Edit Category:').'<em> '; checkForEmptyTitle(get_language_string($result['cat_name']),'category'); echo '</em>';
			} else {
				echo gettext('Categories');
			}
			?>
			<span class="zenpagestats"><?php printCategoriesStatistic();?></span></h1>
			<p class="buttons">
			<span id="tip"><a href="#"><img src="images/info.png" alt="" /><?php echo gettext('Usage tips'); ?></a></span>
			<?php if(isset($_GET['edit'])) { ?>
				<a href="admin-categories.php?tab=categories"><img src="images/add.png" alt="" /><?php echo gettext('Add New Category'); ?></a>
			<?php } ?>
			</p>
			<br clear="all" /><br clear="all" />
			<div id="tips" style="display:none">
				<p><?php echo gettext("A search engine friendly <em>titlelink</em> (aka slug) without special characters to be used in URLs is generated from the title of the currently chosen language automatically if a new category is added. You can edit it later manually if necessary."); ?></p>
				<p><?php echo gettext("If you are editing a category check <em>Edit Titlelink</em> if you need to customize how the title appears in URLs. Otherwise it will be automatically updated to any changes made to the title. If you want to prevent this check <em>Enable permaTitlelink</em> and the titlelink stays always the same (recommended if you use Zenphoto's multilingual mode). <strong>Note: </strong> <em>Edit titlelink</em> overrides the permalink setting."); ?></p>
				<p><?php echo gettext("<strong>Important:</strong> If you are using Zenphoto's multi-lingual mode the Titlelink is generated from the Title of the currently selected language."); ?></p>
				<p><?php echo gettext("Hint: If you need more space for your text use TinyMCE's full screen mode (Click the blue square on the top right of editor's control bar)."); ?></p>
			</div>
			<div style="padding:15px; margin-top: 10px">
				<?php
				if(isset($_GET['edit'])) {
					$formname = 'update';
					$formaction = 'admin-categories.php?edit&amp;update&amp;tab=categories';
					$action = 'update_categories';
				} else {
					$formname = 'addcat';
					$formaction = 'admin-categories.php?save&amp;tab=categories';
					$action = 'save_categories';
				}
				?>
				<form method="post" name="<?php echo $formname; ?>update" action="<?php echo $formaction; ?>">
					<?php XSRFToken($action);?>
					<input	type="hidden" name="action" id="action" value="<?php echo htmlspecialchars($action,ENT_QUOTES); ?>" />
					<input	type="hidden" name="password_enabled" id="password_enabled" value="0" />
					<input	type="hidden" name="olduser" id="olduser" value="<?php if (isset($result)) echo $result['user']; ?>" />
					<?php
					if(isset($_GET['edit'])) {
						?>
						<input type="hidden" name="id" value="<?php echo $result['id'];?>" />
						<input type="hidden" name="catlink-old" id="catlink-old" value="<?php echo $result['cat_link']; ?>" />
						<?php
					}
					?>
					<table>
						<tr>
						 <?php
						 if(isset($_GET['edit'])) {
							$cattitlemessage = gettext('Category Title:');
						 } else {
							$cattitlemessage =  gettext('New Category Title:');
						 }
							?>
							<td class="topalign-padding"><?php echo $cattitlemessage; ?></td>
							<td><?php if(isset($_GET['edit'])) { print_language_string_list_zenpage($result['cat_name'],'category',false) ; } else { print_language_string_list_zenpage('','category',false) ;} ?>
								<input name="permalink" type="checkbox" id="permalink" value="1" <?php if(isset($_GET['edit'])) { checkIfChecked($result['permalink']); } else { echo 'checked="checked"'; } ?> /> <?php echo gettext('Enable permaTitleLink'); ?>
							</td>
						</tr>
						<?php
						if(isset($_GET['edit'])) {
							?>
							<tr>
								<td class="topalign-padding"><?php echo gettext('Category TitleLink:'); ?></td>
								<td><input name="catlink" type="text" size="85" id="catlink" value="<?php echo $result['cat_link']; ?>" />
								<input name="edittitlelink" type="checkbox" id="edittitlelink" value="1" /> <?php echo gettext('Edit TitleLink'); ?>
								</td>
							</tr>
							<?php
						} else {
							$result['user'] = $result['password'] = $result['password_hint'] = '';
						}
						?>
						<tr class="passwordextrashow">
							<td>
								<a href="javascript:toggle_passwords('',true);">
									<?php echo gettext("Category password:"); ?>
								</a>
							</td>
							<td>
								<?php
								$x = $result['password'];
								if (empty($x)) {
									?>
									<img src="../../images/lock_open.png" alt="" />
									<?php
								} else {
									$x = '          ';
									?>
									<img src="../../images/lock.png" alt="" />
									<?php
								}
								?>
							</td>
						</tr>
						<tr class="passwordextrahide" style="display:none">
							<td>
								<a href="javascript:toggle_passwords('',false);">
								<?php echo gettext("Category guest user:"); ?>
								</a>
							</td>
							<td colspan="2">
								<input type="text" size="<?php echo TEXT_INPUT_SIZE_SHORT; ?>" name="category_user" value="<?php echo htmlspecialchars($result['user']); ?>" />
							</td>
						</tr>
						<tr class="passwordextrahide" style="display:none">
							<td style="text-align:right">
								<?php echo gettext("Category password:"); ?>
								<br />
								<?php echo gettext("(repeat)"); ?>
							</td>
							<td colspan="2">
								<input type="password" size="<?php echo TEXT_INPUT_SIZE_SHORT; ?>" name="categorypass" value="<?php echo $x; ?>" />
								<br />
								<input type="password" size="<?php echo TEXT_INPUT_SIZE_SHORT; ?>" name="categorypass_2" value="<?php echo $x; ?>" />
							</td>
						</tr>
						<tr class="passwordextrahide" style="display:none">
							<td>
								<?php echo gettext("Category password hint:"); ?>
							</td>
							<td colspan="2">
							<?php print_language_string_list($result['password_hint'], 'category_hint', false, NULL, '', false); ?>
							</td>
						</tr>
						<tr class="passwordextrahide" style="display:none">
							<td colspan="2">
								<p class="notebox"><?php echo gettext('<strong>Note:</strong> Articles assigned to multiple categories will take the protection of the least strict category. So if the article belongs to any unprotected category it will be unprotected.');?></p>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<?php
								if(isset($_GET['edit'])) {
									$submittext =  gettext('Update Category');
								} else {
									$submittext =  gettext('Save New Category');
								}
								?>
								<p class="buttons">
									<button type="submit" title="<?php echo $submittext; ?>">
										<img src="../../images/pass.png" alt="" />
										<strong><?php echo $submittext; ?></strong>
									</button>
								</p>
								<p class="buttons">
									<button type="reset" title="<?php echo gettext('Reset'); ?>">
										<img src="../../images/reset.png" alt="" />
										<strong><?php echo gettext('Reset'); ?></strong>
									</button>
								</p>
								<br clear="all" /><br />
							</td>
						</tr>
					</table>
				</form>
				<hr />
			<form action="admin-categories.php?page=news&amp;tab=categories" method="post" id="checkeditems" name="checkeditems" onsubmit="return confirmAction();">
				<?php XSRFToken('checkeditems');?>
				<input	type="hidden" name="action" id="action" value="checkeditems" />
			<input name="processcheckeditems" type="hidden" value="apply" />
			<p class="buttons">
				<button type="submit" title="<?php echo gettext('Apply'); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext('Apply'); ?></strong></button>
			</p>
			<br clear="all" /><br />
				<table class="bordered">
				 <tr>
					<th colspan="6"><?php echo gettext('Edit this Category'); ?>
						<?php
						$checkarray = array(
						gettext('*Bulk actions*') => 'noaction',
						gettext('Delete') => 'deleteall',
						gettext('Reset hitcounter') => 'resethitcounter',
						);
						?>
						<span style="float: right">
							<select name="checkallaction" id="checkallaction" size="1">
							<?php generateListFromArray(array('noaction'), $checkarray,false,true); ?>
							</select>
						</span>

					</th>
					</tr>
					<tr class="newstr">
						<td class="subhead" colspan="6">
							<label style="float: right"><?php echo gettext("Check All"); ?> <input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />
							</label>
						</td>
					</tr>
					<?php printCategoryList(); ?>
					</table>
				<p class="buttons"><button type="submit" title="<?php echo gettext('Apply'); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext('Apply'); ?></strong></button></p>

			</form>
			</div> <!-- box -->
			<ul class="iconlegend">
				<li><img src="../../images/lock.png" alt="" /><?php echo gettext("Has Password"); ?></li>
				<li><img src="../../images/reset.png" alt="" /><?php echo gettext('Reset hitcounter'); ?></li>
				<li><img src="../../images/fail.png" alt="" /><?php echo gettext('Delete category'); ?></li>
			</ul>
		</div> <!-- tab_articles -->
	</div> <!-- content -->
</div> <!-- main -->
<?php printAdminFooter(); ?>

</body>
</html>
