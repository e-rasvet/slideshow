<?php

require_once("../../config.php");

$slides   = optional_param('slides'); 
$userid   = optional_param('userid');
$fmstime  = optional_param('fmstime');

$siteid = explode ("/", $CFG->wwwroot);
$siteid = str_replace (".", "_", $siteid[2]);

$mediadata = "";

for ($i=1; $i <= $slides; $i++) {
    $mediadata .= '<table><tr><td colspan="3" align="left"><h3>Slide #'.$i.'</h3></td></tr><tr><td><a href="#" id="attachment_upload_'.$fmstime.'_'.$i.'" class="showarrow">Select image (*.jpg) for slide.</a></td><td><input name="slideimages[attachment_slideup_'.$fmstime.'_'.$i.']" type="hidden" value="attachment_slideup_'.$fmstime.'_'.$i.'"/></td><td rowspan="3"><div>Use voice record <input type="checkbox" value="1" name="usevoice['.str_replace(" ", "_", $USER->username).'_'.date("Ymd_Hi", $fmstime).']" checked="checked" /></div>
    <div id="slide_preview_'.$i.'"></div></td></tr><tr><td></td><td></td></tr><tr><td>Record caption for slide. </td><td>
    <applet id="nanogong'.$i.'" archive="nanogong.jar" code="gong.NanoGong" width="180" height="40"><param name="Color" value="#ffffff" /><param name="AudioFormat" value="ImaADPCM" /></applet></td></tr></table>';

    $mediadata .= "<hr /><br />";

}

echo "<center>".$mediadata."<input type=\"hidden\" value=\"".$slides."\" name=\"slides\" /></center>";

?>