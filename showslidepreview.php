<?php

require_once('../../config.php');

$courseid  = optional_param('courseid', 0, PARAM_INT);

$updir = "{$courseid}/presentation/temp";

$imagepath = $CFG->dataroot."/".$updir . "/s_" . $_GET['id'] . ".jpg";


function LoadJpeg($imgname)
{
    /* Attempt to open */
    $im = @imagecreatefromjpeg($imgname);

    /* See if it failed */
    if(!$im)
    {
        /* Create a black image */
        $im  = imagecreatetruecolor(150, 30);
        $bgc = imagecolorallocate($im, 255, 255, 255);
        $tc  = imagecolorallocate($im, 0, 0, 0);

        imagefilledrectangle($im, 0, 0, 150, 30, $bgc);

        /* Output an error message */
        imagestring($im, 1, 5, 5, 'Error loading ' . $imgname, $tc);
    }

    return $im;
}

header('Content-Type: image/jpeg');

$img = LoadJpeg($imagepath);

imagejpeg($img);
imagedestroy($img);

?>