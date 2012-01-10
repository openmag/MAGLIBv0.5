<?php

define('CHANGE_TYPE_NONE', 'nochange');
define('CHANGE_TYPE_ADD',  'add');
define('CHANGE_TYPE_DEL',  'delete');
define('CHANGE_TYPE_MOVE', 'move');
define('CHANGE_TYPE_MIX',  'mix');

class LCSTool {
	private $__seq1 = null;
	private $__seq2 = null;
	private $__matrix = null;
	private $__path = null;

	public function __construct($str1, $str2, $tokens) {
		if($this->normalize($str1, $str2, $tokens)) {
			$this->newMatrix();
			$this->lcsMatrix();
			$this->__path = $this->trace();
			#var_dump($this->__path);
		}
	}

	private function normalize($str1, $str2, $tokens) {
		#_log("str1: $str1");
		#_log("str2: $str2");
		for($s1=0,$s2=0;$s1 < strlen($str1) && $s2 < strlen($str2) && substr($str1, $s1, 1) === substr($str2, $s2, 1); $s1++,$s2++) {
			#echo substr($str1, $s1, 1)." ".substr($str2, $s2, 1)."\n";
			#if(substr($str1, $s1, 1) == substr($str2, $s2, 1)) {
			#	break;
			#}
		}
		for($e1=strlen($str1),$e2=strlen($str2);$e1 > $s1 && $e2 > $s2 && substr($str1, $e1-1, 1) === substr($str2, $e2-1, 1); $e1--,$e2--) {
			#if(substr($str1, $e1-1, 1) === substr($str2, $e2-1, 1)) {
			#	break;
			#}
		}
		if($e1 > $s1) {
			$str1 = substr($str1, $s1, $e1 - $s1);
		}else {
			$str1 = '';
		}
		if($e2 > $s2) {
			$str2 = substr($str2, $s2, $e2 - $s2);
		}else {
			$str2 = '';
		}
		#echo $str1."\n";
		#echo $str2."\n";
		#_log("str1: $str1 $s1 $e1");
		#_log("str2: $str2 $s2 $e2");
		if(strlen($str1) == 0 && strlen($str2) == 0) {
			return FALSE;
		}elseif(empty($str1) && empty($str2)) {
			return FALSE;
		}else {
			$this->__seq1 = $this->tokenize($str1, $tokens);
			$this->__seq2 = $this->tokenize($str2, $tokens);
			return TRUE;
		}
	}

	private function tokenize($str, $tokens) {
		$subseq = array();
		while(strlen($str) > 0) {
			#echo $str."\n";
			$pos = -1;
			$tok = null;
			foreach($tokens as $t) {
				$tpos = strpos($str, $t);
				if($tpos !== FALSE && ($pos < 0 || $pos > $tpos)) {
					$pos = $tpos;
					$tok = $t;
				}
			}
			#echo $pos."\n";
			if($pos >= 0) {
				if($pos > 0) {
					$subseq[] = substr($str, 0, $pos);
					$str = substr($str, $pos);
				}
				$subseq[] = $tok;
				$str = substr($str, strlen($tok));
			}else {
				$subseq[] = $str;
				$str = '';
			}
		}
		return $subseq;
	}

	private function newMatrix() {
		for($i = 0; $i < count($this->__seq1); $i ++) {
			$this->__matrix[] = array();
			for($j = 0; $j < count($this->__seq2); $j ++) {
				$this->__matrix[$i][] = array();
			}
		}
	}

	private function valueOf($i, $j) {
		if($i < 0 && $j < 0) {
			return array(0, '.', 0);
		}elseif($i < 0 && $j >= 0) {
			return array(0, '-', 0);
		}elseif($i >= 0 && $j < 0) {
			return array(0, '|', 0);
		}else {
			return $this->__matrix[$i][$j];
		}
	}

	private function getDirChange($sub, $dir) {
		if($sub[1] == '.' || $dir == '.' || $sub[1] == $dir) {
			return $sub[2];
		}else {
			return $sub[2] + 1;
		}
	}

	private function lcsMatrix() {
		for($i = 0; $i < count($this->__seq1); $i ++) {
			for($j = 0; $j < count($this->__seq2); $j ++) {
				if($this->__seq1[$i] === $this->__seq2[$j]) {
					$sub = $this->valueOf($i - 1, $j - 1);
					$this->__matrix[$i][$j][0] = $sub[0]+1;
					$this->__matrix[$i][$j][1] = '.';
					$this->__matrix[$i][$j][2] = $sub[2];
				}else {
					$sub1 = $this->valueOf($i - 1, $j);
					$sub2 = $this->valueOf($i, $j - 1);
					if($sub1[0] > $sub2[0]) {
						$this->__matrix[$i][$j][0] = $sub1[0];
						$this->__matrix[$i][$j][1] = '|';
						$this->__matrix[$i][$j][2] = $this->getDirChange($sub1, '|');
					}elseif($sub1[0] < $sub2[0]) {
						$this->__matrix[$i][$j][0] = $sub2[0];
						$this->__matrix[$i][$j][1] = '-';
						$this->__matrix[$i][$j][2] = $this->getDirChange($sub2, '-');
					}else {
						if($this->getDirChange($sub1, '|') >= $this->getDirChange($sub2, '-')) {
							$this->__matrix[$i][$j][0] = $sub1[0];
							$this->__matrix[$i][$j][1] = '|';
							$this->__matrix[$i][$j][2] = $this->getDirChange($sub1, '|');
						}else {
							$this->__matrix[$i][$j][0] = $sub2[0];
							$this->__matrix[$i][$j][1] = '-';
							$this->__matrix[$i][$j][2] = $this->getDirChange($sub2, '-');
						}
					}
				}
			}
		}
	}

	private function trace() {
		$path = array();
		$path[] = array(count($this->__seq1), count($this->__seq2));
		$i = count($this->__seq1) - 1;
		$j = count($this->__seq2) - 1;
		while($i >= -1 && $j >= -1) {
			$sub = $this->valueOf($i, $j);
			if($sub[1] == '.') {
				array_splice($path, 0, 0, array(array($i, $j)));
				$i--;
				$j--;
			}elseif($sub[1] == '|') {
				$i--;
			}else {
				$j--;
			}
		}
		return $path;
	}

	private function subString($seq, $begin, $end) {
		$str = '';
		for($i = $begin; $i < $end; $i ++) {
			$str .= $seq[$i];
		}
		return $str;
	}

	private function subOffset($seq, $offset) {
		$len = 0;
		for($i = 0; $i < $offset; $i ++) {
			$len += strlen($seq[$i]);
		}
		return $len;
	}

	public function getDiff() {
		$diff = array();
		for($i = 1; $this->__path != null && $i < count($this->__path); $i ++) {
			$pos1 = $this->__path[$i-1];
			$pos2 = $this->__path[$i];
			if($pos1[0] + 1 == $pos2[0] && $pos1[1] + 1 == $pos2[1]) {
			}elseif($pos1[0] + 1 <  $pos2[0] && $pos1[1] + 1 == $pos2[1]) {
				$diff[] = array('-', $this->subOffset($this->__seq1, $pos1[0]+1), $this->subString($this->__seq1, $pos1[0]+1, $pos2[0]));
			}elseif($pos1[0] + 1 == $pos2[0] && $pos1[1] + 1 < $pos2[1]) {
				$diff[] = array('+', $this->subOffset($this->__seq1, $pos1[0]+1), $this->subString($this->__seq2, $pos1[1]+1, $pos2[1]));
			}else {
				$diff[] = array('x', $this->subOffset($this->__seq1, $pos1[0]+1), $this->subString($this->__seq1, $pos1[0]+1, $pos2[0]), $this->subString($this->__seq2, $pos1[1]+1, $pos2[1]));
			}
		}
		return $diff;
	}

	public function getChangeType() {
		$diff = $this->getDiff();
		if(count($diff) == 0) {
			return CHANGE_TYPE_NONE;
		}
		$add_count = 0;
		$del_count = 0;
		$replace_count = 0;
		$del_str = '';
		$add_str = '';
		for($i = 0; $i < count($diff); $i ++) {
			if($diff[$i][0] == '-') {
				$del_count++;
				$del_str = $diff[$i][2];
			}elseif($diff[$i][0] == '+') {
				$add_count++;
				$add_str = $diff[$i][2];
			}else {
				$replace_count++;
			}
		}
		if($replace_count > 0) {
			return CHANGE_TYPE_MIX;
		}elseif($add_count > 0 && $del_count == 0) {
			return CHANGE_TYPE_ADD;
		}elseif($add_count == 0 && $del_count > 0) {
			return CHANGE_TYPE_DEL;
		}elseif($add_count == 1 && $del_count == 1) {
			if($del_str == $add_str) {
				return CHANGE_TYPE_MOVE;
			}else {
				return CHANGE_TYPE_MIX;
			}
		}else {
			return CHANGE_TYPE_MIX;
		}
	}
}


/*
function _log($str) {
	echo $str."\n";
}

$str1 = "AB|CDE|X|FF|FF|G";
$str2 = "AB|CDE|FF|FF";
$str1 = ",{\"_type\":\"MENUITEM\",\"_title\":\"\u62a5\u4ef7\u5355\",\"_id\":\"\",\"_target\":\"__new_\",\"_link\":\"http:\/\/10.168.44.254\/MAGLIBdev\/magtest\/services.php?_action=QUOTATION\",\"_expire\":604800000}";
$str2 = "";

echo $str1."\n";
echo $str2."\n";

$lcs = new LCSTool($str1, $str2, array('{', '[', ']', '}'));
#$diff = $lcs->getDiff();
#var_dump($diff);
echo $lcs->getChangeType()."\n";
*/


?>
