<?php  // $Id: view.php,v 1.4 2010/07/09 16:41:20 Igor Nikulin Exp $

    require_once("../../config.php");
    require_once("lib.php");
    require_once ($CFG->dirroot.'/course/moodleform_mod.php');
    
    $id               = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    $a                = optional_param('a', 'list');  // slideshow ID
    $groupshow        = optional_param('groupshow', 'group_all');  // slideshow ID
    $orderby          = optional_param('orderby'); 
    $sort             = optional_param('sort'); 
    $page             = optional_param('page', 1); 
    $p                = optional_param('p'); 
    $delete           = optional_param('delete'); 
    $textcomment      = optional_param('textcomment'); 
    $searchtext       = optional_param('searchtext'); 

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

    if (!isstudent($course->id) && !isteacher($course->id)) {
      error('Only for students and teachers');
    }

    add_to_log($course->id, "slideshow", "view", "view.php?id=$cm->id", "$slideshow->id");

    if ($delete) {
        $data = get_record("slideshow_files", "id", $delete);
        if (isteacher($USER->id) || $USER->id == $data->userid) {
            delete_records("slideshow_files", "id", $delete);
        }
    }

    if ($textcomment) {
        $data                = new object;
        $data->instance      = $id;
        $data->userid        = $USER->id;
        $data->fileid        = $p;
        $data->text          = $textcomment;
        $data->timemodified  = time();
        
        insert_record ("slideshow_comments", $data);
    }

/// Print the page header

    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
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
    
    
    if ($p) {
        slideshow_show_slide($p);
        
        echo '<div id="comments"></div>';
        
        $comments = get_records("slideshow_comments", "fileid", $p);
        
		//#########error if no comments, added conditional JUSTIN 20110514
		if($comments){
		//##########
			foreach ($comments as $comment) {
				print_simple_box_start('center', '800px', '#ffffff', 10);
				echo format_text($comment->text);
				
				$datauser = get_record("user", "id", $comment->userid);
				echo '<div style="text-align:right"><small>'.date("H:i d.m.Y", $comment->timemodified).' <a href="'.$CFG->wwwroot.'/user/view.php?id='.$id.'&course='.$course->id.'">'. fullname($datauser).'('.$datauser->username.')</a></small></div>';
				
				print_simple_box_end();
			}
		//#########error if no comments, added conditional JUSTIN 20110514
        }
		//#########end of edit 20110514
		
        if ($slideshow->allowcomment == "yes") {
            print_simple_box_start('center', '800px', '#ffffff', 10);
            class slideshow_comment_form extends moodleform {
                function definition() {
                    global $COURSE, $CFG, $cm, $USER, $slideshow;
                    $mform    =& $this->_form;
                    $mform->addElement('header', 'general', get_string('slideshow_comment', 'slideshow'));
                    
                    $mform->addElement('htmleditor', 'textcomment', get_string('slideshow_text', 'slideshow'));
                    $mform->setType('textcomment', PARAM_TEXT);
                    $mform->addRule('textcomment', null, 'required', null, 'client');
                    
                    $this->add_action_buttons($cancel = false);
                }
            }
            
            $mform = new slideshow_comment_form('view.php?id='.$id.'&p='.$p);
            $mform->display(); 
            print_simple_box_end();
        }
        
    } else {
        $from = $page * 30 - 30;
        
        if ($searchtext) {
          if ($groupshow == "group_all") {
            $searchtextsql = " WHERE text LIKE '%{$searchtext}%' ";
          }
          else
          {
            $searchtextsql = " AND text LIKE '%{$searchtext}%' ";
          }
        }
      
	// ############edit JUSTIN  20110514 
	// if no search text entered went to error
    //    if ($groupshow == "group_all") {
		if(!$searchtext){
			 $data = get_records_sql("SELECT * FROM {$CFG->prefix}slideshow_files WHERE instance={$id}  ORDER BY timemodified LIMIT {$from}, 30");
		}else  if ($groupshow == "group_all") {
	//############# End of edito Justin 20110514
            $data = get_records_sql("SELECT * FROM {$CFG->prefix}slideshow_files {$searchtextsql} ORDER BY timemodified LIMIT {$from}, 30");
        } else if ($groupshow == "group_wg") {
            $data = get_records_sql("SELECT * FROM {$CFG->prefix}slideshow_files WHERE groupid='0' {$searchtextsql} ORDER BY timemodified LIMIT {$from}, 30");
        } else {
            $data = get_records_sql("SELECT * FROM {$CFG->prefix}slideshow_files WHERE groupid='{$groupshow}' {$searchtextsql} ORDER BY timemodified LIMIT {$from}, 30");
        }
        
        $totalcount = count($data);
        
        print_simple_box_start('center', '800px', '#ffffff', 10);
        echo '<div style=""><form action="view.php?id='.$id.'&groupshow='.$groupshow.'" method="post"><input style="width: 680px;" type="text" name="searchtext" value="'.$searchtext.'" /> <input type="submit" name="submit" value="'.get_string('slideshow_search', 'slideshow').'" /></form></div>';
    
        print_simple_box_end();
    
        print_paging_bar($totalcount, $page, 30, 'view.php?id='.$id.'&groupshow='.$groupshow.'&');
        
		// ############edit JUSTIN  20110514 
		//if no data showed error, added the if($data) condition
		//foreach ($data as $data_) {
		//		slideshow_show_slide_shot($data_->id);
		//}
		if($data){
			foreach ($data as $data_) {
				slideshow_show_slide_shot($data_->id);
			}
		}
		//############# End of edito Justin 20110514
        
        print_paging_bar($totalcount, $page, 30, 'view.php?id='.$id.'&groupshow='.$groupshow.'&');
    }

/// Finish the page
    print_footer($course);

?>
