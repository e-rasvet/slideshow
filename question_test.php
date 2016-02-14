<?php  // $Id: view.php,v 1.4 2010/07/09 16:41:20 Igor Nikulin Exp $

    require_once("../../config.php");
    require_once("lib.php");
    require_once ($CFG->dirroot.'/course/moodleform_mod.php');
    
    $id               = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    $a                = optional_param('a', 'questions');  // slideshow ID
    $p                = optional_param('p'); 
    $submit           = optional_param('submit'); 

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

    add_to_log($course->id, "slideshow", "view", "view.php?id=$cm->id", "$slideshow->id");
    
    if ($submit) {
      foreach ($_POST as $key => $value) {
        if (strstr($key, "ck_")) {
          $idch = str_replace("ck_", "", $key);
          
          if ($dataq = get_record("slideshow_choice", "id", $idch)) {
            $data                 = new object;
            $data->questionid     = $dataq->questionid;
            $data->userid         = $USER->id;
            $data->answerid       = $idch;
            $data->timemodified   = time();
            
            insert_record("slideshow_answer", $data);
          }
        }
      }
    }
    
    print_header();
    
    $data   = get_records("slideshow_questions", "fileid", $p);
    
    echo '<form action="question_test.php?id='.$id.'&p='.$p.'" method="post">';
    
    $nosubmit = false;
    
    foreach ($data as $data_) {
        $dataansfers = get_records_sql("SELECT * FROM {$CFG->prefix}slideshow_answer WHERE questionid='{$data_->id}' and userid='{$USER->id}'");

        echo '<div id="row_meta_1" style="clear: both;-moz-border-radius:6px 6px 6px 6px;background-color:#F3F3F3;list-style-type:none;padding:5px 5px 5px 10px;margin:5px"><table><tr>
        <td width="50px"></td>
        <td colspan="2" align="center"><strong>'.$data_->name.'</strong></td>
        <td width="100px"></td>
        </tr>';
        $datach = get_records("slideshow_choice", "questionid", $data_->id);
        foreach ($datach as $datach_) {
            if ($dataansfers) {
                $nosubmit = true;
                $youransfer = "";
                foreach ($dataansfers as $dataansfer) {
                    if ($datach_->id == $dataansfer->answerid) $youransfer = get_string('slideshow_youransfer', 'slideshow');
                }
                if ($datach_->grade == "0") $ansferci = get_string('slideshow_incorrect', 'slideshow'); else $ansferci = get_string('slideshow_correct', 'slideshow');
                
                echo '<tr>
        <td width="50px"></td>';
                if (!empty($youransfer) && $datach_->grade != "0") {
                    echo '<td width="200px" align="right">'.$datach_->name.'</td>
        <td width="200px"><font color="green">'.$youransfer.' '.$ansferci.'</font></td>';
                }
                else
                {
                    echo '<td width="200px" align="right">'.$datach_->name.'</td>
        <td width="200px">'.$youransfer.' '.$ansferci.'</td>';
                }
                echo '<td width="100px"></td>
        </tr>';
            }
            else
            {
                echo '<tr>
        <td width="50px"></td>
        <td width="200px" align="right">'.$datach_->name.'</td>
        <td width="50px"><input type="checkbox" name="ck_'.$datach_->id.'" value="1" /></td>
        <td width="100px"></td>
        </tr>';
            }
        }
        echo '<tr>
        </tr></table></div>';
    }
    
    if (!$nosubmit) echo '<div id="row_meta_1" style="clear: both;-moz-border-radius:6px 6px 6px 6px;background-color:#F3F3F3;list-style-type:none;padding:5px 5px 5px 10px;margin:5px"><table width="450px"><tr>
        <td align="center"><input type="submit" name="submit" value="'.get_string('slideshow_submit', 'slideshow').'" /></td></tr></table></div>';
    
    echo '</form>';
    
/// Finish the page
    print_footer($course);

?>
