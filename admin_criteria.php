<?php
if (isActionAccessible($guid, $connection2,"/modules/Reporting/admin_criteria.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    // proceed
    // include function pages
    $modpath =  "./modules/".$_SESSION[$guid]["module"];

    include $modpath."/admin_function.php" ;
    include $modpath."/admin_criteria_function.php" ;
    include $modpath."/function.php";

    $crit = new crit($guid, $connection2);
    $crit->modpath = $modpath;

    $title = "Criteria";
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
    echo "<li>Use this page to enter the criteria that need to be graded for each subject</li>";
    echo "<li>Select the year group and subject</li>";
    echo "<li>Add, edit and delete the criteria</li>";
    echo "<li>Make sure you show the order in which you wish the criteria to appear<li>";
    echo "</ul>";
    echo "</div>";
    echo "<div id='instructShow' style='display:block;float:right' class='smalltext'>";
    echo "<a href='#' onclick='instructShow()'>Instructions</a>";
    echo "</div>";
    echo "<div style='clear:both;'></div>";


    echo "<div class = '$crit->class' id = 'status'>$crit->msg</div>";
    admin_navbar($guid, $connection2, $title);
    $_SESSION[$guid]['sidebarExtra'] = chooseSchoolYear($connection2, '', '', $crit->schoolYearID);
    $_SESSION[$guid]['sidebarExtra'] .= chooseYearGroup($connection2, $crit->yearGroupID, $crit->schoolYearID);
    $_SESSION[$guid]['sidebarExtra'] .= $crit->choose_subject($connection2);

    $crit->mainform($guid, $connection2);
}
?>