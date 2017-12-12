<?php
if (isActionAccessible($guid, $connection2,"/modules/Reporting/admin_design.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    // proceed
    // include function pages
    $modpath =  "./modules/".$_SESSION[$guid]["module"];
    ?>
    <script>
        var modpath2 = "<?php echo $modpath ?>";
    </script>
    <?php
    include $modpath."/admin_function.php" ;
    include $modpath."/admin_design_function.php" ;
    include $modpath."/function.php";

    $des = new des($guid, $connection2);
    $des->modpath = $modpath;

    $title = "Design";
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
    echo "<p>Create the layout for the PDF file associated with a report</p>";
    echo "<ul>";
    echo "<li>Text - use the editor to create any text.</li>";
    echo "<li>Insert values from the database using the insert feature</li>";
    echo "<li>Subject - will show all subject reports.  Currently you have no control over what appears in the PDF but we can add options on later.</li>";
    echo "<li>Pastoral - will show form tutor comments</li>";
    echo "<li>Page break - forces output to a new page</li>";
    echo "<li>Images - it is possible to insert an image but at the moment it will not appear on the PDF.</li>";
    echo "<li>You can only use basic formatting such as justification and font size.  We will make other formatting features work in later versions.</li>";
    echo "</ul>";
    echo "</div>";
    echo "<div id='instructShow' style='display:block;float:right' class='smalltext'>";
    echo "<a href='#' onclick='instructShow()'>Instructions</a>";
    echo "</div>";
    echo "<div style='clear:both;'></div>";

    echo "<div class = '' id = 'status'></div>";
    admin_navbar($guid, $connection2, $title);
    $_SESSION[$guid]['sidebarExtra'] = chooseSchoolYear($connection2, '', '', $des->schoolYearID);
    $_SESSION[$guid]['sidebarExtra'] .= $des->chooseReport($connection2, $des->reportID, $des->schoolYearID);

    $des->mainform($guid, $connection2);
}
?>