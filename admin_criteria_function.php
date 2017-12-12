<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
class crit {

    var $class;
    var $msg;

    function crit($guid, $connection2) {

        $this->schoolYearID = getSchoolYearID($connection2, $schoolYearName, $currentYear);

        $this->yearGroupID = getYearGroupID();
        $this->subjectID = $this->getSubjectID();
        $this->criteriaID = $this->getCriteriaID();

        // check if add, edit or delete is required
        $this->mode = getMode();

        if (isset($_POST['save'])) {
            $ok = $this->save($connection2);
            setStatus($ok, 'Save', $this->msg, $this->class);
            if ($ok) {
                $this->criteriaID = '';
                $this->mode = '';
            }
        }

        if (isset($_POST['cancel'])) {
            $this->criteriaID = '';
            $this->mode = '';
        }

        if ($this->mode == 'delete') {
            $ok = $this->delete($connection2);
            setStatus($ok, 'Save', $this->msg, $this->class);
            if ($ok) {
                $this->criteriaID = '';
                $this->mode = '';
            }
        }
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function getSubjectID() {
        $subjectID = '';
        if (isset($_POST['subjectID'])) {
            $subjectID = $_POST['subjectID'];
        } else {
            if (isset($_GET['subjectID'])) {
               $subjectID = $_GET['subjectID'];
            }
        }
        return $subjectID;
    }

    function getCriteriaID() {
        $criteriaID = '';
        if (isset($_POST['criteriaID'])) {
            $criteriaID = $_POST['criteriaID'];
        } else {
            if (isset($_GET['criteriaID'])) {
               $criteriaID = $_GET['criteriaID'];
            }
        }
        return $criteriaID;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function readCriteriaList($connection2) {
        // read list of criteria associated with a course/subject
        $data = array("subjectID" => $this->subjectID);
        $sql = "SELECT *
            FROM arrCriteria
            WHERE subjectID = :subjectID
            ORDER BY criteriaOrder";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function delete($connection2) {
        $data = array('criteriaID' => $this->criteriaID);
        $sql = "DELETE FROM arrCriteria
            WHERE criteriaID = :criteriaID";
        $rs = $connection2->prepare($sql);
        $ok = $rs->execute($data);
        return $ok;
    }

    function save($connection2) {
        $criteriaName = trim($_POST['criteriaName']);

        $data = array(
            'criteriaName' => $criteriaName
        );
        $set = "SET criteriaName = :criteriaName";
        if ($this->criteriaID > 0) {
            $data['criteriaID'] = $this->criteriaID;
            $sql = "UPDATE arrCriteria $set WHERE criteriaID = :criteriaID";
        } else {
            $data['subjectID'] = $this->subjectID;
            $set .= ", subjectID = :subjectID";
            $sql = "INSERT IGNORE INTO arrCriteria $set";
        }
        $rs = $connection2->prepare($sql);
        $ok = $rs->execute($data);
        return $ok;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function formCriteria() {
        ?>
        <tr>
            <td>
                <input type='text' name='criteriaName' value='<?php echo $this->criteriaName ?>' size='40' />
            </td>
            <td>
                <input type='submit' name='save' value='Save' />
                <input type='submit' name='cancel' value='Cancel' />
            </td>
        </tr>
        <?php
    }

    function mainform($guid, $connection2) {
        if ($this->yearGroupID > 0 && $this->subjectID > 0) {
            $path = $_SESSION[$guid]['absoluteURL']."/modules/".$_SESSION[$guid]["module"];
            $modpath = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]["module"];
            $linkPath = $modpath.'/admin_criteria.php';
            $linkNew = $linkPath.
                    "&amp;subjectID=".$this->subjectID.
                    "&amp;yearGroupID=".$this->yearGroupID.
                    "&amp;schoolYearID=".$this->schoolYearID.
                    "&amp;mode=new";
            $this->criteriaList = $this->readCriteriaList($connection2);
            
            ?>
            <div>&nbsp;</div>
            <form id='frm_define' name='frm_define' method='post' action=''>
                <input type='hidden' name='criteriaID' value='<?php echo $this->criteriaID ?>' />
                <input type='hidden' name='subjectID' value='<?php echo $this->subjectID ?>' />
                <input type='hidden' name='yearGroupID' value='<?php echo $this->yearGroupID ?>' />
                <input type='hidden' name='schoolYearID' id='schoolYearID' value='<?php echo $this->schoolYearID ?>' />
                <div style='display:inline-block;margin-right:10px;'><a href='<?php echo $linkNew ?>'>Add new</a>  (drag to change order)</div>
                <div style='display:inline-block;margin-right:10px;'><a href='#' id='copycrit'>Copy</a></div>
                <div>Names must be unique</div>
                <table class='mini' style='width:100%' id='critTable'>
                    <thead>
                        <tr>
                            <th style='width:55%;'>Criteria</th>
                            <th style='width:25%;'>Action</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                    <?php
                    if ($this->criteriaList->rowCount() == 0 || $this->mode == 'new') {
                        $this->criteriaName = '';
                        $this->criteriaOrder = '';
                        $this->formCriteria();
                    }
                    while ($row = $this->criteriaList->fetch()) {
                        if ($this->criteriaID == $row['criteriaID']) {
                            $this->criteriaName = $row['criteriaName'];
                            $this->formCriteria();
                        } else {
                            $linkEdit = $linkPath.
                                "&amp;criteriaID=".$row['criteriaID'].
                                "&amp;subjectID=".$this->subjectID.
                                "&amp;yearGroupID=".$this->yearGroupID.
                                "&amp;schoolYearID=".$this->schoolYearID.
                                "&amp;mode=edit";
                            $messageDelete = "WARNING All grades associated with this criterion will be lost.  Delete ".$row['criteriaName']."?";
                            $linkDelete = "window.location = \"$linkPath&amp;criteriaID=".$row['criteriaID'].
                                "&amp;subjectID=".$this->subjectID.
                                "&amp;yearGroupID=".$this->yearGroupID.
                                "&amp;schoolYearID=".$this->schoolYearID.
                                "&amp;mode=delete\"";
                            ?>
                            <tr class='crititem'>
                                <td>
                                    <?php 
                                    echo "<input type='hidden' name='rowCriteriaID' value='".$row['criteriaID']."' />";
                                    echo $row['criteriaName'] 
                                    ?>
                                </td>
                                <td style='text-align:center'>
                                    <a href='<?php echo $linkEdit ?>'>Edit</a> <a href='#' onclick='if (confirm("<?php echo $messageDelete ?>")) <?php echo $linkDelete ?>'>Delete</a>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </form>
            
            <p>&nbsp;</p>
            <div id='copyCriteria' style='background-color: #eeeeee;padding:4px;display:none;'>
                <form id='copyCriteriaForm' method='post'>
                    <?php
                    $this->copyCriteriaList($connection2);
                    //$this->copyReportList($connection2);
                    $this->copyYearGroupList($connection2);
                    $this->copySubjectList($connection2);
                    ?>
                    <div>
                    <button type='button' id='copySubmit'>Copy</button>
                    </div>
                </form>
            </div>
            <p>&nbsp;</p>
            
            <script>
                var path = '<?php echo $path ?>';
                var orderpath = path + "/admin_criteria_ajax.php"; 
                $('#critTable tbody').sortable({
                    // save order after dragging to new position
                    stop: function() {
                        var formData = $('#frm_define').serialize();
                        $.ajax({
                            url: orderpath,
                            data: {
                                formData: formData
                            },
                            type: 'POST',
                            success: function(data) {
                                console.log(data);
                            }
                        });
                    }
                });
                
                // preserve table width when dragging
                $('td').each(function(){
                    $(this).css('width', $(this).width() +'px');
                });
                
                $('#copycrit').click(function() {
                    $('#copyCriteria').show();
                });
                
                $('.criteriaListAll').click(function() {
                    checkAll('criteriaList', $(this).prop('checked'));
                });
                /*
                $('.reportListAll').click(function() {
                    checkAll('reportList', $(this).prop('checked'));
                });
                
                $('.yearGroupListAll').click(function() {
                    checkAll('yearGroupList', $(this).prop('checked'));
                });
                */
                $('.subjectListAll').click(function() {
                    checkAll('subjectList', $(this).prop('checked'));
                });
                
                // year group changed so change subject list
                $('#yearGroupIDcopy').change(function() {
                    orderpath = path + "/admin_criteria_subject_ajax.php",
                    $.ajax({
                        url: orderpath,
                        data: {
                            yearGroupID: $('#yearGroupIDcopy').val(),
                            schoolYearID: $('#schoolYearID').val()
                        },
                        type: 'POST',
                        dataType: 'JSON',
                        success: function(data) {
                            console.log(data);
                            var html = '';
                            $.each(data.subjectList, function(i, sub) {
                                html += "<div>";
                                    html += "<input type='checkbox' class='subjectList' name='subjectIDcopy' value='" + sub.subjectID + "' checked /> ";
                                    html += sub.subjectName;
                                html += "</div>";
                            });
                            $('#subjectList').html(html);
                        }
                    });
                });
                
                // submitted now copy criteria to seleted targets
                $('#copySubmit').click(function() {
                    // copy criteria to selected targets
                    var formData = $('#copyCriteriaForm').serialize();
                    //console.log(formData);
                    orderpath = path + "/admin_criteria_copy_ajax.php",
                    $.ajax({
                        url: orderpath,
                        data: {
                            formData: formData
                        },
                        type: 'POST',
                        success: function(data) {
                            console.log(data);
                            alert('Copied');
                        }
                    });
                });
            </script>
            <?php
        }
        ?>
        <?php
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////////
    function choose_subject($connection2) {
        // select subject
        ob_start();
        if ($this->yearGroupID > 0) {
            $subjectList = $this->readSubjectlist($connection2);
            ?>
            <div style = "padding:2px;">
                <div style="float:left;width:30%" class = "smalltext">Subject</div>
                <div style="float:left;width:70%">
                    <form name='frm_subject' method='post' action='' style="display:inline">
                        <input type='hidden' name='yearGroupID' value='<?php echo $this->yearGroupID ?>' />
                        <input type='hidden' name='schoolYearID' value='<?php echo $this->schoolYearID ?>' />
                        <select name='subjectID' style="width:95%" onchange="this.form.submit()">
                            <option></option>
                            <?php
                            if ($subjectList->rowCount() > 0) {
                                while ($row = $subjectList->fetch()) {
                                    $selected = '';
                                    if ($this->subjectID == $row['subjectID']) {
                                        $selected = 'selected';
                                    }
                                    $subjectName = trimCourseName($row['subjectName']);
                                    echo "<option value='".$row['subjectID']."' $selected>";
                                        echo trimCourseName($row['subjectName']);
                                    echo "</option>";
                                }
                            }
                            ?>
                        </select>
                    </form>
                </div>
                <div style="clear:both"></div>
            </div>
            <?php
        }
        return ob_get_clean();
    }
    ////////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////////
    function readSubjectlist($connection2) {
        $data = array(
            "schoolYearID" => $this->schoolYearID,
            "yearGroupID" => '%'.$this->yearGroupID.'%'
        );
        $sql = "SELECT DISTINCT gibbonCourse.gibbonCourseID AS subjectID, 
            gibbonCourse.name AS subjectName
            FROM gibbonCourse
            INNER JOIN gibbonCourseClass
            ON gibbonCourse.gibbonCourseID = gibbonCourseClass.gibbonCourseID
            WHERE gibbonSchoolYearID = :schoolYearID
            AND gibbonYearGroupIDList LIKE :yearGroupID
            AND reportable = 'Y'";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
    ////////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////////
    function copySubjectList($connection2) {
        $subjectList = $this->readSubjectlist($connection2);
        ?>
        <div style='display:inline-block;vertical-align:top;'>
            <div><strong>Select subjects to copy to</strong></div>
            <?php
            echo "<div style='margin-bottom:4px;'><input type='checkbox' class='subjectListAll' value='1' /> <em>Check all</em></div>";
            echo "<div id='subjectList'>";
                while ($row = $subjectList->fetch()) {
                    echo "<div>";
                        echo "<input type='checkbox' class='subjectList' name='subjectIDcopy' value='".$row['subjectID']."' checked /> ";
                        echo $row['subjectName'];
                    echo "</div>";
                }
            echo "</div>";
            ?>
        </div>
        <?php
    }
    ////////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////////
    function copyYearGroupList($connection2) {
        $yearGroupList = readYeargroup($connection2);
        ?>
        <div style='display:inline-block;margin-right:10px;vertical-align:top;'>
            <div><strong>Select year group to copy to</strong></div>
            <?php
            echo "<select name='yearGroupIDcopy' id='yearGroupIDcopy'>";
                while ($row = $yearGroupList->fetch()) {
                    echo "<option value='".$row['gibbonYearGroupID']."'>";
                        echo $row['name'];
                    echo "</option>";
                }
            echo "</select>";
            ?>
        </div>
        <?php
    }
    ////////////////////////////////////////////////////////////////////////////////
    
    /*
    ////////////////////////////////////////////////////////////////////////////////
    function copyReportList($connection2) {
        $reportList = readReport($connection2, $this->schoolYearID);
        ?>
        <div style='display:inline-block;margin-right:10px;vertical-align:top;'>
            <div><strong>Select reports to copy to</strong></div>
            <?php
            echo "<div style='margin-bottom:4px;'><input type='checkbox' class='reportListAll' value='1' /> <em>Check all</em></div>";
            while ($row = $reportList->fetch()) {
                echo "<div>";
                    echo "<input type='checkbox' class='reportList' name='reportIDcopy' value='".$row['reportID']."' checked /> ";
                    echo $row['reportName'];
                echo "</div>";
            }
            ?>
        </div>
        <?php
    }
    ////////////////////////////////////////////////////////////////////////////////
    */
    
    ////////////////////////////////////////////////////////////////////////////////
    function copyCriteriaList($connection2) {
        ?>
        <div style='display:inline-block;margin-right:10px;vertical-align:top;'>
            <div><strong>Select criteria to copy</strong></div>
            <?php
            echo "<div style='margin-bottom:4px;'><input type='checkbox' class='criteriaListAll' value='1' /> <em>Check all</em></div>";
            $this->criteriaList->execute();
            while ($row = $this->criteriaList->fetch()) {
                echo "<div>";
                    echo "<input type='checkbox' class='criteriaList' name='criteriaIDcopy' value='".$row['criteriaID']."' checked /> ";
                    echo $row['criteriaName'];
                echo "</div>";
            }
            ?>
            <p>&nbsp;</p>
        </div>
        <?php
    }
    ////////////////////////////////////////////////////////////////////////////////
}
?>
