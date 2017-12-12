<?php
if (isActionAccessible($guid, $connection2,"/modules/Reporting/admin_complete.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>".__($guid, 'Home')."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".__($guid, getModuleName($_GET['q']))."</a> > </div><div class='trailEnd'>".__($guid, 'Admin Complete').'</div>';
    echo '</div>';    
    // proceed
    // include function pages
    $modpath =  "./modules/".$_SESSION[$guid]["module"];

    include $modpath."/admin_function.php" ;
    include $modpath."/admin_complete_function.php" ;
    include $modpath."/function.php";

    $comp = new comp($guid, $connection2);
    $comp->modpath = $modpath;

    $title = "Complete";
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
    echo "<ul>";
    echo "<li>This page shows whether teachers have completed their reports</li>";
    echo "<li>Select a year group from the drop down box on the right</li>";
    echo "<li>Select a class</li>";
    echo "<li>Reports marked with a tick have been created and downloaded by the parents</li>";
    echo "<li>Reports marked with a cross have been created but have not yet been downloaded by the parents</li>";
    echo "<li>Where there is no tick or cross the report has not been created</li>";
    echo "</ul>";
    echo "</div>";
    echo "<div id='instructShow' style='display:block;float:right' class='smalltext'>";
    echo "<a href='#' onclick='instructShow()'>Instructions</a>";
    echo "</div>";
    echo "<div style='clear:both;'></div>";


    echo "<div class = '$comp->class' id = 'status'>$comp->msg</div>";
    admin_navbar($guid, $connection2, $title);
    $_SESSION[$guid]['sidebarExtra'] = chooseSchoolYear($connection2, '', '', $comp->schoolYearID);
    $_SESSION[$guid]['sidebarExtra'] .= chooseYearGroup($connection2, $comp->yearGroupID, $comp->schoolYearID);
    $_SESSION[$guid]['sidebarExtra'] .= $comp->chooseReport($connection2, $comp->reportID, $comp->schoolYearID, $comp->yearGroupID);

    $comp->mainform($guid, $connection2);
}
?>
