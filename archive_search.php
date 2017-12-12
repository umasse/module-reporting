<?php
if (isActionAccessible($guid, $connection2,"/modules/Reporting/archive.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    // proceed
    // include function pages
    $modpath =  "./modules/".$_SESSION[$guid]["module"];
    include $modpath."/archive_search_function.php" ;
    include $modpath."/function.php";

    $arc = new arc($guid, $connection2);
    $arc->modpath = $modpath;

    // return page for forms
    $thisPage = 'search';
    $title = 'Archive - All students';

    $arc->repAccess = 2; // testing purposes

    setSessionVariables($guid, $connection2);

    ///////////////////////////////////////////////////////////////////////////////////////////
    // output to screen
    ///////////////////////////////////////////////////////////////////////////////////////////
    pageTitle($title);

    echo "<div class='instruct' id='instruct' style='display:none'>";
    echo "<div style='float:left'><strong>Instructions</strong></div>";
    echo "<div style='float:right'>";
    echo "<a href='#' onclick='instructHide()'>Hide</a>";
    echo "</div>";
    echo "<div style=clear:both></div>";
    echo "<ul>";
    echo "<li>Type in all or part of the student's surname or first name";
    echo "<li>Click on <em>Go</em> or press <em>Enter</em></li>";
    echo "<li>Select the student you want from the drop down box and click on <em>Go</em></li>";
    echo "<li>Click on a report name to download and open it in your PDF reader</li>";
    echo "</ul>";
    echo "</div>";
    echo "<div id='instructShow' style='display:block;float:right' class='smalltext'>";
    echo "<a href='#' onclick='instructShow()'>Instructions</a>";
    echo "</div>";
    echo "<div style='clear:both;'>&nbsp;</div>";


    archiveNavbar($guid, $thisPage);
    //echo "<div class = '$arc->class' id = 'status'>$arc->msg</div>";
    /*
    $_SESSION[$guid]['sidebarExtra'] = "<div>";
    $_SESSION[$guid]['sidebarExtra'] .= chooseSchoolYear($connection2, '', $arc->reportID, $arc->schoolYearID);
    $_SESSION[$guid]['sidebarExtra'] .= chooseYearGroup($connection2, $arc->yearGroupID);
    if ($arc->yearGroupID > 0) {
        $_SESSION[$guid]['sidebarExtra'] .= chooseRollgroup($connection2, $arc->rollGroupID, $arc->schoolYearID, $arc->yearGroupID);
    }
    if ($arc->rollGroupID > 0) {
        $_SESSION[$guid]['sidebarExtra'] .= chooseReport($connection2, '', $arc->reportID, $arc->rollGroupID, $arc->schoolYearID, '', $arc->yearGroupID);
    }
    $_SESSION[$guid]['sidebarExtra'] .= "</div><div style = 'clear:both;'>&nbsp;</div>";
    if ($arc->rollGroupID > 0 && $arc->reportID > 0) {
        $_SESSION[$guid]['sidebarExtra'] .= $arc->chooseLeft();
    }
    */
    echo "<div>&nbsp</div>";
    echo "<div>";
        $arc->mainform($guid, $connection2);
    echo "</div>";
}
?>
