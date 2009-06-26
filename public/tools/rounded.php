<?
//  The following script is hereby released under the Modified BSD license (http://www.opensource.org/licenses/bsd-license.php)
//  Copyright 2004 Raefer Gabriel
//
//  Script arguments:
//  Corner Name :              cn (one of: tl, bl, tr, br)
//  Height      :              h (height for elliptical arc)
//  Width       :              w (width for elliptical arc)
//  Size        :              s (alternative parameter for circular arc)
//  Border      :              b (thickness of optional border line)
//  Border color:              cb (ignored unless b!=0)
//  Inner color :              ci
//  Outer color :              co
//
//  Usage:
//  rounded.php?cn=tl&s=10&ci=ffffff&co=000077 (top left 10x10 image, white inside, light blue outside)
//  rounded.php?cn=tr&h=20&w=20&ci=ffffff&co=000077 (top right 10x20 elliptical corner image, white inside, light blue outside)

//  See http://hardgrok.org/blog/item/20/ for more information on use, or see the included examples.

//  This variable controls the caching mechanism.  It's off by default for ease of installation, but I highly
//  recommend using it unless you are just testing things out or doing active development work on this script.
//
//  You will need to create the directory 'cache' under the script install directory (usually your web server
//  root) and make sure the user this script runs as has write access to it in order for caching to work.

$caching = false;
$cachedir = 'cache/';


/**
 * Helper functions
 **/

// hex to rgb color translation
function hex2rgb($hex)
{
    for($i=0; $i<3; $i++)
    {
        $temp = substr($hex,2*$i,2);
        $rgb[$i] = 16 * hexdec(substr($temp,0,1)) + hexdec(substr($temp,1,1));
    }
    return $rgb;
}

//output image from cached file
function outputfromfile($filename)
{
    header("Content-Type: image/gif");
    header("Expires: ".gmdate("D, d M Y H:i:s",time()+24*60*60));
    include($filename);
}

$defaultValues = array(
    'cn' => 'tl',
    's'  => 10,
    'h'  => 10,
    'w'  => 10,
    'b'  => 0,
    'cb' => 0,
    'ci' => '000000',
    'co' => 'ffffff',
);

extract(array_merge($defaultValues, $_GET));

$mangledname = $cn.'_'.$s.'_'.$h.'_'.$w.'_'.$b.'_'.$cb.'_'.$ci.'_'.$co.'.gif';
$cachefile = $cachedir.$mangledname;

/*
//first check to see if we've already generated this file.  If so, just return it
if ($caching && file_exists($cachefile)) {
    outputfromfile($cachefile);
    exit();
}*/

// set default values for size and color
if ( !$s and !$h )
  $s=10;
if ( !$ci )
  $ci="ffffff";
//echo "ci: $ci 1: " . $ci{0} . " co: $co 1: " . $co{0};
if($ci{0} == '#')
    $ci = substr($ci,1);
if ( !$co )
  $co="000000";
if($co{0} == '#')
    $co = substr($co,1);

//pick larger of inner and outer radius
if ($h==0) {
  $h=$w=$s;
} else if ($h>$w) {
  $s = $h;
} else {
  $s = $w;
}

//convert colors to rgb
$rgbinner = hex2rgb($ci);
$rgbouter = hex2rgb($co);
$rgbborder = hex2rgb($cb);

//we want to render the image at twice the specified size then downsample later to antialias
$width = $w*2;
$height = $h*2;
$bthickness = $b*2;

// cn ha de ser uno de estos valores
if( !($cn=="tl" OR $cn=="tr" OR $cn=="bl" OR $cn=="br" OR $cn=="nt") )
    exit();


if ( $cn=="tl" ) {
  $center_x = $width;
  $center_y = $height;
  $arc_start = 180;
  $arc_end = 270;
} else if ( $cn=="tr" ) {
  $center_x = -2;
  $center_y = $height;
  $arc_start = 270;
  $arc_end = 360;
} else if ( $cn=="bl" ) {
  $center_x = $width;
  $center_y = -1;
  $arc_start = 90;
  $arc_end = 0;
} else if ( $cn=="br" ) {
  $center_x = -2;
  $center_y = -1;
  $arc_start = 180;
  $arc_end = 90;
}

$image_scratch = imagecreatetruecolor($width, $height);
//imageantialias function seems broken in PHP4.3.8
//imageantialias($im,TRUE);
$inner_color = imagecolorallocate($image_scratch, $rgbinner[0], $rgbinner[1], $rgbinner[2]);
$outer_color = imagecolorallocate($image_scratch, $rgbouter[0], $rgbouter[1], $rgbouter[2]);
$border_color = imagecolorallocate($image_scratch, $rgbborder[0], $rgbborder[1], $rgbborder[2]);

//fill in background color
imagefill($image_scratch, 0, 0, $outer_color);


//first deal with border case
if ($b!=0) {
  //draw filled arc for border
  imagefilledarc($image_scratch, $center_x, $center_y, $width*2, $height*2, $arc_start, $arc_end, $border_color, IMG_ARC_PIE);
  //draw smaller filled arc for inner region
  imagefilledarc($image_scratch, $center_x, $center_y, $width*2-2*$bthickness+1, $height*2-2*$bthickness+1, $arc_start, $arc_end, $inner_color, IMG_ARC_PIE);
} else {
  //plain, non-bordered corner
  //draw filled arc
  imagefilledarc($image_scratch, $center_x, $center_y, $width*2, $height*2, $arc_start, $arc_end, $inner_color, IMG_ARC_PIE);
}

//resample image down to antialias
$image_dest = imagecreatetruecolor($w, $h);
//why size-1 here?  I'm not exactly sure, but it works
imagecopyresampled($image_dest, $image_scratch, 0, 0, 0, 0, $w, $h, $width-1, $height-1);

// first setting content-type and caching header to 1 day expiration.
// this needs to be done before we do any output, even buffered output
//header("Content-Type: image/gif");
//header("Expires: ".gmdate("D, d M Y H:i:s",time()+24*60*60));
//alternative approach to control caching, doesn't seem to be necessary though
//header("Cache-Control : max-age = ".str(24*60*60));
//header("Content-Disposition: inline; filename=".$cn.$h.$w.$ci.$co.".gif");


//first, save a copy of the generated image if caching mode is set
if ($caching) {
    // Start buffering the output
    ob_start();

    // Now output the image
    imagegif($image_dest);

    // Fetch output buffer
    $buffer = ob_get_contents();

    // Stop buffering and display the buffer
    ob_end_flush();

    // Write a cache file from the contents
    $fp = fopen($cachefile, 'wb');
    fwrite($fp, $buffer);
    fclose($fp);
}

// Enviamos el content type apropiado
header ("Content-type: image/gif");

// Le ponemos un nombre
header('Content-Disposition: inline; filename="'.$cn.'.gif"');


// Copiamos la imagen
imagegif($image_dest);


//can also output jpegs if you prefer, but I don't recommend it (quality is much worse)
//imagejpeg($image_dest,"",95);

//clean up images
imagedestroy($image_scratch);
imagedestroy($image_dest);

//return "$destino/$cn.gif";
?>
