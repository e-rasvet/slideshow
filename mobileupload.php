<?php

    require_once("../../config.php");
    require_once("lib.php");
    require_once("SimpleImage.php");
    
    $id                     = optional_param('id', 0, PARAM_INT);
    $cid                    = optional_param('cid', 0, PARAM_INT);
    $uid                    = optional_param('uid', 0, PARAM_INT);
    $time                   = optional_param('time');
    $type                   = optional_param('type');
    $slideimages            = optional_param('slideimages');
    $fname                  = optional_param('fname');
    
    $data = get_record_sql("SELECT * FROM {$CFG->prefix}slideshow_files WHERE userid={$uid} AND instance={$id} ORDER BY id desc");
    $aid = $data->id;
    
    mkdir("{$CFG->dataroot}/{$cid}", 0777);
    mkdir("{$CFG->dataroot}/{$cid}/presentation", 0777);
    mkdir("{$CFG->dataroot}/{$cid}/presentation/{$aid}", 0777);
    //mkdir("{$CFG->dataroot}/{$cid}/presentation/{$aid}/_tmp", 0777);
    mkdir("{$CFG->dataroot}/{$cid}/presentation/{$aid}/mp3", 0777);
    
    
    if ($type == 'image') {
      $updir = "{$cid}/presentation/{$aid}";
      //foreach ($_FILES as $keytmpname => $valuetmpname) {
      $keytmpname = $fname;
          $file = $CFG->dataroot."/".$updir . "/" . $keytmpname . ".jpg";
          unlink($CFG->dataroot."/".$updir . "/" . $keytmpname . ".jpg");
          unlink($CFG->dataroot."/".$updir . "/s_" . $keytmpname . ".jpg");
          if (move_uploaded_file($_FILES['media']['tmp_name'], $file)) {
              //-------Resize images----------//--500x280
              
              $image = new SimpleImage();
              $image->load($file);
              
              $exif = exif_read_data($file);
              $ort = $exif['Orientation'];
              switch($ort)
              {
                case 3: // 180 rotate left
                    $image->imagerotate(180);
                    break;

                case 6: // 90 rotate right
                    $image->imagerotate(-90);
                    break;

                case 8:    // 90 rotate left
                    $image->imagerotate(90);
                    break;
              }
              
              $image->resizeToHeight(375);
              $image->save($file);
              
              /*
              @$image=imagecreatefromjpeg($file);
              $image = imagerotate($image, -90, 0);
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
              */
          }
      //}
    } else if ($type == 'audio') {
      $updir = "{$cid}/presentation/{$aid}/mp3";
      $keytmpname = $fname;
      //foreach ($_FILES as $keytmpname => $valuetmpname) {
          $file    = $CFG->dataroot."/".$updir . "/" . $keytmpname . ".wav";
          $filemp3 = $CFG->dataroot."/".$updir . "/" . $keytmpname . ".mp3";
          $fileogg = $CFG->dataroot."/".$updir . "/" . $keytmpname . ".ogg";
          unlink($CFG->dataroot."/".$updir . "/" . $keytmpname . ".wav");
          move_uploaded_file($_FILES['media']['tmp_name'], $file);
          
          runExternal("/usr/local/bin/ffmpeg -y -i {$file} -acodec libmp3lame -ab 68k -ar 44100 {$filemp3}", &$code); 
          runExternal("/usr/local/bin/ffmpeg -i {$file} -acodec libvorbis -aq 100  -ac 2 {$fileogg}", &$code);
      //}
    } else if ($type == 'submit') {
        $updir = "{$cid}/presentation/{$aid}";
        $slideimages = explode(":", $slideimages);
        $xmlcontents = '<xml version="1.0" encoding="utf-8">
         <parameter autochange="0" />
         <images>';
        foreach ($slideimages as $valuetmpname) {
          if (!empty($valuetmpname) && $valuetmpname != 'undefined') {
            //list($a1,$i1) = eaplode(":",$valuetmpname);
            $xmlcontents .= '
        <image url="'.$CFG->wwwroot.'/file.php/'.$cid.'/presentation/'.$data->id.'/'.$fname.'_'.$valuetmpname.'.jpg" voice="'.$CFG->wwwroot.'/file.php/'.$cid.'/presentation/'.$aid.'/mp3/'.$fname.'_'.$valuetmpname.'.mp3" mp3="true" />
';
          }
        }
        
        $xmlcontents .= '
        </images>
    </xml>';
    
        $fp = fopen($CFG->dataroot."/".$updir . "/" . $fname . ".xml", "w+");
        fwrite($fp, $xmlcontents);
        fclose($fp);
    }
    


  function runExternal( $cmd, &$code ) {

   $descriptorspec = array(
       0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
       1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
       2 => array("pipe", "w") // stderr is a file to write to
   );

   $pipes= array();
   $process = proc_open($cmd, $descriptorspec, $pipes);

   $output= "";

   if (!is_resource($process)) return false;

   #close child's input imidiately
   fclose($pipes[0]);

   stream_set_blocking($pipes[1],false);
   stream_set_blocking($pipes[2],false);

   $todo= array($pipes[1],$pipes[2]);

   while( true ) {
       $read= array();
       if( !feof($pipes[1]) ) $read[]= $pipes[1];
       if( !feof($pipes[2]) ) $read[]= $pipes[2];

       if (!$read) break;

       $ready= stream_select($read, $write=NULL, $ex= NULL, 2);

       if ($ready === false) {
           break; #should never happen - something died
       }

       foreach ($read as $r) {
           $s= fread($r,1024);
           $output.= $s;
       }
   }

   fclose($pipes[1]);
   fclose($pipes[2]);

   $code= proc_close($process);

   return $output;
  }