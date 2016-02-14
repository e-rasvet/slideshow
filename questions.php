<?php  // $Id: view.php,v 1.4 2010/07/09 16:41:20 Igor Nikulin Exp $

    require_once("../../config.php");
    require_once("lib.php");
    require_once ($CFG->dirroot.'/course/moodleform_mod.php');
    
    $id               = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    $a                = optional_param('a', 'questions');  // slideshow ID
    $p                = optional_param('p'); 

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
    
    if ($_POST['name_1']) {
        delete_records("slideshow_questions", "fileid", $p);
        foreach ($_POST as $key => $value) {
            if (strstr($key, "name_")) {
                $idq = (int) str_replace("name_", "", $key);
                
                $data                 = new object;
                $data->instance       = $id;
                $data->fileid         = $p;
                $data->name           = optional_param('name_'.$idq);
                $data->userid         = $USER->id;
                $data->timemodified   = time();
                
                if ($idqr = insert_record("slideshow_questions", $data)) {
                    $data                 = new object;
                    $data->questionid     = $idqr;
                    $data->name           = optional_param('cha_'.$idq);
                    $data->grade          = optional_param('cka_'.$idq, 0, PARAM_INT);
                    $data->timemodified   = time();
                    if ($data->name) insert_record("slideshow_choice", $data);
                    $data                 = new object;
                    $data->questionid     = $idqr;
                    $data->name           = optional_param('chb_'.$idq);
                    $data->grade          = optional_param('ckb_'.$idq, 0, PARAM_INT);
                    $data->timemodified   = time();
                    if ($data->name) insert_record("slideshow_choice", $data);
                    $data                 = new object;
                    $data->questionid     = $idqr;
                    $data->name           = optional_param('chc_'.$idq);
                    $data->grade          = optional_param('ckc_'.$idq, 0, PARAM_INT);
                    $data->timemodified   = time();
                    if ($data->name) insert_record("slideshow_choice", $data);
                    $data                 = new object;
                    $data->questionid     = $idqr;
                    $data->name           = optional_param('chd_'.$idq);
                    $data->grade          = optional_param('ckd_'.$idq, 0, PARAM_INT);
                    $data->timemodified   = time();
                    if ($data->name) insert_record("slideshow_choice", $data);
                    
                    set_field ("slideshow_files", "multiplechoicequestions", $idqr, "id", $p);
                }
            }
        }
        redirect($CFG->wwwroot.'/mod/slideshow/view.php?id='.$id, "Done");
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
    
    print_simple_box_start('center', '800px', '#ffffff', 10);
    
    echo '<script type="text/javascript" src="js/jquery.min.js"></script>';
    
    $data = get_record("slideshow_files", "id", $p);
    
    echo '<script type="text/javascript" charset="utf-8">

var metaid = 2;

function addFormFieldMeta() {
    var metarowid = window.metaid;
    jQuery("#divTxtMeta").append(\'<div id="row_meta_\'+metarowid+\'" style="clear: both;-moz-border-radius:6px 6px 6px 6px;background-color:#F3F3F3;list-style-type:none;padding:5px 5px 5px 10px;margin:5px"> \
    <table> \
    <tr> \
    <td width="200px" align="right"><strong>Question \'+metarowid+\'</strong></td> \
    <td width="60px" align="right">'.get_string('slideshow_questionname', 'slideshow').'</td> \
    <td colspan="3"><input type="text" name="name_\'+metarowid+\'" value="" style="width:400px;" /></td> \
    </tr> \
    <tr> \
    <td></td> \
    <td align="right">A</td> \
    <td><input type="text" name="cha_\'+metarowid+\'" value="" style="width:320px;" /></td> \
    <td width="10px"><input type="checkbox" name="cka_\'+metarowid+\'" value="1"></td> \
    <td>'.get_string('slideshow_correct', 'slideshow').'</td> \
    </tr> \
    <tr> \
    <td></td> \
    <td align="right">B</td> \
    <td><input type="text" name="chb_\'+metarowid+\'" value="" style="width:320px;" /></td> \
    <td width="10px"><input type="checkbox" name="ckb_\'+metarowid+\'" value="1"></td> \
    <td>'.get_string('slideshow_correct', 'slideshow').'</td> \
    </tr> \
    <tr> \
    <td></td> \
    <td align="right">C</td> \
    <td><input type="text" name="chc_\'+metarowid+\'" value="" style="width:320px;" /></td> \
    <td width="10px"><input type="checkbox" name="ckc_\'+metarowid+\'" value="1"></td> \
    <td>'.get_string('slideshow_correct', 'slideshow').'</td> \
    </tr> \
    <tr> \
    <td></td> \
    <td align="right">D</td> \
    <td><input type="text" name="chd_\'+metarowid+\'" value="" style="width:320px;" /></td> \
    <td width="10px"><input type="checkbox" name="ckd_\'+metarowid+\'" value="1"></td> \
    <td>'.get_string('slideshow_correct', 'slideshow').'</td> \
    </tr> \
    </table> \
    </div>\');
    metarowid = (metarowid - 1) + 2;
    window.metaid = metarowid;
}
function removeFormFieldMeta(metarowid) {
    jQuery(metarowid).remove();
}
</script> 

<form method="post" action="questions.php?id='.$id.'&p='.$p.'">
<div id="divTxtMeta">
<div id="row_meta_1" style="clear: both;-moz-border-radius:6px 6px 6px 6px;background-color:#F3F3F3;list-style-type:none;padding:5px 5px 5px 10px;margin:5px">
<table> 
    <tr> 
    <td width="200px" align="right"><strong>Question 1</strong></td> 
    <td width="60px" align="right">'.get_string('slideshow_questionname', 'slideshow').'</td> 
    <td colspan="3"><input type="text" name="name_1" value="" style="width:400px;" /></td> 
    </tr> 
    <tr> 
    <td></td> 
    <td align="right">A</td> 
    <td><input type="text" name="cha_1" value="" style="width:320px;" /></td> 
    <td width="10px"><input type="checkbox" name="cka_1" value="1"></td> 
    <td>'.get_string('slideshow_correct', 'slideshow').'</td> 
    </tr> 
    <tr> 
    <td></td> 
    <td align="right">B</td> 
    <td><input type="text" name="chb_1" value="" style="width:320px;" /></td> 
    <td width="10px"><input type="checkbox" name="ckb_1" value="1"></td> 
    <td>'.get_string('slideshow_correct', 'slideshow').'</td> 
    </tr> 
    <tr> 
    <td></td> 
    <td align="right">C</td> 
    <td><input type="text" name="chc_1" value="" style="width:320px;" /></td> 
    <td width="10px"><input type="checkbox" name="ckc_1" value="1"></td> 
    <td>'.get_string('slideshow_correct', 'slideshow').'</td> 
    </tr> 
    <tr> 
    <td></td> 
    <td align="right">D</td> 
    <td><input type="text" name="chd_1" value="" style="width:320px;" /></td> 
    <td width="10px"><input type="checkbox" name="ckd_1" value="1"></td> 
    <td>'.get_string('slideshow_correct', 'slideshow').'</td> 
    </tr> 
</table>
</div>
</div>
<div style="margin-left:40px;"><a href="#" onClick="addFormFieldMeta(); return false;">'.get_string('slideshow_add', 'slideshow').'</a></div>';
    
    echo '<div style="clear: both;padding-top:10px;text-align:center;">
<p class="submit"><input type="submit" name="submit" value="'.get_string('slideshow_create', 'slideshow').'" /></p>
</div>
</form>';
    
    print_simple_box_end();

/// Finish the page
    print_footer($course);

?>
