<?php
/**
 */
define('OFFSET_PATH', 1);

require_once(dirname(__FILE__).'/admin-functions.php');
require_once(dirname(__FILE__).'/admin-globals.php');

admin_securityChecks(FILES_RIGHTS, currentRelativeURL(__FILE__));

printAdminHeader();

echo "\n</head>";
?>

<body>

<?php	printLogoAndLinks(); ?>
<div id="main">
	<?php printTabs('upload'); ?>
	<div id="content">
		<div id="container">
			<?php printSubtabs('upload','files'); ?>
			<div class="tabbox">
				<h1><?php echo gettext('File Manager'); ?></h1>
				<?php
				$locale = substr(getOption("locale"),0,2);
				if (empty($locale)) $locale = 'en';
				?>
				<iframe src="zp-extensions/tiny_mce/plugins/ajaxfilemanager/ajaxfilemanager.php?language=<?php echo $locale; ?>&tab=files" width="100%" height="480" style="border: 0">
				</iframe>
			</div>
		</div>
	</div>
</div>
<br clear="all" />
<?php printAdminFooter(); ?>

</body>
</html>
