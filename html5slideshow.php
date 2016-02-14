<?php include_once "../../config.php"; ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
         "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Slideshow</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script type="application/x-javascript" src="js/jquery.min.js"></script>
</head>

<body><?php

$xml_url = optional_param('xml_url');


$xmlfile = file_get_contents($CFG->dataroot.$xml_url);


$data = explode("<image ", $xmlfile);
$slide = array();

while(list($key,$value)=each($data)) {
  if ($key) {
    if (!strstr($value, 'mp3="true"')) 
      $slide[$key]['duration'] = 3000;
      
    if (!$slide[$key]['duration'])
      if (preg_match("!voice=\"(.*?)\"!si",$value,$voice)) 
        $slide[$key]['voice'] = $voice[1]; 
    
    if (preg_match("!url=\"(.*?)\"!si",$value,$url)) 
        $slide[$key]['url'] = $url[1]; 
        
    $lastkey = $key;
  }
}

?>
<script type="application/x-javascript">
$(document).ready(function(){
  var slideid     = 1;
  var reslideid   = 1;
  var pause       = 0;
  var playmark    = 0;
  window.pause    = pause;
  window.playmark = playmark;
  $('#nav-play').click(function() {
    //console.log(window.playmark +"/" + window.pause + "/" + window.reslideid);
    if (window.playmark == 1 && window.pause == 0) {
      window.pause = 1;
      document.getElementById("audio-"+window.reslideid).pause(); 
      $('#nav-play-btn').attr("src", "html5-play.png");
    } else if (window.playmark == 1 && window.pause == 1) {
      window.pause = 0;
      document.getElementById("audio-"+window.reslideid).play(); 
      $('#nav-play-btn').attr("src", "html5-pause.png");
    } else {
      showslide(slideid);
    }
  });
  
  $('#nav-rtl').click(function() {
    //console.log(window.reslideid);
    document.getElementById("audio-"+window.reslideid).pause();
    showslide(window.reslideid - 1);
  });
  
  $('#nav-ltr').click(function() {
    //console.log(window.reslideid);
    document.getElementById("audio-"+window.reslideid).pause();
    showslide(window.reslideid + 1);
  });
  
function showslide(slideid) {
  var imagesarray=new Array(); 
<?php 
while(list($key,$value)=each($slide)) {
  echo 'imagesarray['.$key.'] = "'.$value['url'].'";
';
}
reset($slide); ?>
  if (slideid != <?php echo $lastkey + 1; ?>) {
    if (slideid == 1) $('#nav-rtl-btn').attr("src", "html5-empty.png"); else $('#nav-rtl-btn').attr("src", "html5-rtl.png");
    if (slideid == <?php echo $lastkey; ?>) $('#nav-ltr-btn').attr("src", "html5-empty.png"); else $('#nav-ltr-btn').attr("src", "html5-ltr.png");
    $('#slidenumber').html(slideid);
  
    $('#imagecanvas').attr("src", imagesarray[slideid]);
    var audio = document.getElementById("audio-"+slideid);
    audio.load();
    audio.play(); 
    window.playmark = 1;
    window.reslideid = slideid;
    $('#nav-play-btn').attr("src", "html5-pause.png");
    audio.addEventListener("ended", function() { 
      showslide(slideid + 1);
    }, true);
  } else {
    window.playmark = 0;
    slideid = 1;
    $('#nav-play-btn').attr("src", "html5-play.png");
  }
}

  $('.image-slide').click(function() {
    showslide(window.reslideid);
  });
  
  
<?php 
while(list($key,$value)=each($slide)) {
?>
    document.getElementById("audio-<?php echo $key; ?>").addEventListener("loadstart", function() { 
      $('#audio-status').html('<img src="html5-loader.gif" />');
    }, true);
    document.getElementById("audio-<?php echo $key; ?>").addEventListener("loadeddata", function() { 
      $('#audio-status').html('');
    }, true);
    document.getElementById("audio-<?php echo $key; ?>").addEventListener("playing", function() { 
      $('#audio-status').html('<img src="html5-playing.png" />');
    }, true);
    document.getElementById("audio-<?php echo $key; ?>").addEventListener("ended", function() { 
      $('#audio-status').html('');
    }, true);
<?php
}
reset($slide); ?>
});

(function($) {
  var cache = [];
  $.preLoadImages = function() {
    var args_len = arguments.length;
    for (var i = args_len; i--;) {
      var cacheImage = document.createElement('img');
      cacheImage.src = arguments[i];
      cache.push(cacheImage);
    }
  }
})(jQuery)

jQuery.preLoadImages(<?php 
$urltext = "";
while(list($key,$value)=each($slide)) {
  $urltext .= '"'.$value['url'].'",';
}
$urltext = substr($urltext, 0, -1);
echo $urltext;
reset($slide); ?>, "html5-play.png", "html5-pause.png", "html5-empty.png", "html5-rtl.png", "html5-ltr.png", "html5-playing.png", "html5-loader.gif");
</script>

</head>
<body>
<div id="content" style="height:375px;">
<img src="<?php echo $slide[1]['url']; ?>" id="imagecanvas" class="image-slide"/>
</div>
<div style="width:490px;height:40px;background:#eee;padding:4px;border:1px solid #bbb;">
<div style="padding:0 180px;">
<div style="float:left;"><a href="#" id="nav-rtl" /><img src="html5-empty.png" width="36px" height="36px" id="nav-rtl-btn" style="padding:0;border:0"/></a></div>
<div style="float:left;"><a href="#" id="nav-play" /><img src="html5-play.png" width="36px" height="36px" id="nav-play-btn" style="padding:0;border:0"/></a></div>
<div style="float:left;"><a href="#" id="nav-ltr" /><img src="html5-empty.png" width="36px" height="36px" id="nav-ltr-btn" style="padding:0;border:0"/></a></div>
</div>
<div style="position:absolute;left:420px;color:#666;font-size:12px;">Slide: <span id="slidenumber">1</span> of <?php echo $lastkey; ?></div>

<div id="audio-status" style="position:absolute;left:20px;color:#666;font-size:12px;">Loading</div>

</div>

<?php

while(list($key,$value)=each($slide)) {
  if (!$value['duration'])
    if (!strstr($_SERVER['HTTP_USER_AGENT'], "Firefox"))
      echo '<div><audio src="'.$value['voice'].'" id="audio-'.$key.'" autobuffer="autobuffer" preload="auto"></audio></div>';
    else
      echo '<div><audio src="'.str_replace($CFG->wwwroot."/file.php", "html5getfile.php?audio=", str_replace(".mp3", ".ogg", $value['voice'])).'" id="audio-'.$key.'" autobuffer="autobuffer" preload="auto"></audio></div>';
}

?>

</body>
</html>