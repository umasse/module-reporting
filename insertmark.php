<?php
if (isActionAccessible($guid, $connection2,"/modules/Reporting/insertmark.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    // proceed

    // include function pages
    $modpath =  "./modules/".$_SESSION[$guid]["module"];
    include $modpath."/subject_function.php";
    include $modpath."/insertmark_function.php";
    include $modpath."/function.php";
    
    $schoolYearID = $_GET['schoolYearID'];
    $reportID = $_GET['reportID'];
    $subjectID = $_GET['subjectID'];
    $classID = $_GET['classID'];
    
    $criteriaList = readCriteriaList($connection2, $subjectID);    
    $markSetList = get_markSet($connection2, $classID);
    
    echo "<form name='markbookMap' method='post' action='insertmark_confirm.php'>";
        echo "<input type='hidden' name='schoolYearID' value='$schoolYearID' />";
        echo "<input type='hidden' name='reportID' value='$reportID' />";
        echo "<input type='hidden' name='subjectID' value='$subjectID' />";
        echo "<input type='hidden' name='classID' value='$classID' />";
        echo "<table style='table-layout:fixed'>";
            echo "<tr>";
                echo "<th style='width:150px;'>Report field</th>";
                echo "<th style='width:150px;'>Markbook field</th>";
                echo "<th style='width:80px;'>Attainment</th>";
                echo "<th style='width:80px;'>Effort</th>";
                echo "<th style='width:80px;'>Comment</th>";
            echo "</tr>";
            $criteriaList->execute();
            while ($rowCriteria = $criteriaList->fetch()) {
                $typeName = "type".$rowCriteria['criteriaID'];
                echo "<tr>";
                    echo "<td>";
                        echo "<input type='hidden' name='criteriaID' value='".$rowCriteria['criteriaID']."' />";
                        echo $rowCriteria['criteriaName'];
                    echo "</td>";
                    echo "<td>";
                        $markSetList->execute();
                        echo "<select>";
                            echo "<option value='0'>No mapping</option>";
                            while ($rowMarkSet = $markSetList->fetch()) {
                                echo "<option value='".$rowMarkSet['gibbonMarkbookColumnID']."'>";
                                    echo $rowMarkSet['name'];
                                echo "</option>";
                            }
                        echo "</select>";
                    echo "</td>";
                    echo "<td style='text-align:center;'>";
                        echo "<input type='radio' name='$typeName' value='1' checked/>";
                    echo "</td>";
                    echo "<td style='text-align:center;'>";
                        echo "<input type='radio' name='$typeName' value='2' />";
                    echo "</td>";
                    echo "<td style='text-align:center;'>";
                        echo "<input type='radio' name='$typeName' value='3' />";
                    echo "</td>";
                echo "</tr>";
            }
        echo "</table>";
        echo "<input type='submit' name='submit' value='Submit' />";
        echo "<input type='reset' name='cancel' value='Cancel' />";
    echo "</form>";
    die();
    
    // get year group
    $yearGroupIDList = get_classYearGroups($connection2, $classCode, $schoolYearID);
    
    $gradeset = read_gradeset($guid, $connection2, $yearGroupIDList);
    $yearGroupID = yearGroupListToYearGroup($yearGroupIDList);

    $studentID = 0;
    if (isset($_GET['studentID'])) {
        $studentID = $_GET['studentID'];
    }

    $reportName = get_reportName($connection2, $schoolYearID, $reportNum);
    $className  = get_className($connection2, $classCode);

    $markSetID = 0;
    $markSetList = get_markSet($connection2, $classCode);
    

    // see if a markset has been chosen
    if (isset($_POST['markSetID'])) {
        $markSetID = $_POST['markSetID'];
    }

    $status = 0;
    // if a markset has been chosen read the marks associated with it
    if ($markSetID > 0) {
        
        $markList = read_markList($connection2, $markSetID, $studentID);
        $markSetName = get_markSetName($connection2, $markSetID);
        
        // read from form
        $insertAttain = 1;
        $insertEffort = 1;
        $insertComment = 1;
        $overwrite = 0;
        
        if (isset($_POST['submit'])) {
            $insertAttain  = $_POST['insertAttain'];
            $insertEffort  = $_POST['insertEffort'];
            $insertComment = $_POST['insertComment'];
            $overwrite     = $_POST['overwrite'];
            $status = insert_grades($connection2, $schoolYearID, $yearGroupID, $reportNum, $classCode,
                $markList, $studentID, $insertAttain, $insertEffort, $insertComment, $overwrite);
        }
    }
    show_insert_status($status);
    
    ?>
    <div class='smalltext'>
        <table>
            <tr>
                <td><strong>Report:</strong></td>
                <td><?php echo $reportName ?></td>
            </tr>
            <tr>
                <td><strong>Class:</strong></td>
                <td><?php echo $className ?></td>
            </tr>
        </table>
        <?php
        if ($studentID > 0) {
            $studentName = get_studentName($connection2, $studentID);
            echo "<div>Student: ".$studentName."</div>";
        }
        ?>

        <?php
        if ($markSetList->rowCount() > 0) {
            ?>
            <form name='marksetselect' method='post' action=''>
                <input type='hidden' id='markSetID' name='markSetID' value='' />
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Att</th>
                        <th>Eff</th>
                        <th>Com</th>
                    </tr>
                    <?php
                    while ($row_markSetList = $markSetList->fetch()) {
                        $link = "document.marksetselect.markSetID.value='".
                                $row_markSetList['gibbonMarkbookColumnID']."';
                                    document.marksetselect.submit();";
                        ?>
                        <tr>
                            <td>
                                <a href="#" onclick="<?php echo $link ?>">
                                    <?php echo $row_markSetList['name'] ?>
                                </a>
                            </td>
                            <td><?php echo $row_markSetList['type'] ?></td>
                            <td><?php echo $row_markSetList['attainment'] ?></td>
                            <td><?php echo $row_markSetList['effort'] ?></td>
                            <td><?php echo $row_markSetList['complete'] ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </form>
            <?php
        }
        ?>

        <?php
        if ($markSetID > 0) {
            ?>
            <div><strong><?php echo $markSetName ?></strong></div>
            <?php
            if ($markList->rowCount() > 0) {
                ?>
                <!-- choose fields to insert -->
                <form name="insertMark" method="post" action="">
                    <input type="hidden" name="markSetID" value="<?php echo $markSetID ?>"/>
                    <div>
                        <div style="float:left;width:150px;">Insert attainment grade:</div>
                        <div style="float:left;">
                            <input type="radio" name="insertAttain" value="1"
                            <?php if ($insertAttain == 1) echo "checked='checked'" ?>> Yes
                            <input type="radio" name="insertAttain" value="0"
                            <?php if ($insertAttain == 0) echo "checked='checked'" ?>> No
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div>
                        <div style="float:left;width:150px;">Insert effort grade:</div>
                        <div style="float:left;">
                            <input type="radio" name="insertEffort" value="1"
                            <?php if ($insertEffort == 1) echo "checked='checked'" ?>> Yes
                            <input type="radio" name="insertEffort" value="0"
                            <?php if ($insertEffort == 0) echo "checked='checked'" ?>> No
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div>
                        <div style="float:left;width:150px;">Insert comment:</div>
                        <div style="float:left;">
                            <input type="radio" name="insertComment" value="1"
                            <?php if ($insertComment == 1) echo "checked='checked'" ?>> Yes
                            <input type="radio" name="insertComment" value="0"
                            <?php if ($insertComment == 0) echo "checked='checked'" ?>> No
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div>
                        <div style="float:left;width:150px;">Overwrite existing data:</div>
                        <div style="float:left;">
                            <input type="radio" name="overwrite" value="1"
                            <?php if ($overwrite == 1) echo "checked='checked'" ?>> Yes
                            <input type="radio" name="overwrite" value="0"
                            <?php if ($overwrite == 0) echo "checked='checked'" ?>> No
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div>Copy these marks?</div>
                    <div><input type="submit" name="submit" value="Yes" /></div>
                </form>
                <p>&nbsp;</p>
                <?php
                // show marks
                show_marks($markList);
            } else {
                echo "<div class = 'smalltext'>No marks to insert</div>";
            }
        }
        ?>
    </div>
    <?php
}
