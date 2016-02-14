<?php  // $Id: edit.php,v 1.4 2010/07/09 16:41:20 Igor Nikulin Exp $

    require_once("../../config.php");
    require_once("lib.php");
    require_once($CFG->dirroot.'/course/moodleform_mod.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->libdir.'/uploadlib.php');
    require_once($CFG->libdir.'/gradelib.php');

    $id           = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    $a            = optional_param('a', 'edit');  // slideshow ID
    $name         = optional_param('name');  // slideshow ID
    $summary      = optional_param('summary');  // slideshow ID
    $slides       = optional_param('slides');  // slideshow ID
    $slideimages  = optional_param('slideimages');
    $idrec        = optional_param('idrec');
    $mobileslide  = optional_param('mobileslide');

    if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
    
        if (! $slideshow = get_record("slideshow", "id", $cm->instance)) {
            error("Course module is incorrect");
        }

    } else {
        if (! $slideshow = get_record("slideshow", "id", $a)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $slideshow->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("slideshow", $slideshow->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }

    require_login($course->id);

    add_to_log($course->id, "slideshow", "edit", "edit.php?id=$cm->id", "$slideshow->id");
    
    //---------Edit record-----------//
    if ($name && $idrec) {
        set_field("slideshow_files", "text", $summary, "id", $idrec);
        set_field("slideshow_files", "name", $name, "id", $idrec);
    
        if ($_FILES['attachment']['tmp_name']) {
            $updir    = $CFG->dataroot."/{$course->id}/presentation/{$idrec}";
            move_uploaded_file ($_FILES['attachment']['tmp_name'], $updir."/".$_FILE['attachment']['tmp_name']);
        }
        
        redirect($CFG->wwwroot.'/mod/slideshow/view.php?id='.$id, "Done");
    }

    //---------Add record-----------//
    if ($name) {
        $usergroups  = slideshow_groups_get_user_groups($USER->id);
        //####edited JUSTIN , error if no user groups 20110514
		//$groupid     = $usergroups[0][0];
		if($usergroups && count($usergroups) > 0 && count($usergroups[0])>0){
			$groupid     = $usergroups[0][0];
		}else{
			$groupid = -1;
		}
        //####end of edit
		
        $data = new object;
        $data->name                  = $name;
        $data->text                  = $summary;
        $data->userid                = $USER->id;
        $data->instance              = $id;
        $data->timemodified          = time();
		
		//#############edited Justin, error if no user groups 20110514
		//if ($groupid) $data->groupid = $groupid;
        if ($groupid > -1) $data->groupid = $groupid;
		//###################3
        
        $idrec = insert_record('slideshow_files', $data);
        
        if ($slides) {
            $siteid = explode ("/", $CFG->wwwroot);
            $siteid = str_replace (".", "_", $siteid[2]);
    
            $updirtemp = $CFG->dataroot."/{$course->id}/presentation/temp";
            $updir    = $CFG->dataroot."/{$course->id}/presentation/{$idrec}";
            
            if (!is_dir($updir)) {
                @mkdir ($updir, 0777);
            }
            
            if (!is_dir($updir.'/mp3')) {
                @mkdir ($updir.'/mp3', 0777);
            }
            
			//###########Edit Justin 20110514 removed hard coded RTMP server address
            //$xmlcontents = '<xml version="1.0" encoding="utf-8">
			// <parameter imageFolder="rtmp://kochi-tech.net/blog/'.$siteid.'/'.$USER->id.'" autochange="0" />
			//<images>';
	 $xmlcontents = '<xml version="1.0" encoding="utf-8">
    <parameter imageFolder="rtmp://' . $CFG->fms .'/' .$siteid . '/'. $USER->id . '" autochange="0" />
    <images>';
		//###############End of Edit 20110514
    
            foreach ($slideimages as $keytmpname => $valuetmpname) {
              if (strstr($keytmpname, "attachment_slideup_")) {
                  $idofslide = str_replace("attachment_slideup_", "", $keytmpname);
                  $file = $updir . "/" . $idofslide . ".jpg";
                  $imagepath = $updirtemp . "/" . $keytmpname . ".jpg";
                  if (copy($imagepath, $file)) {
                      $xmlcontents .= '
        <image url="'.$CFG->wwwroot.'/file.php/'.$course->id.'/presentation/'.$idrec.'/'. $idofslide . '.jpg" ';
                  }
                  $xmlname1 = explode ("_", $idofslide);
                  $xmlname = $xmlname1[0];
                  foreach ($_POST['usevoice'] as $key =>$value) {
                      $wavprefixname = $key;
                  }
                
                  $wavslideid = explode("_", $idofslide);
                  $wavslideid = $wavslideid[1];

                  slideshow_runExternal("/usr/local/bin/ffmpeg -i {$updirtemp}/mp3/{$wavprefixname}_{$wavslideid}.wav -ar 22050 -ab 64k -acodec libmp3lame {$updir}/mp3/{$wavprefixname}_{$wavslideid}.mp3", &$code);
                  slideshow_runExternal("/usr/local/bin/ffmpeg -i {$updirtemp}/mp3/{$wavprefixname}_{$wavslideid}.wav  -acodec libvorbis -aq 100  -ac 2 {$updir}/mp3/{$wavprefixname}_{$wavslideid}.ogg", &$code);
                  

                  if (is_file("{$updirtemp}/mp3/{$wavprefixname}_{$wavslideid}.wav")) {
                      $xmlcontents .= 'voice="'.$CFG->wwwroot.'/file.php/'.$course->id.'/presentation/'.$idrec.'/mp3/'.$wavprefixname.'_'.$wavslideid.'.mp3" mp3="true" />';
                  }
                  else
                  {
                      $xmlcontents .= 'voice="" duration="4" />';
                  }
              }
            }
            
            $xmlcontents .= '
    </images>
</xml>';

            $fp = fopen($updir . "/" . $xmlname . ".xml", "w+");
            fwrite($fp, $xmlcontents);
            fclose($fp);
        
            $vstavkav = '{FMS:SLIDESHOW='.$CFG->wwwroot.'/mod/slideshow/swfslideplayer.swf?xml_url='.$CFG->wwwroot.'/file.php/'.$course->id.'/presentation/'.$idrec.'/'.$xmlname . '.xml}';
        
            $data->text = $vstavkav."<br />".$data->text;
        
            set_field ("slideshow_files", "text", $data->text, "id", $idrec);
        }
        
        if ($_FILES['attachment']['tmp_name']) {
            move_uploaded_file ($_FILES['attachment']['tmp_name'], $updir."/".$_FILE['attachment']['tmp_name']);
        }
        
        if ($mobileslide) {
            $vstavkav = '{FMS:SLIDESHOW='.$CFG->wwwroot.'/mod/slideshow/swfslideplayer.swf?xml_url='.$CFG->wwwroot.'/file.php/'.$course->id.'/presentation/'.$idrec.'/'.$mobileslide . '.xml}';
        
            $data->text = $vstavkav."<br />".$data->text;
        
            set_field ("slideshow_files", "text", $data->text, "id", $idrec);
        }
        
        redirect($CFG->wwwroot.'/mod/slideshow/view.php?id='.$id, "Done");
    }
    //------------------------------//

/// Print the page header

    if ($course->category) {
        $navigation = "<a href=\"../../course/edit.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }

    $strslideshows = get_string("modulenameplural", "slideshow");
    $strslideshow  = get_string("modulename", "slideshow");

    print_header("$course->shortname: $slideshow->name", "$course->fullname",
                 "$navigation <a href=index.php?id=$course->id>$strslideshows</a> -> $slideshow->name", 
                  "", "", true, update_module_button($cm->id, $course->id, $strslideshow), 
                  navmenu($course, $cm));

/// Print the main part of the page


    include('tabs.php');
    
    
    class mod_slideshow_add_form extends moodleform {

      function definition() {

        global $COURSE, $CFG, $quizs, $USER, $id;
        
        $slides   = optional_param('slides');
        $fmstime  = optional_param('fmstime', time()); 

        $mform    =& $this->_form;
        
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('slideshow_name_slide', 'slideshow'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('htmleditor', 'summary', get_string('slideshow_introduction_slide', 'slideshow'));
        
        $mform->addElement('file', 'attachment', get_string('attachment', 'forum'));
        
        if (!$slides) {
          $mform->addElement('header', 'slideshow', 'Slide Show');
            
          if (strstr($_SERVER['HTTP_USER_AGENT'], "iPhone") || strstr($_SERVER['HTTP_USER_AGENT'], "iPad")) {
            $mediadata = '<h3 style="padding: 0 20px;"><a href="slideshow://?link='.$CFG->wwwroot.'&id='.$id.'&uid='.$USER->id.'&cid='.$COURSE->id.'&time='.$fmstime.'&type=slideshow" onclick="document.getElementById(\'mform1\').submit();">Add slides</a></h3>'; //onclick="document.getElementById(\'mform1\').submit();"
            
            $mform->addElement('static', 'description', '', $mediadata);
            $mform->addElement('hidden', 'mobileslide', $fmstime);
            
          } else {

            $mediadata = '
            <style type="text/css">
            .showarrow.hover {text-decoration:underline;color: #C7D92C;}
            </style>
            <script type="text/javascript" src="js/jquery.min.js"></script>
            <script type="text/javascript" src="js/ajaxupload.js"></script>
            <script language="JavaScript">
function activateajaximagesuploading() {
    new AjaxUpload(\'attachment_upload_'.$fmstime.'_1\', {
      action: \'uploadimageajax.php\',
      data: {
        \'courseid\' : "'.$COURSE->id.'"
      },
      name: \'attachment_slideup_'.$fmstime.'_1\',
      autoSubmit: true,
      responseType: false,
      onSubmit: function(file, extension) {
        jQuery(\'#slide_preview_1\').html(\'<img src="img/ajax-loader.gif" alt="loadeing"/>\');
      },
      onComplete: function(file, response) {
        var randomnumber=Math.floor(Math.random()*1100);
        jQuery(\'#slide_preview_1\').html(\'<img src="showslidepreview.php?courseid='.$COURSE->id.'&id=attachment_slideup_'.$fmstime.'_1&random=\' +randomnumber+ \'" alt=" "/>\');
      }
    });
    new AjaxUpload(\'attachment_upload_'.$fmstime.'_2\', {
      action: \'uploadimageajax.php\',
      data: {
        \'courseid\' : "'.$COURSE->id.'"
      },
      name: \'attachment_slideup_'.$fmstime.'_2\',
      autoSubmit: true,
      responseType: false,
      onSubmit: function(file, extension) {
        jQuery(\'#slide_preview_2\').html(\'<img src="img/ajax-loader.gif" alt="loadeing"/>\');
      },
      onComplete: function(file, response) {
        var randomnumber=Math.floor(Math.random()*1100);
        jQuery(\'#slide_preview_2\').html(\'<img src="showslidepreview.php?courseid='.$COURSE->id.'&id=attachment_slideup_'.$fmstime.'_2&random=\' +randomnumber+ \'" alt=" "/>\');
      }
    });
    new AjaxUpload(\'attachment_upload_'.$fmstime.'_3\', {
      action: \'uploadimageajax.php\',
      data: {
        \'courseid\' : "'.$COURSE->id.'"
      },
      name: \'attachment_slideup_'.$fmstime.'_3\',
      autoSubmit: true,
      responseType: false,
      onSubmit: function(file, extension) {
        jQuery(\'#slide_preview_3\').html(\'<img src="img/ajax-loader.gif" alt="loadeing"/>\');
      },
      onComplete: function(file, response) {
        var randomnumber=Math.floor(Math.random()*1100);
        jQuery(\'#slide_preview_3\').html(\'<img src="showslidepreview.php?courseid='.$COURSE->id.'&id=attachment_slideup_'.$fmstime.'_3&random=\' +randomnumber+ \'" alt=" "/>\');
      }
    });
    new AjaxUpload(\'attachment_upload_'.$fmstime.'_4\', {
      action: \'uploadimageajax.php\',
      data: {
        \'courseid\' : "'.$COURSE->id.'"
      },
      name: \'attachment_slideup_'.$fmstime.'_4\',
      autoSubmit: true,
      responseType: false,
      onSubmit: function(file, extension) {
        jQuery(\'#slide_preview_4\').html(\'<img src="img/ajax-loader.gif" alt="loadeing"/>\');
      },
      onComplete: function(file, response) {
        var randomnumber=Math.floor(Math.random()*1100);
        jQuery(\'#slide_preview_4\').html(\'<img src="showslidepreview.php?courseid='.$COURSE->id.'&id=attachment_slideup_'.$fmstime.'_4&random=\' +randomnumber+ \'" alt=" "/>\');
      }
    });
    new AjaxUpload(\'attachment_upload_'.$fmstime.'_5\', {
      action: \'uploadimageajax.php\',
      data: {
        \'courseid\' : "'.$COURSE->id.'"
      },
      name: \'attachment_slideup_'.$fmstime.'_5\',
      autoSubmit: true,
      responseType: false,
      onSubmit: function(file, extension) {
        jQuery(\'#slide_preview_5\').html(\'<img src="img/ajax-loader.gif" alt="loadeing"/>\');
      },
      onComplete: function(file, response) {
        var randomnumber=Math.floor(Math.random()*1100);
        jQuery(\'#slide_preview_5\').html(\'<img src="showslidepreview.php?courseid='.$COURSE->id.'&id=attachment_slideup_'.$fmstime.'_5&random=\' +randomnumber+ \'" alt=" "/>\');
      }
    });
    new AjaxUpload(\'attachment_upload_'.$fmstime.'_6\', {
      action: \'uploadimageajax.php\',
      data: {
        \'courseid\' : "'.$COURSE->id.'"
      },
      name: \'attachment_slideup_'.$fmstime.'_6\',
      autoSubmit: true,
      responseType: false,
      onSubmit: function(file, extension) {
        jQuery(\'#slide_preview_6\').html(\'<img src="img/ajax-loader.gif" alt="loadeing"/>\');
      },
      onComplete: function(file, response) {
        var randomnumber=Math.floor(Math.random()*1100);
        jQuery(\'#slide_preview_6\').html(\'<img src="showslidepreview.php?courseid='.$COURSE->id.'&id=attachment_slideup_'.$fmstime.'_6&random=\' +randomnumber+ \'" alt=" "/>\');
      }
    });
    new AjaxUpload(\'attachment_upload_'.$fmstime.'_7\', {
      action: \'uploadimageajax.php\',
      data: {
        \'courseid\' : "'.$COURSE->id.'"
      },
      name: \'attachment_slideup_'.$fmstime.'_7\',
      autoSubmit: true,
      responseType: false,
      onSubmit: function(file, extension) {
        jQuery(\'#slide_preview_7\').html(\'<img src="img/ajax-loader.gif" alt="loadeing"/>\');
      },
      onComplete: function(file, response) {
        var randomnumber=Math.floor(Math.random()*1100);
        jQuery(\'#slide_preview_7\').html(\'<img src="showslidepreview.php?courseid='.$COURSE->id.'&id=attachment_slideup_'.$fmstime.'_7&random=\' +randomnumber+ \'" alt=" "/>\');
      }
    });
    new AjaxUpload(\'attachment_upload_'.$fmstime.'_8\', {
      action: \'uploadimageajax.php\',
      data: {
        \'courseid\' : "'.$COURSE->id.'"
      },
      name: \'attachment_slideup_'.$fmstime.'_8\',
      autoSubmit: true,
      responseType: false,
      onSubmit: function(file, extension) {
        jQuery(\'#slide_preview_8\').html(\'<img src="img/ajax-loader.gif" alt="loadeing"/>\');
      },
      onComplete: function(file, response) {
        var randomnumber=Math.floor(Math.random()*1100);
        jQuery(\'#slide_preview_8\').html(\'<img src="showslidepreview.php?courseid='.$COURSE->id.'&id=attachment_slideup_'.$fmstime.'_8&random=\' +randomnumber+ \'" alt=" "/>\');
      }
    });
    new AjaxUpload(\'attachment_upload_'.$fmstime.'_9\', {
      action: \'uploadimageajax.php\',
      data: {
        \'courseid\' : "'.$COURSE->id.'"
      },
      name: \'attachment_slideup_'.$fmstime.'_9\',
      autoSubmit: true,
      responseType: false,
      onSubmit: function(file, extension) {
        jQuery(\'#slide_preview_9\').html(\'<img src="img/ajax-loader.gif" alt="loadeing"/>\');
      },
      onComplete: function(file, response) {
        var randomnumber=Math.floor(Math.random()*1100);
        jQuery(\'#slide_preview_9\').html(\'<img src="showslidepreview.php?courseid='.$COURSE->id.'&id=attachment_slideup_'.$fmstime.'_9&random=\' +randomnumber+ \'" alt=" "/>\');
      }
    });
    new AjaxUpload(\'attachment_upload_'.$fmstime.'_10\', {
      action: \'uploadimageajax.php\',
      data: {
        \'courseid\' : "'.$COURSE->id.'"
      },
      name: \'attachment_slideup_'.$fmstime.'_10\',
      autoSubmit: true,
      responseType: false,
      onSubmit: function(file, extension) {
        jQuery(\'#slide_preview_10\').html(\'<img src="img/ajax-loader.gif" alt="loadeing"/>\');
      },
      onComplete: function(file, response) {
        var randomnumber=Math.floor(Math.random()*1100);
        jQuery(\'#slide_preview_10\').html(\'<img src="showslidepreview.php?courseid='.$COURSE->id.'&id=attachment_slideup_'.$fmstime.'_10&random=\' +randomnumber+ \'" alt=" "/>\');
      }
    });
};
            
            $("#id_submitbutton").click(function() {
                $(".loaderlayer").show();
            });
            $("#mform1").live("submit", function(){
                if (document.getElementById("mform1").name.value != "") {
                $(".loaderlayer").show();
            var applet = document.getElementById("nanogong1");
            var ret = applet.sendGongRequest("PostToForm", "nanogongslides.php?courseid='.$COURSE->id.'&userid='.$USER->id.'&fmstime='.$fmstime.'&filename='.str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", $fmstime).'_1", "voicefile1","", "temp");
            var applet = document.getElementById("nanogong2");
            var ret = applet.sendGongRequest("PostToForm", "nanogongslides.php?courseid='.$COURSE->id.'&userid='.$USER->id.'&fmstime='.$fmstime.'&filename='.str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", $fmstime).'_2", "voicefile2","", "temp");
            var applet = document.getElementById("nanogong3");
            var ret = applet.sendGongRequest("PostToForm", "nanogongslides.php?courseid='.$COURSE->id.'&userid='.$USER->id.'&fmstime='.$fmstime.'&filename='.str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", $fmstime).'_3", "voicefile3","", "temp");
            var applet = document.getElementById("nanogong4");
            var ret = applet.sendGongRequest("PostToForm", "nanogongslides.php?courseid='.$COURSE->id.'&userid='.$USER->id.'&fmstime='.$fmstime.'&filename='.str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", $fmstime).'_4", "voicefile4","", "temp");
            var applet = document.getElementById("nanogong5");
            var ret = applet.sendGongRequest("PostToForm", "nanogongslides.php?courseid='.$COURSE->id.'&userid='.$USER->id.'&fmstime='.$fmstime.'&filename='.str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", $fmstime).'_5", "voicefile5","", "temp");
            var applet = document.getElementById("nanogong6");
            var ret = applet.sendGongRequest("PostToForm", "nanogongslides.php?courseid='.$COURSE->id.'&userid='.$USER->id.'&fmstime='.$fmstime.'&filename='.str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", $fmstime).'_6", "voicefile6","", "temp");
            var applet = document.getElementById("nanogong7");
            var ret = applet.sendGongRequest("PostToForm", "nanogongslides.php?courseid='.$COURSE->id.'&userid='.$USER->id.'&fmstime='.$fmstime.'&filename='.str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", $fmstime).'_7", "voicefile7","", "temp");
            var applet = document.getElementById("nanogong8");
            var ret = applet.sendGongRequest("PostToForm", "nanogongslides.php?courseid='.$COURSE->id.'&userid='.$USER->id.'&fmstime='.$fmstime.'&filename='.str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", $fmstime).'_8", "voicefile8","", "temp");
            var applet = document.getElementById("nanogong9");
            var ret = applet.sendGongRequest("PostToForm", "nanogongslides.php?courseid='.$COURSE->id.'&userid='.$USER->id.'&fmstime='.$fmstime.'&filename='.str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", $fmstime).'_9", "voicefile9","", "temp");
            var applet = document.getElementById("nanogong10");
            var ret = applet.sendGongRequest("PostToForm", "nanogongslides.php?courseid='.$COURSE->id.'&userid='.$USER->id.'&fmstime='.$fmstime.'&filename='.str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", $fmstime).'_10", "voicefile10","", "temp");
                }
            });
            
            </script>
            <div class="loaderlayer" style="display:none;background-color:#FF0000;position:fixed;right:0px;top:0px"><img src=\'img/ajax-record-save.gif\' alt=\'Recording was saved\'/></div>
            ';
            $mform->addelEment('hidden', 'nanogongfile', date("Ymd_Hi", $fmstime));
            $mform->addElement('static', 'description', '', $mediadata);

            $countofslides[0] = "Select";
        
            for ($i=1; $i<= 10; $i++) {
                $countofslides[$i] = $i;
            }

            $mform->addElement('html', '<div id="recorsfields">');
            $mform->addElement('html', '<script language="JavaScript">
            function ajaxselector(a) {
              //var loadingtext = "<img src=\"img/ajax-loader.gif\" alt=\"loadeing\" />";
              //$("#recorsfields").html(loadingtext);
              $.post("recordsforms.php", { slides: a.options[a.selectedIndex].value, userid: "'.$USER->id.'", fmstime: "'.$fmstime.'" }, function(data){$("#recorsfields").html(data); activateajaximagesuploading();});
            }
            </script>');
            $mform->addElement('html', '<div class="fitem"><div class="fitemtitle"><label for="id_category">'.get_string('slideshow_selectslides', 'slideshow').'</label></div><div class="felement fselect"><select onchange="ajaxselector(this);return false;" name="category" id="id_category">
    <option value="0">Select</option>
    <option value="1">1</option>
    <option value="2">2</option>
    <option value="3">3</option>
    <option value="4">4</option>
    <option value="5">5</option>
    <option value="6">6</option>
    <option value="7">7</option>
    <option value="8">8</option>
    <option value="9">9</option>
    <option value="10">10</option>
</select></div></div>');
            $mform->addElement('html', '</div>');
          }
        }
        else
        {
            $mform->addElement('hidden', 'slides');
        }
        
//-------------------------------------------------------------------------------
        $this->add_action_buttons();

      }
    }


    class mod_slideshow_edit_form extends moodleform {
      function definition() {
        global $COURSE, $CFG, $quizs, $USER, $idrec;
        
        $slides   = optional_param('slides');
        $fmstime  = optional_param('fmstime', time()); 

        $mform    =& $this->_form;
        
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('slideshow_name', 'slideshow'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('htmleditor', 'summary', get_string('slideshow_introduction', 'slideshow'));
        
        $mform->addElement('file', 'attachment', get_string('attachment', 'forum'));
        
        $data = get_record("slideshow_files", "id", $idrec);
        
        $mform->setDefault('name', $data->name);
        $mform->setDefault('summary', $data->text);
        
        $this->add_action_buttons();
      }
    }
    
    
    if ($idrec) {
        $mform = new mod_slideshow_edit_form('edit.php?id='.$id.'&idrec='.$idrec);
        
        $mform->display();
    }
    else
    {
        $mform = new mod_slideshow_add_form('edit.php?id='.$id);
        
        $mform->display();
    }
/// Finish the page
    print_footer($course);

?>
