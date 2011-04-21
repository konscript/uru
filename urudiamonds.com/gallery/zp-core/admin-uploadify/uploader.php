<?php
define('OFFSET_PATH', 3);
require_once(dirname(dirname(__FILE__)).'/admin-functions.php');

$_zp_loggedin = NULL;
if (isset($_POST['auth'])) {
	$auth = $_POST['auth'];
	$admins = $_zp_authority->getAdministrators();
	foreach ($admins as $admin) {
		if (md5('admin-uploadify/uploader.php'.serialize($admin)) == $auth && $admin['rights'] & UPLOAD_RIGHTS) {
			$_zp_loggedin = $_zp_authority->checkAuthorization($admin['pass']);
			break;
		}
	}
}

admin_securityChecks(UPLOAD_RIGHTS, $return = currentRelativeURL(__FILE__));

if (!empty($_FILES)) {
	$name = trim(basename(sanitize($_FILES['Filedata']['name'],3)));
	if (isset($_FILES['Filedata']['error']) && $_FILES['Filedata']['error']) {
		debugLogArray('Uploadify error:', $_FILES);
		trigger_error(sprintf(gettext('Uploadify error on %1$s. Review your debug log.'),$name));
	} else {
		$tempFile = sanitize($_FILES['Filedata']['tmp_name'],3);
		$folder = trim(sanitize($_POST['folder'],3));
		if (substr($folder,0,1) == '/') {
			$folder = substr($folder,1);
		}
		$albumparmas = explode(':', $folder,3);
		$folder = trim($albumparmas[1]);
		if (substr($folder,0,1) == '/') {
			$folder = substr($folder,1);
		}
		if (substr($folder,-1) == '/') {
			$folder = substr($folder,0,-1);
		}
		$targetPath = getAlbumFolder().internalToFilesystem($folder);
		if (!empty($folder)) {
			if (!isMyAlbum($folder, UPLOAD_RIGHTS)) {
				if (!zp_apply_filter('admin_managed_albums_access',false, $return)) {
					header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
					exit();
				}
			}
			if (!is_dir($targetPath)) {
				mkdir_recursive($targetPath, CHMOD_VALUE);
				$album = new Album(new Gallery(), $folder);
				$album->setShow($albumparmas[0]!='false');
				$album->setTitle($albumparmas[2]);
				$album->save();
			}
			@chmod($targetPath, CHMOD_VALUE);
			$error = zp_apply_filter('check_upload_quota', UPLOAD_ERR_OK, $tempFile);
			if (!$error) {
				if (is_valid_image($name) || is_valid_other_type($name)) {
					$soename = seoFriendly($name);
					if (strrpos($soename,'.')===0) $soename = md5($name).$soename; // soe stripped out all the name.
					$targetFile =  $targetPath.'/'.internalToFilesystem($soename);

					$rslt = move_uploaded_file($tempFile,$targetFile);
					@chmod($targetFile, 0666 & CHMOD_VALUE);
					$album = new Album(New Gallery(), $folder);
					$image = newImage($album, $soename);
					if ($name != $soename && $image->getTitle() == substr($soename, 0, strrpos($soename, '.'))) {
						$image->setTitle(substr($name, 0, strrpos($name, '.')));
						$image->save();
					}

				} else if (is_zip($name)) {
					unzip($tempFile, $targetPath);
				}
			}
		}
	}
}


echo '1';

?>