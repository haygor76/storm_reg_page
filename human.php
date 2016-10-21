<?php
session_start();

// generate a random string
// get a string of 3 letters
//$rand = str_shuffle(str_shuffle(str_shuffle(ranstring(rand(2,3),4))));
// get two random number and put them in the string
//$rand .= rand(0,99);

// get a string with a length of 3 to 5 characters long
$rand = RandomCode(3,4);

// randomize the string
$rand = str_shuffle(str_shuffle(str_shuffle($rand)));

// create the hash for the random number and put it in the session
$_SESSION['image_random_value'] = strtolower($rand);

// Store the output file name
$output = "human";

// pick a random background image
//$bgNum = rand(1,25);
$bgNumA = array (0 => '1', '4', '7', '7', '7', '12', '14', '16', '17');

$bgNum = $bgNumA[rand(0,count($bgNumA) - 1)];
$bgImage = "capbg/capbg".$bgNum.".jpg";

// append bg number to output name
$output .= "_".$bgNum."_";

// Create an image object using the chosen background
$image = imagecreatefromjpeg($bgImage);


// split the sting up for output later
$string = str_split($rand, 1);

// use a for loop to go through each letter in the string
for ($i=0; $i<(strlen($rand));$i++)
{
	// Get a random font size
	$rand_size = rand(12,15);

	// select random text color values
	$red = rand(0,255);
	$gre = rand(0,1);
	$blu = rand(0,100);

	// modify the colors that where picked
	$r = $red;
	$g = $gre;
	$b = $blu;
	$r = abs($red - $gre);
	$g = abs($gre - $blu);
	$b = abs($blu - $red);

	// set the character number
	$textColor = imagecolorallocate ($image, $r, $g, $b);

	if ($i % rand(1, strlen($rand)) == 0)
	{
		$textColor = $textColor * -1;
	}

	// check if it is the first char for the spacing of the letters
	if ($i == 0)
	{
		$x = 5;
	}
	else
	{
		$x = ($rand_size * $i) + rand(1, 5);
	}
	// set the height of the letter
	$y = rand(15, 20);
	
	// Pick a random number for the angle and font
	$odd = rand(1,31);
	// set the angle depending on what the random number
	if ($odd % 2 == 0)
	{
		$angle = rand(rand(0,35), 35);
	}
	else
	{
		$angle = rand(0,15) * -1;
	}
	// actually add the character to the image

//echo $image."<br />";
//echo $rand_size."<br />";
//echo $angle."<br />";
//echo $x."<br />";
//echo $y."<br />";
//echo $textColor."<br />";
//echo "capfonts/".$odd.".ttf"."<br />";
//echo $string[$i]."<br /><br />";

	imagettftext ($image, $rand_size, $angle, $x, $y, $textColor, "capfonts/".$odd.".ttf", $string[$i]);
}
$ranNum = rand(1, 1100);
//echo $ranNum."<br /><br />";
$filter = false;
$image2 = imagecreatetruecolor(imagesx($image), imagesy($image));
imagecopy($image2, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
if ($ranNum <= 100)
{
	$filter = imagefilter($image2, IMG_FILTER_NEGATE);
}
else if ($ranNum < 100 && $ranNum >= 200)
{
	$filter = imagefilter($image2, IMG_FILTER_GRAYSCALE);
}
else if ($ranNum < 200 && $ranNum >= 300)
{
	$filter = imagefilter($image2, IMG_FILTER_BRIGHTNESS, 50);
}
else if ($ranNum < 300 && $ranNum >= 400)
{
	$filter = imagefilter($image2, IMG_FILTER_CONTRAST, -25);
}
else if ($ranNum < 400 && $ranNum >= 500)
{
	$filter = imagefilter($image2, IMG_FILTER_COLORIZE, rand(0,255), rand(0,255), rand(0,255));
}
else if ($ranNum < 500 && $ranNum >= 600)
{
	$filter = imagefilter($image2, IMG_FILTER_EDGEDETECT);
}
else if ($ranNum < 600 && $ranNum >= 700)
{
	$filter = imagefilter($image2, IMG_FILTER_EMBOSS);
}
else if ($ranNum < 700 && $ranNum >= 800)
{
	$filter = imagefilter($image2, IMG_FILTER_GAUSSIAN_BLUR);
}
else if ($ranNum < 800 && $ranNum >= 900)
{
	$filter = imagefilter($image2, IMG_FILTER_SELECTIVE_BLUR);
}
else if ($ranNum < 900 && $ranNum >= 1000)
{
	$filter = imagefilter($image2, IMG_FILTER_MEAN_REMOVAL);
}
else if ($ranNum < 1000 && $ranNum >= 1100)
{
	$filter = imagefilter($image2, IMG_FILTER_SMOOTH, 50);
}

// Append the filter number to output file
$output .= $ranNum."_";

// Find a image type that is supported
if (function_exists("imagepng")) 
{
	$imgHeader = "Content-type: image/png";
	$output = $output.$rand.".png";
	if ($filter === true)
	{
		imagepng($image2, $output);
	}
	else
	{
		imagepng($image, $output);
	}
}
elseif (function_exists("imagegif")) 
{
	$imgHeader = "Content-type: image/gif";
	$output = $output.$rand.".gif";
	imagegif($image, $output);
}
elseif (function_exists("imagejpeg")) 
{
	$imgHeader = "Content-type: image/jpeg";
	$output = $output.$rand.".jpg";
	imagejpeg($image, $output, 0.5);
}
elseif (function_exists("imagewbmp")) 
{
	$imgHeader = "Content-type: image/vnd.wap.wbmp";
	$output = $output.$rand.".bmp";
	imagewbmp($image, $output);
}
else 
{
    die("No image support in this PHP server");
}

// destroy the image to free up the memory
imagedestroy($image);


// Open a file pointer for the image for reading
$fp = fopen($output, "r");

// Read file pointer contents to memory
$contents = fread($fp, filesize($output));

// Close the file pointer
fclose($fp);

// Delete the file now that it has been read into memory
unlink($output);

// send several headers to make sure the image is not cached
// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

// HTTP/1.0
header("Pragma: no-cache");

// send the content type header so the image is displayed properly
header($imgHeader);

// Stream out the file
echo $contents;


function ranstring ($length, $charset)
{
	/********************************************************************************
	* Character Sets								*
	* 1 - only numbers								*
	* 2 - lowercase letters								*
	* 3 - uppercase letters								*
	* 4 - lowercase and uppercase letters						*
	* 5 - lowercase and uppercase letters with numbers				*
	* 6 - all possible keyboard buttons						*
	* 7 - lowercase letters and numbers						*
	********************************************************************************/
	
	$string = "";

	switch($charset)
	{
		case 1:
			// only numbers (0 - 9)
			for($i=0; $i<$length; $i++)
			{
				$ranchar = rand(0, 9);
				$string .= $ranchar;		
			}
			break;
		case 2:
			// lowercase letters (a - z)
			for($i=0; $i<$length; $i++)
			{
				$ranchar = rand(97, 122);
				$ranchar = chr($ranchar);
				$string .= $ranchar;		
			}
			break;
		case 3:
			// uppercase letters (A - Z)
			for($i=0; $i<$length; $i++)
			{
				$ranchar = rand(65, 90);
				$ranchar = chr($ranchar);					
				$string .= $ranchar;
			}
			break;
		case 4:
			// lowercase (a - z) and uppercase (A - Z) letters
			for($i=0; $i<$length; $i++)
			{
				$ranchar = rand(1, 52);
				if($ranchar>=1&&$ranchar<=26)
				{
					$ranchar = $ranchar + 64;
					$ranchar = chr($ranchar);
				}
				else
				{
					$ranchar = $ranchar + 70;
					$ranchar = chr($ranchar);
				}					
				$string .= $ranchar;
			}			
			break;
		case 5:
			// lowercase (a - z) and uppercase (A - Z) letters with numbers (0 - 9)
			for($i=0; $i<$length; $i++)
			{
				$ranchar = rand(1, 62);
				if($ranchar>=1&&$ranchar<=10)
				{
					$ranchar = $ranchar + 47;
					$ranchar = chr($ranchar);
				}
				elseif($ranchar>=11&&$ranchar<=36)
				{
					$ranchar = $ranchar + 54;
					$ranchar = chr($ranchar);
				}				
				else
				{
					$ranchar = $ranchar + 60;
					$ranchar = chr($ranchar);
				}
				$string .= $ranchar;
			}
			break;
		case 6:
			// all possible keyboard buttons excluding space, ", ', `
			for($i=0; $i<$length; $i++)
			{
				$ranchar = rand(1, 91);
				if($ranchar==1)
				{
					$ranchar = $ranchar + 32;
					$ranchar = chr($ranchar);				
				}
				elseif($ranchar>=2&&$ranchar<=5)
				{
					$ranchar = $ranchar + 33;
					$ranchar = chr($ranchar);
				}		
				elseif($ranchar>=6&&$ranchar<=61)
				{
					$ranchar = $ranchar + 34;
					$ranchar = chr($ranchar);
				}					
				else
				{
					$ranchar = $ranchar + 35;
					$ranchar = chr($ranchar);
				}
				$string .= $ranchar;
			}
			break;
		case 7:
			// only numbers (0 - 9)
			for($i=0; $i<(($length / 2) - 1); $i++)
			{
				$ranchar = rand(0, 9);
				$string .= $ranchar;
			}		
			// lowercase letters (a - z)
			for($j=0; $j<(($i * 2) - 1); $j++)
			{
				$ranchar = rand(97, 122);
				$ranchar = chr($ranchar);
				$string .= $ranchar;		
			}
			break;

	}

	return str_shuffle(str_shuffle(str_shuffle($string)));	
} 

function RandomCode($min,$max) // Chose the turing code
{
// Choosing a random Security Code
$src = 'abcdefghijkmnpqrstuvwxyz';    /* no l, o */
$src .= 'ABCDEFGHIJKMNPQRSTUVWXYZ';    /* no L, O */
$src .= '23456789';            /* no 1, 0 */

$srclen = strlen($src)-1;

// Chose the length of the turing code
$length = mt_rand($min,$max); 

$code = '';

// Fill the turing string with characters and numbers from $src
for($i=0; $i<$length; $i++)
{ 
	$code .= substr($src, mt_rand(0, $srclen), 1);
}
return $code;

}
?>