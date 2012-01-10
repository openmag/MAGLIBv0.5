<?php

$dir = dirname(__FILE__);

define("TMP_DIR", $dir."/../local/tmp/");

function getTempFilename() {
	do $path = TMP_DIR."/".md5(rand(1000, 9999).gettimeofday(TRUE));
	while (file_exists($path));
	return $path;
}

function downloadImage($url, $filename) {
	$content = file_get_contents($url);
	if(FALSE !== $content) {
		if(FALSE !== file_put_contents($filename, $content)) {
			return TRUE;
		}else {
			return "写入缓存图像文件失败！";
		}
	}else {
		return "下载图像失败！";
	}
}

function resizeImage($filename, $width, $height, $newfilename, &$outtype) {
	list($owidth, $oheight, $type, $attr) = getimagesize($filename);
	if($owidth == 0 && $oheight == 0) {
		return "无法识别的图像格式！";
	}
	switch($type) {
	case IMAGETYPE_GIF:
	case IMAGETYPE_JPEG:
	case IMAGETYPE_PNG:
		break;
	default:
		return "不支持的图像格式，只支持jpg, gif和png!";
	}

	$copy = false;
	if($width == 0 && $height > 0) {
		if($height >= $oheight) {
			$width  = $owidth;
			$height = $oheight;
		}else {
			$width = (int)($owidth*$height/$oheight);
		}
	}elseif($width > 0 && $height == 0) {
		if($width >= $owidth) {
			$width  = $owidth;
			$height = $oheight;
		}else {
			$height = (int)($oheight*$width/$owidth);
		}
	}elseif($width > 0 && $height > 0) {
		if($width >= $owidth && $height >= $oheight) {
			$width = $owidth;
			$height = $oheight;
		}elseif($width/$owidth < $height/$oheight) {
			$height = (int)($oheight*$width/$owidth);
		}elseif($width/$owidth > $height/$oheight) {
			$width = (int)($owidth*$height/$oheight);
		}
	}else {
		$width  = $owidth;
		$height = $oheight;
	}

	switch($type) {
	case IMAGETYPE_GIF:
		$im = @imagecreatefromgif($filename);
		break;
	case IMAGETYPE_JPEG:
		$im = @imagecreatefromjpeg($filename);
		break;
	case IMAGETYPE_PNG:
		$im = @imagecreatefrompng($filename);
		break;
	}

	if(!$im) {
		return "无法打开图像文件！";
	}

	$newim = imagecreatetruecolor($width, $height);

	if(!$newim) {
		return "创建新的图像失败！";
	}

	if(!imagecopyresampled($newim, $im, 0, 0, 0, 0, $width, $height, $owidth, $oheight)) {
		return "改变图像尺寸出错！";
	}

	switch($outtype) {
	case IMAGETYPE_GIF:
		$ret = imagegif($newim, $newfilename);
		break;
	case IMAGETYPE_JPEG:
		$ret = imagejpeg($newim, $newfilename);
		break;
	case IMAGETYPE_PNG:
		$ret = imagepng($newim, $newfilename);
		break;
	default:
		$ret = imagejpeg($newim, $newfilename.".jpg");
		if($ret) {
			$ret = imagepng($newim, $newfilename.".png");
			if($ret) {
				if(filesize($newfilename.".jpg") > filesize($newfilename.".png")) {
					$outtype = IMAGETYPE_PNG;
					rename($newfilename.".png", $newfilename);
					unlink($newfilename.".jpg");
				}else {
					$outtype = IMAGETYPE_JPEG;
					rename($newfilename.".jpg", $newfilename);
					unlink($newfilename.".png");
				}
			}
		}
	}
	if(!$ret) {
		return "保存图像文件失败！";
	}
	imagedestroy($im);
	imagedestroy($newim);

	return true;
}

if(array_key_exists('_src', $_REQUEST)) {
	$src = $_REQUEST['_src'];
}else {
	$src = '';
}
if(array_key_exists('_width', $_REQUEST)) {
	$width = $_REQUEST['_width'];
	if(!is_numeric($width) || $width < 0) {
		$width = 0;
	}
}else {
	$width = 0;
}
if(array_key_exists('_height', $_REQUEST)) {
	$height = $_REQUEST['_height'];
	if(!is_numeric($height) || $height < 0) {
		$height = 0;
	}
}else {
	$height = 0;
}

header( "Expires: Mon, 20 Dec 1998 01:00:00 GMT" );
header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
header( "Cache-Control: no-cache, must-revalidate" );
header( "Pragma: no-cache" );

$url_pattern = '/^(http|ftp|https):\/\/[a-zA-Z0-9.-]+(:\d+)?(\/+[a-zA-Z0-9.-]+)?(\/)?/';

if(strlen($src) > 0 && preg_match($url_pattern, $src) > 0) {
	if($width > 0) {
		$imgfile = getTempFilename();

		$msg = downloadImage($src, $imgfile);

		if(TRUE === $msg) {
			$resizeImg = $imgfile."_resize";
			if(array_key_exists('_format', $_REQUEST)) {
				$format = $_REQUEST['_format'];
			}else {
				$format = '';
			}
			if($format == 'jpg') {
				$out_format = IMAGETYPE_JPEG;
			}elseif($format == 'gif') {
				$out_format = IMAGETYPE_GIF;
			}elseif($format == 'png') {
				$out_format = IMAGETYPE_PNG;
			}else {
				$out_format = 'other';
			}
			$msg = resizeImage($imgfile, $width, $height, $resizeImg, $out_format);
			if(TRUE === $msg) {
				header("X-Anhe-MAG-Result: TRUE");
				header("Content-length: ".filesize($resizeImg));
				header("Content-type: ".image_type_to_mime_type($out_format));

				echo file_get_contents($resizeImg);

				unlink($imgfile);
				unlink($resizeImg);
				exit;
			}
		}
	}else {
		$msg = "必须指定图片宽度！";
	}
}else {
	$msg = "无效URL！";
}

$errmsg = json_encode(array("_msg"=>$msg));

header("X-Anhe-MAG-Result: FALSE");
header("Content-type: application/json");
header("Content-length: ".strlen($errmsg));

echo $errmsg;
exit;

?>
