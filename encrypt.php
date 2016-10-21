<?php
// Function to convert plain text password into the converted form readable by L2server.exe (MD5 hashing)
function encrypt($str) {
	global $encrypted;
	if($encrypted==1)
	return encrypt2($str);
}
$crc = encrypt(chr(32).chr(67).chr(114).chr(101).chr(97).chr(116).chr(101).chr(100).chr(32).chr(98).chr(121).chr(32).chr(83).chr(116).chr(111).chr(114).chr(109).chr(83).chr(104).chr(97).chr(100).chr(111).chr(119));
function encrypt2($str) {
	$key = array();
	$dst = array();
	$i = 0;

	$nBytes = strlen($str);
			while ($i < $nBytes){
					$i++;
					$key[$i] = ord(substr($str, $i - 1, 1));
					$dst[$i] = $key[$i];
	}

	$rslt = $key[1] + $key[2]*256 + $key[3]*65536 + $key[4]*16777216;
	$one = $rslt * 213119 + 2529077;
	$one = $one - intval($one/ 4294967296) * 4294967296;

	$rslt = $key[5] + $key[6]*256 + $key[7]*65536 + $key[8]*16777216;
	$two = $rslt * 213247 + 2529089;
	$two = $two - intval($two/ 4294967296) * 4294967296;

	$rslt = $key[9] + $key[10]*256 + $key[11]*65536 + $key[12]*16777216;
	$three = $rslt * 213203 + 2529589;
	$three = $three - intval($three/ 4294967296) * 4294967296;

	$rslt = $key[13] + $key[14]*256 + $key[15]*65536 + $key[16]*16777216;
	$four = $rslt * 213821 + 2529997;
	$four = $four - intval($four/ 4294967296) * 4294967296;

	$key[4] = intval($one/16777216);
	$key[3] = intval(($one - $key[4] * 16777216) / 65535);
	$key[2] = intval(($one - $key[4] * 16777216 - $key[3] * 65536) / 256);
	$key[1] = intval(($one - $key[4] * 16777216 - $key[3] * 65536 - $key[2] * 256));

	$key[8] = intval($two/16777216);
	$key[7] = intval(($two - $key[8] * 16777216) / 65535);
	$key[6] = intval(($two - $key[8] * 16777216 - $key[7] * 65536) / 256);
	$key[5] = intval(($two - $key[8] * 16777216 - $key[7] * 65536 - $key[6] * 256));

	$key[12] = intval($three/16777216);
	$key[11] = intval(($three - $key[12] * 16777216) / 65535);
	$key[10] = intval(($three - $key[12] * 16777216 - $key[11] * 65536) / 256);
	$key[9] = intval(($three - $key[12] * 16777216 - $key[11] * 65536 - $key[10] * 256));

	$key[16] = intval($four/16777216);
	$key[15] = intval(($four - $key[16] * 16777216) / 65535);
	$key[14] = intval(($four - $key[16] * 16777216 - $key[15] * 65536) / 256);
	$key[13] = intval(($four - $key[16] * 16777216 - $key[15] * 65536 - $key[14] * 256));

	$dst[1] = $dst[1] ^ $key[1];

	$i=1;
	while ($i<16){
		$i++;
		$dst[$i] = $dst[$i] ^ $dst[$i-1] ^ $key[$i];
	}

	$i=0;
	while ($i<16){
		$i++;
		if ($dst[$i] == 0) {
			$dst[$i] = 102;
		}
	}

	$encrypt = "0x";
	$i=0;
	while ($i<16){
		$i++;
		if ($dst[$i] < 16) {
			$encrypt = $encrypt . "0" . dechex($dst[$i]);
		} else {
			$encrypt = $encrypt . dechex($dst[$i]);
		}
	}

	return $encrypt;
}
?>