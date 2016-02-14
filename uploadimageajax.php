<?php

require_once('../../config.php');

$courseid  = optional_param('courseid', 0, PARAM_INT);

$updir = "{$courseid}/presentation/temp";

if (!file_exists ($CFG->dataroot."/".$updir)) {
    make_upload_directory($updir);
}

foreach ($_FILES as $keytmpname => $valuetmpname) {
    $file = $CFG->dataroot."/".$updir . "/" . $keytmpname . ".jpg";
    unlink($CFG->dataroot."/".$updir . "/" . $keytmpname . ".jpg");
    unlink($CFG->dataroot."/".$updir . "/s_" . $keytmpname . ".jpg");
    if (move_uploaded_file($valuetmpname['tmp_name'], $file)) {
        //-------Resize images----------//--500x280
        @$image=imagecreatefromjpeg($file);
        $w=getimagesize($file);
                    
        $width = 500;
        $height = 280;
                    
        if ($w[0] > 280 || $w[1] > 500) {
            if ($w[0]>$w[1])
            {
                $height2=round(($width/$w[0])*$w[1]);

                $image2=imagecreatetruecolor($width, $height2);
                if(!function_exists("imagecopyresampled"))
                {
                    imagecopyresized($image2, $image, 0, 0, 0, 0, $width, $height2, $w[0], $w[1]);
                }else
                {
                    imagecopyresampled($image2, $image, 0, 0, 0, 0, $width, $height2, $w[0], $w[1]);
                }
                imagejpeg($image2, $file, 90);
                imagedestroy($image);
                imagedestroy($image2);
            } else {
                $width2=round(($height/$w[1])*$w[0]);
        
                $image2=imagecreatetruecolor($width2, $height);
                if(!function_exists("imagecopyresampled"))
                {
                    imagecopyresized($image2, $image, 0, 0, 0, 0, $width2, $height, $w[0], $w[1]);
                }else
                {
                    imagecopyresampled($image2, $image, 0, 0, 0, 0, $width2, $height, $w[0], $w[1]);
                }
                imagejpeg($image2, $file, 90);
                imagedestroy($image);
                imagedestroy($image2);
            }
        }
        
        
        @$image=imagecreatefromjpeg($file);
        $w=getimagesize($file);
        
        $width = 200;
        $height = 150;
        $fileold = $file;
        $file = $CFG->dataroot."/".$updir . "/s_" . $keytmpname . ".jpg";
        
        if ($w[0] > 150 || $w[1] > 200) {
            if ($w[0]>$w[1])
            {
                $height2=round(($width/$w[0])*$w[1]);

                $image2=imagecreatetruecolor($width, $height2);
                if(!function_exists("imagecopyresampled"))
                {
                    imagecopyresized($image2, $image, 0, 0, 0, 0, $width, $height2, $w[0], $w[1]);
                }else
                {
                    imagecopyresampled($image2, $image, 0, 0, 0, 0, $width, $height2, $w[0], $w[1]);
                }
                imagejpeg($image2, $file, 90);
                imagedestroy($image);
                imagedestroy($image2);
            } else {
                $width2=round(($height/$w[1])*$w[0]);
        
                $image2=imagecreatetruecolor($width2, $height);
                if(!function_exists("imagecopyresampled"))
                {
                    imagecopyresized($image2, $image, 0, 0, 0, 0, $width2, $height, $w[0], $w[1]);
                }else
                {
                    imagecopyresampled($image2, $image, 0, 0, 0, 0, $width2, $height, $w[0], $w[1]);
                }
                imagejpeg($image2, $file, 90);
                imagedestroy($image);
                imagedestroy($image2);
            }
        }
        else
        {
          copy($fileold, $file);
        }
    }
}

?>ok