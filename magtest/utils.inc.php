<?php

function getSizeStr($size) {
	if($size >= 10*1024*1024) {
		return ((int)($size*1.0/1024/1024))."(MB)";
	}else if($size >= 1024*1024) {
		return (((int)($size*10.0/1024/1024))/10.0)."(MB)";
	}else if($size >= 10*1024) {
		return ((int)($size*1.0/1024))."(KB)";
	}else if($size >= 1024) {
		return (((int)($size*10.0/1024))/10.0)."(KB)";
	}else {
		return $size."(B)";
	}
}

function getFileIcon($suffix) {
	switch($suffix) {
	case ".xsl":
		return "icon_xsl.png";
	case ".ppt":
		return "icon_ppt.png";
	case ".doc":
		return "icon_doc.png";
	case ".pdf":
		return "icon_pdf.png";
	default:
		return "icon_unknown.png";
	}
}

?>
