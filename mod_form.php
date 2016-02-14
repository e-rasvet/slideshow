<?php //$Id


require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_slideshow_mod_form extends moodleform_mod {

    function definition() {

        global $COURSE, $CFG, $quizs;

        $mform    =& $this->_form;
        
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('slideshow_name', 'slideshow'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('htmleditor', 'summary', get_string('slideshow_introduction', 'slideshow'));
//-------------------------------------------------------------------------------

        $mform->addElement('select', 'allowstudentslideshow', get_string('slideshow_allowstudentslideshow', 'slideshow'), Array("yes"=>"yes", "no"=>"no"));
        
        $mform->addElement('select', 'multiplechoicequestions', get_string('slideshow_multiplechoicequestions', 'slideshow'), Array('yes' => 'yes', 'no' => 'no' ));
        
        $mform->addElement('select', 'allowcomment', get_string('slideshow_allowcomment', 'slideshow'), Array("yes"=>"yes", "no"=>"no"));
        
        $mform->addElement('select', 'maxupload', get_string('slideshow_maxupload', 'slideshow'), Array("0"=>"Unlimited", "1"=>"1", "2"=>"2", "3"=>"3", "4"=>"4", "5"=>"5", "6"=>"6", "7"=>"7", "8"=>"8", "9"=>"9", "10"=>"10"));
        
//-------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        $this->add_action_buttons();

    }
}

?>
