<?php
if (isActionAccessible($guid, $connection2,"/modules/Reporting/admin.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    // proceed
    // include function pages
    $modpath =  "./modules/".$_SESSION[$guid]["module"];

    include $modpath."/admin_function.php" ;
    include $modpath."/function.php";

    $title = "Admin";
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
    echo "<p>Manage the system setup from these pages</p>";
    echo "<ul>";
    echo "<li>Define - create reports</li>";
    echo "<li>Assign - assign reports to year groups</li>";
    echo "<li>Design - design the template for the report for PDF output</li>";
    echo "<li>Access - set access to reports for different roles</li>";
    echo "<li>Criteria - Set up criteria on which each subject will report</li>";
    echo "<li>Complete - check which staff have written their reports</li>";
    echo "<li>Start of year - bring forward settings from previous school year so you do not have to design everything from scratch</li>";
    echo "</ul>";
    echo "</div>";
    echo "<div id='instructShow' style='display:block;float:right' class='smalltext'>";
    echo "<a href='#' onclick='instructShow()'>Instructions</a>";
    echo "</div>";
    echo "<div style='clear:both;'></div>";

    admin_navbar($guid, $connection2, $title);
    $_SESSION[$guid]['sidebarExtra'] = freemium($modpath);
}
?>
