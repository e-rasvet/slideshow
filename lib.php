<?php  // $Id: lib.php,v 1.4 2010/07/09 16:41:20 Igor Nikulin Exp $


/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will create a new instance and return the id number 
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted slideshow record
 **/
function slideshow_add_instance($slideshow) {
    
    $slideshow->timemodified = time();

    # May have to add extra stuff in here #
    
    return insert_record("slideshow", $slideshow);
}

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will update an existing instance with new data.
 *
 * @param object $instance An object from the form in mod.html
 * @return boolean Success/Fail
 **/
function slideshow_update_instance($slideshow) {

    $slideshow->timemodified = time();
    $slideshow->id = $slideshow->instance;

    # May have to add extra stuff in here #

    return update_record("slideshow", $slideshow);
}

/**
 * Given an ID of an instance of this module, 
 * this function will permanently delete the instance 
 * and any data that depends on it. 
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function slideshow_delete_instance($id) {

    if (! $slideshow = get_record("slideshow", "id", "$id")) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! delete_records("slideshow", "id", "$slideshow->id")) {
        $result = false;
    }

    return $result;
}

/**
 * Return a small object with summary information about what a 
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 **/
function slideshow_user_outline($course, $user, $mod, $slideshow) {
    return $return;
}

/**
 * Print a detailed representation of what a user has done with 
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function slideshow_user_complete($course, $user, $mod, $slideshow) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity 
 * that has occurred in slideshow activities and print it out. 
 * Return true if there was output, or false is there was none. 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function slideshow_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such 
 * as sending out mail, toggling flags etc ... 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function slideshow_cron () {
    global $CFG;

    return true;
}

/**
 * Must return an array of grades for a given instance of this module, 
 * indexed by user.  It also returns a maximum allowed grade.
 * 
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int $slideshowid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 **/
function slideshow_grades($slideshowid) {
   return NULL;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of slideshow. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $slideshowid ID of an instance of this module
 * @return mixed boolean/array of students
 **/
function slideshow_get_participants($slideshowid) {
    return false;
}

/**
 * This function returns if a scale is being used by one slideshow
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $slideshowid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function slideshow_scale_used ($slideshowid,$scaleid) {
    $return = false;

    //$rec = get_record("slideshow","id","$slideshowid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}



function slideshow_runExternal( $cmd, &$code ) {

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



function slideshow_groups_get_user_groups($userid=0) {
    global $CFG, $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    if (!$rs = get_recordset_sql("SELECT g.id, gg.groupingid
                                    FROM {$CFG->prefix}groups g
                                         JOIN {$CFG->prefix}groups_members gm        ON gm.groupid = g.id
                                         LEFT JOIN {$CFG->prefix}groupings_groups gg ON gg.groupid = g.id
                                   WHERE gm.userid = $userid")) {
        return array('0' => array());
    }

    $result    = array();
    $allgroups = array();
    
    while ($group = rs_fetch_next_record($rs)) {
        $allgroups[$group->id] = $group->id;
        if (is_null($group->groupingid)) {
            continue;
        }
        if (!array_key_exists($group->groupingid, $result)) {
            $result[$group->groupingid] = array();
        }
        $result[$group->groupingid][$group->id] = $group->id;
    }
    rs_close($rs);

    $result['0'] = array_keys($allgroups); // all groups

    return $result;
} 

function slideshow_show_slide($p) {
	//########## $CFG was missing,  edited Justin 20110514
   // global $id, $course, $USER, $searchtext;
	 global $id, $course, $USER, $searchtext, $CFG;
	//############# End of edit 20110514

    print_simple_box_start('center', '800px', '#ffffff', 10);
    $data_ = get_record("slideshow_files", "id", $p);
    
    if (strstr($data_->text, "{FMS:SLIDESHOW=")) {
        $fmslink = explode ("{FMS:SLIDESHOW=", $data_->text);
        $fmslink = $fmslink[1];
        $fmslink = explode ("}", $fmslink);
        $fmslink = $fmslink[0];
      
        //$fmshtml = '<div style="margin: 10px;"><h3><a href="view.php?id='.$id.'&p='.$data_->id.'">'.$data_->name.'</a></h3></div><div style="clean:both;"></div><div style="border: 1px #666666 solid;float:left;width:500px;margin-right: 20px;"><object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="500" height="428" id="audioplayer" align="middle"><param name="allowScriptAccess" value="sameDomain" /><param name="movie" value="'.$fmslink.'" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" /><embed src="'.$fmslink.'" quality="high" bgcolor="#ffffff" width="500" height="428" name="audioplayer" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object></div>';
        
        $fmslink2 = str_replace("{$CFG->wwwroot}/mod/slideshow/swfslideplayer.swf?xml_url={$CFG->wwwroot}/file.php", "html5slideshow.php?xml_url=", $fmslink);
        $fmshtml = '<iframe src="'.$fmslink2.'" style="border: medium none;" height="433px" scrolling="no" width="508px">
  &lt;p&gt;Your browser does not support iframes.&lt;/p&gt;
           </iframe>';
        
        $height = "200px";
    }
    else
    {
        $fmshtml = '<div style="margin: 10px;"><h3><a href="view.php?id='.$id.'&p='.$data_->id.'">'.$data_->name.'</a></h3></div><div style="clean:both;"></div><div></div>';
        $height = "100px";
    }
            
    $fmshtml .= '<div style="background:#FFFFCC;padding:20px;">';
        
    $datauser = get_record("user", "id", $data_->userid);
    $picture  = print_user_picture($data_->userid,$course->id, true, 0, true);
    $fmshtml .= '<div style="height:'.$height.';float:left;margin-right:10px;"><strong><a href="'.$CFG->wwwroot.'/user/view.php?id='.$id.'&course='.$course->id.'">'. fullname($datauser)."({$datauser->username})</a></strong><br /><br />";
            
    if ($data_->userid == $USER->id) {
        $fmshtml .= '<a href="view.php?id='.$id.'&delete='.$data_->id.'" onclick="return confirm(\'Are you sure you want to delete?\')">'.get_string('slideshow_delete', 'slideshow').'</a><br />
        <a href="edit.php?id='.$id.'&idrec='.$data_->id.'">'.get_string('slideshow_edit', 'slideshow').'</a><br />';
    } else if (isteacher($USER->id)) {
        $fmshtml .= '<a href="view.php?id='.$id.'&delete='.$data_->id.'" onclick="return confirm(\'Are you sure you want to delete?\')">'.get_string('slideshow_delete', 'slideshow').'</a><br />';
    }
        
    if ($data_->multiplechoicequestions != "no") {
        $fmshtml .= '<a href="question_test.php?id='.$id.'&p='.$data_->id.'" target="questionpreview" onclick="this.target=\'questionpreview\'; return openpopup(\'/mod/slideshow/question_test.php?id='.$id.'&p='.$data_->id.'\', \'questionpreview\', \'scrollbars=yes,resizable=yes,width=600,height=700\', 0);">'.get_string('slideshow_questions', 'slideshow').'</a><br />';
    }
    else if ($USER->id == $data_->userid) {
        $fmshtml .= '<a href="questions.php?id='.$id.'&p='.$data_->id.'">'.get_string('slideshow_questionsadd', 'slideshow').'</a><br />';
    }
    if ($data_->file != "no") $fmshtml .= '<a href="'.$CFG->wwwroot.'/file.php/'.$course->id.'/presentation/'.$data_->id.'/'.$data_->file.'">'.get_string('slideshow_ppt', 'slideshow').'</a><br />';
        
    $comments = count_records("slideshow_comments", "fileid", $data_->id);
    $fmshtml .= '<a href="view.php?id='.$id.'&p='.$data_->id.'#comments">'.get_string('slideshow_comments', 'slideshow', $comments).'</a><br />';
        
    $fmshtml .= '</div><div style="height:'.$height.';">'.$picture .'</div></div><div style="clean:both;"></div>';
        
    $fmshtml .= print_simple_box_start('center', '760px', '#ffffff', 10, '', '', true);
       
    if (strstr($data_->text, "{FMS:SLIDESHOW=")) {
        $data_->text = str_replace ("{FMS:SLIDESHOW=".$fmslink."}", $fmshtml, format_text($data_->text));
    }
    else
    {
        $data_->text = $fmshtml."<br />".format_text($data_->text);
    }
    $data_->text .= print_simple_box_end(true);
        //}
        
    if ($searchtext) $data_->text = str_replace($searchtext, '<strong>'.$searchtext.'</strong>', $data_->text);
        
    echo $data_->text;
        
    print_simple_box_end();
    
    print_simple_box_end();
}


function slideshow_show_slide_shot($p) {
	//########## $CFG was missing,  edited Justin 20110514
    //global $id, $course, $USER, $searchtext;
	global $id, $course, $USER, $searchtext, $CFG;
	//################End of edit 20110514

    print_simple_box_start('center', '800px', '#ffffff', 10);
    $data_ = get_record("slideshow_files", "id", $p);
    
    $slideschecker = "";
    
    if (!strstr($data_->text, "{FMS")) $slideschecker = get_string('slideshow_withoutslides', 'slideshow');
    
    echo '<div style="margin: 10px;"><h3><a href="view.php?id='.$id.'&p='.$data_->id.'">'.$data_->name.'</a>'.$slideschecker.'</h3></div>';
    
    $datauser = get_record("user", "id", $data_->userid);
    echo '<div style="text-align:right"><small>'.date("H:i d.m.Y", $data_->timemodified).' <a href="'.$CFG->wwwroot.'/user/view.php?id='.$id.'&course='.$course->id.'">'. fullname($datauser).' ('.$datauser->username.')</a></small></div>';
    
    print_simple_box_end();
}

?>