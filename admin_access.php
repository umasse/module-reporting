<?php
if (isActionAccessible($guid, $connection2,"/modules/Reporting/admin_access.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    // proceed
    // include function pages
    $modpath =  "./modules/".$_SESSION[$guid]["module"];

    include $modpath."/admin_function.php" ;
    include $modpath."/admin_access_function.php" ;
    include $modpath."/function.php";

    $acc = new acc($guid, $connection2);
    $acc->modpath = $modpath;

    $title = "Access";
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
    echo "<li>Use this page to enable or disable report editing</li>";
    echo "<li>Reports that are ticked may be edited</li>";
    echo "<li>Reports that are not ticked may not be edited by teachers</li>";
    echo "<li>SLT and administrators may edit reports even when they are closed</li>";
    echo "</ul>";
    echo "</div>";
    echo "<div id='instructShow' style='display:block;float:right' class='smalltext'>";
    echo "<a href='#' onclick='instructShow()'>Instructions</a>";
    echo "</div>";
    echo "<div style='clear:both;'></div>";


    echo "<div class = '$acc->class' id = 'status'>$acc->msg</div>";
    admin_navbar($guid, $connection2, $title);
    $_SESSION[$guid]['sidebarExtra'] = chooseSchoolYear($connection2, '', '', $acc->schoolYearID);

    $acc->mainform($guid, $connection2);
}
?>
