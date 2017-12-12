<?php
if (isActionAccessible($guid, $connection2,"/modules/Reporting/admin_assign.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    // proceed
    // include function pages
    $modpath =  "./modules/".$_SESSION[$guid]["module"];

    include $modpath."/admin_function.php" ;
    include $modpath."/admin_assign_function.php" ;
    include $modpath."/function.php";

    $ass = new ass($guid, $connection2);
    $ass->modpath = $modpath;

    $title = "Assign";
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
    echo "<li>Use this page to assign reports to year groups</li>";
    echo "<li>Make sure you have created the reports you need in the <em>define</em> section</li>";
    echo "<li>Check boxes in the appropriate columns</li>";
    echo "<li>Click on <em>save</em></li>";
    echo "</ul>";
    echo "</div>";
    echo "<div id='instructShow' style='display:block;float:right' class='smalltext'>";
    echo "<a href='#' onclick='instructShow()'>Instructions</a>";
    echo "</div>";
    echo "<div style='clear:both;'></div>";


    echo "<div class = '$ass->class' id = 'status'>$ass->msg</div>";
    admin_navbar($guid, $connection2, $title);
    $_SESSION[$guid]['sidebarExtra'] = chooseSchoolYear($connection2, '', '', $ass->schoolYearID);

    $ass->mainform($guid, $connection2);
}
?>
