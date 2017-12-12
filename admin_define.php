<?php
if (isActionAccessible($guid, $connection2,"/modules/Reporting/admin_define.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>".__($guid, 'Home')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".__($guid, getModuleName($_GET['q']))."</a> > </div><div class='trailEnd'>".__($guid, 'Admin Define').'</div>';
    echo '</div>';    
    // proceed
    // include function pages
    $modpath =  "./modules/".$_SESSION[$guid]["module"];

    include $modpath."/admin_function.php" ;
    include $modpath."/admin_define_function.php" ;
    include $modpath."/function.php";

    $def = new def($guid, $connection2);
    $def->modpath = $modpath;

    $title = "Create";
    setSessionVariables($guid, $connection2);

    ///////////////////////////////////////////////////////////////////////////////////////////
    // output to screen
    ///////////////////////////////////////////////////////////////////////////////////////////
    echo "<div class='instruct' id='instruct' style='display:none'>";
    echo "<div style='float:left'><strong>Instructions</strong></div>";
    echo "<div style='float:right'>";
    echo "<a href='#' onclick='instructHide()'>Hide</a>";
    echo "</div>";
    echo "<div style=clear:both></div>";
    echo "<p>Use this page to create reports</p>";
    echo "<ul>";
    echo "<li>Create a name for the report.  Best if you include the school year and term to make it easier to retrieve reports later.</li>";
    echo "<li>Set the default grade scale.  Later we may allow different criteria to use different grade scales.</li>";
    echo "<li>Currently you have little control over the appearance of the report.  We can add in more options later.</li>";
    echo "<li>You can add as many reports as you like.  You may define different reports for different year groups and different times of the year.</li>";
    echo "<li>Later we may consider adding a <em>copy</em> function to allow new reports to be set up with the same settings as an existing one.</li>";
    echo "</ul>";
    echo "</div>";
    echo "<div id='instructShow' style='display:block;float:right' class='smalltext'>";
    echo "<a href='#' onclick='instructShow()'>Instructions</a>";
    echo "</div>";
    echo "<div style='clear:both;'></div>";


    echo "<div class = '$def->class' id = 'status'>$def->msg</div>";
    admin_navbar($guid, $connection2, $title);
    $_SESSION[$guid]['sidebarExtra'] = chooseSchoolYear($connection2, '', '', $def->schoolYearID);

    $def->mainform($guid, $connection2);
}
?>
