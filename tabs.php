<?php  // $Id: mysql.php,v 1.0 2010/07/09 16:41:20 Igor Nikulin

    if (empty($slideshow)) {
        error('You cannot call this script in that way');
    }
    if (!isset($a)) {
        $a = 'list';
    }
    if (!isset($cm)) {
        $cm = get_coursemodule_from_instance('slideshow', $slideshow->id);
    }
    if (!isset($course)) {
        $course = get_record('course', 'id', $slideshow->course);
    }

    $tabs       = array();
    $row        = array();
    $inactive   = NULL;
    $activetwo  = NULL;
    $secondrow  = array();

    $row[] = new tabobject('list', $CFG->wwwroot . "/mod/slideshow/view.php?id=" . $id , get_string('slideshow_listofslideshows', 'slideshow'));
    $row[] = new tabobject('edit', $CFG->wwwroot . "/mod/slideshow/edit.php?id=" . $id , get_string('slideshow_addnewslideshow', 'slideshow'));
    
    $tabs[] = $row;
    
    $groups = groups_get_all_groups($course->id);
    //print_r ($groups);
    
    if ($groups) {
        $secondrow[] = new tabobject('group_all', $CFG->wwwroot."/mod/slideshow/view.php?id={$id}&groupshow=group_all", 'List of all Presentations');
        //$secondrow[] = new tabobject('group_wg', $CFG->wwwroot."/mod/slideshow/view.php?id={$id}&groupshow=group_wg", 'Without Group');
        foreach ($groups as $group) {
          $secondrow[] = new tabobject('group_'.$group->id, $CFG->wwwroot."/mod/slideshow/view.php?id={$id}&groupshow={$group->id}" , "({$group->name}) group");
        }
    }
    
    if ($groups && $a == 'list') {
        $inactive  = array($a);
        $activetwo = array($groupshow);
        
        $tabs = array($row, $secondrow);
    }
    else
    {
        $tabs = array($row);
    }
    
	//#############Justin 20110514
	//whats this $p for? perhaps its a paramater
	//if not declared goes to error though, so added an isset
    //if ($p) {
	if (isset($p)){
	//#############Justin 20110514 end of edit
        $inactive   = NULL;
        $activetwo  = NULL;
    }
    
    print_tabs($tabs, $a, $inactive, $activetwo);

?>