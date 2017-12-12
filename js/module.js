// JavaScript Document
function checkAll(className, status) {
    // check all check boxes on page
    $("."+className).prop("checked", status);
}

function checkEnter(myObject, maxLength, submit, numChar, charBar) {
    // restrict length of comment
    var lenObj = myObject.length;
    if (lenObj > maxLength) {
        state = true;
        $('#'+charBar).css('background-color', '#ff0000');
        //document.getElementById(charBar).style.backgroundColor = '#F00';
    } else {
        state = false;
        $('#'+charBar).css('background-color', '#cccccc')
        //document.getElementById(charBar).style.backgroundColor = '#CCC';
    }

    // enable/disable submit buttons
    if (state == true) {
        $('.submit').hide();
    } else {
        $('.submit').show();
    }

   // update character counter
   $('#'+numChar).html(lenObj);
}

function checkForEdit(objStatus) {
    // check if there is a status bar of objStatus
    if ($('#'+objStatus).length) {
        var status = $('#'+objStatus).attr('class');
        if (status == "editing") {
            return confirm("You have unsaved data, do you wish to continue?");
        } else {
            return true;
        }
    } else {
        return true;
    }
}

function countBoxes(className) {
    // make sure at least one check box has been ticked before allowing submission of form
    var count = $('input:checked').length;
    if ($('#checkAllStudents').prop('checked')) {
        // make sure you don't include the checkall button
        count = count - 1;
    }
    if ($('#showLeft').val() == 1) {
        // make sure you don't include the checkall button
        count = count - 1;
    }
    if (count > 0) {
        return true;
    } else {
        alert('You must choose at least one student');
        return false;
    }
}

function instructShow() {
    $('#instruct').show();
    $('#instructShow').hide();
}

function instructHide() {
    $('#instruct').hide();
    $('#instructShow').show();
}

function notSaved(targetHead) {
    // change comment colour when report is edited
    //document.getElementById(target_head).style.backgroundColor = '#00F'; // set colout to blue
    var className = 'editing'
    var text = 'Editing - you should save before moving to another page'; // set colout to blue
    $('#'+targetHead).attr('class', className).html(text);
}

function showEdit(showBox, hideBox, idAnchor) {
    // hide/show textareas
    // used on proofing page to conceal all but current editing boxes
    $('.idedit').hide(); // hide all textareas
    $('.idshow').show(); // show all comments
    $('#'+showBox).show(); // show selected textarea
    $('#'+hideBox).hide(); // hide related comment
    var id = '#' + idAnchor;
    location.ref = id;
}

function showLeft(check, obj) {
    if (check == true)
        var val = 1;
    else
        var val = 0;
    obj.value = val;
}

function stopRKey(evt) {
	// cancel enter key
	var evt = (evt) ? evt : ((event) ? event : null);
	var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
	if ((evt.keyCode == 13) && (node.type=="textarea"))  {return false;}
}

////////////////////////////////////////////////////////////////////////////////
// template design
////////////////////////////////////////////////////////////////////////////////
var sectionTypeList = new Array("", "Text", "Subject", "Pastoral", "Page Break");
var insertList = new Array(
        "Official name", "First name", "Preferred name", "Surname", "Class"
    );
// sections may be loaded and more added
// numitem allows each section to be differentiated even if they have yet to be saved and have no ID
var numItem = 0;
var modpath;
var myMsg;
var myClass;

$(document).ready(function() {    
    // sections can be dragged into a different order
    if (typeof modpath2 !== 'undefined') { 
        modpath = modpath2;

        // create sectionEdit relative to body
        var html = "";
        html += "<div id='sectionEdit'>";
            html += "<form id='sectionForm'>"
                html += "<textarea id='sectionContent'></textarea>";
            html += "</form>";
            html += "<div>";
                html += "<button class='button' id='form_save_btn'>Save</button>";
                html += "<button class='button' id='form_cancel_btn'>Cancel</button>";
                html += "&nbsp;&nbsp;";
                html += "Insert from database: ";
                html += "<select id='insertItem'>";
                    html += "<option value=''>Please select</option>";
                    for (var i=0; i<insertList.length; i++) {
                        html += "<option value='[" + insertList[i] + "]'>" + insertList[i] + "</option>";
                    }
                html += "</select>";
                html += "<button id='insert_item_btn' class='button'>Insert</button>";
                //html += "<input type='text' id='cursorPos' value='' />";
            html += "</div>";
        html += "</div>";
        $('body').append(html);
        
        var reportID = $('#reportID').val();
        if (reportID > 0) {
            selectSectionType(reportID);
            loadData(reportID);
            $('#template').show();
        }

        $('#template_table tbody').sortable();

        $('.tempitem').draggable({
            containment: parent,
            cursor: 'move',
            snap: parent
        });
    }
});

function sectionRow(sectionID, type, sectionType, numItem, content) {
    // create HTML for one section
    var html = "";
    var idContent = 'content' + numItem;
    var idSection = 'section' + numItem;
    var idSectionType = 'sectionType' + numItem;
    
    html += "<tr class='tempitem' id='num" + numItem + "'>";
        html += "<td class='col1'>";
            html += "<img src='" + modpath + "/images/drag.png' alt='drag' height='16' />";
        html += "</td>";
        html += "<td class='col2'>";
            html += "<div style='float:left'>" + type + "</div>";
            html += "<div class='sectionAction'>";
                if (sectionType === '1' && sectionID > 0) {
                    // for now just edit text sections
                    html += "<a href='#' class='sectionEdit'>Edit</a>&nbsp;&nbsp;|&nbsp;&nbsp;";
                }
                html += "<a href='#' class='sectionDelete'>Delete</a>";
                html += "<input type='hidden' class='sectionID' name='sectionID' id='" + idSection + "' value='" + sectionID + "' />";
                html += "<input type='hidden' class='sectionType' name='sectionType' id='" + idSectionType + "' value='" + sectionType + "' />";
            html += "</div>";
            html += "<div style='clear:both'></div>";
            html += "<div id='" + idContent + "'>";
            if (content !== null) {
                html += content;
            }
            html += "</div>";
        html += "</td>";
    html += "</tr>";
    return html;
}

function loadData(reportID) {
    // load data for selected report
    $('#template_table tbody').html('');
    $.ajax({
        url: modpath + "/admin_design_ajax.php",
        data: {
            action: 'load',
            reportID: reportID
        },
        type: 'POST',
        dataType: 'json',
        success: function (data) {
            //console.log(data);
            var html = "";
            html += "<tr>";
                html += "<th>Order</th>";
                html += "<th>Content</th>";
            html += "</tr>";
            $('#template_table thead').html(html);
            
            if (data.section.length > 0) {
                var numItem = 0;
                var html = "";
                $.each(data.section, function(i, sec) {
                    numItem++;
                    html += sectionRow(sec.sectionID, sectionTypeList[sec.sectionType], sec.sectionType, numItem, sec.sectionContent);
                });         
                $('#template_table tbody').html(html);
                actionButtons(reportID);
            }
        },
        error: function() {
        }
    });
}

function actionButtons(reportID) {
    // activate edit and delete buttons
    // prevent duplicate binding
    $('.sectionEdit').unbind('click');
    $('.sectionDelete').unbind('click');
        
    $('.sectionEdit').click(function() {
        var id = $(this).closest('tr').attr('id');
        id = id.substring(3);
        var idSection = 'section' + id;
        var sectionID = $('#'+idSection).val(); // sectionID, could be null
        var idSectionType = 'sectionType' + id;
        var sectionType = parseInt($('#'+idSectionType).val());
        var idContent = "content" + id;
        var html = "";
        var data = "";
        
        switch (sectionType) {
            case 1:
                // type is text
                if (sectionID > 0) {
                    // has already been saved so read details
                    data += $('#' + idContent).html();
                    //textSectionDetail(sectionID);
                } else {
                    data = "";
                }
                
                // open the form div
                var innerWidth = window.innerWidth;
                var innerHeight = window.innerHeight;
                //var width = (innerWidth/2);
                var sectionHeight = (innerHeight*6)/8;
                var contentHeight;
                if (innerWidth > 1000) {
                    contentHeight = sectionHeight - 92;
                } else if (innerWidth > 800) {
                    contentHeight = sectionHeight - 120;
                }
                
                $('#sectionEdit').css({'display': 'block', 'left': innerWidth/8, 'top': innerHeight/8, 'width': (innerWidth*6)/8, 'height': sectionHeight});
                $('#sectionContent').html(data).css({'top': 0, 'height': contentHeight});
                
                toggleButtons(true);
                
                tinymce.init({
                    selector: "#sectionContent",
                    width: '100%',
                    menubar: false,
                    toolbar: 'bold, italic, underline,forecolor,backcolor,|,alignleft, aligncenter, alignright, alignjustify, |, formatselect, fontselect, fontsizeselect, |, table, |, bullist, numlist,outdent, indent, |, link, unlink, image, media, hr, charmap, code, |, cut, copy, paste, undo, redo, fullscreen',
                    plugins: 'table, template, paste, visualchars, image, link, template, textcolor, hr, charmap, fullscreen, code',
                    statusbar: false,
                    apply_source_formatting : true,
                    browser_spellcheck: true,
                    convert_urls: false,
                    relative_urls: false,
                });

                $('#form_save_btn').click(function() {
                    var sectionContent = tinyMCE.get('sectionContent').getContent();
                    $.ajax({
                        url: modpath + "/admin_design_ajax.php",
                        data: {
                            action: 'save_detail',
                            sectionID: sectionID,
                            sectionContent: sectionContent
                        },
                        type: 'POST',
                        success: function (data) {
                            //console.log(data);
                            loadData(reportID);
                            $('#sectionEdit').css({'display': 'none'});
                            toggleButtons(false);
                            if (data === '1') {
                                $('#status').html("Saved section details").addClass('success');
                            } else {
                                $('#status').html("Failed to save section details").addClass('warning');
                            }
                        }
                    });
                });

                $('#form_cancel_btn').click(function() {
                    $('#sectionEdit').css({'display': 'none'});
                    toggleButtons(false);
                });
                
                $('#insert_item_btn').click(function() {
                    var item = $('#insertItem').val();
                    tinyMCE.execCommand('mceInsertContent', false, item);return false;
                });

                
                break;
                
            case 2:
                break;
        }
    });

    $('.sectionDelete').click(function() {
        if (confirm("Delete this section?")) {
            var id = $(this).closest('tr').attr('id');
            $('#' + id).remove();
        }
    });
}

function toggleButtons(status) {
    $('#load_btn').prop('disabled', status);
    $('#save_btn').prop('disabled', status);
    if (status) {
        $('#sectionTypePanel').css({'visibility': 'hidden'});
        $('.sectionAction').css({'visibility': 'hidden'});
    } else {
        $('#sectionTypePanel').css({'visibility': 'visible'});
        $('.sectionAction').css({'visibility': 'visible'});
    }
}

function selectSectionType(reportID) {
    // show list of section types that can be added
    if (reportID > 0) {
        var html = "<div id='sectionTypePanel'>Insert: ";
        for (var i=1; i<sectionTypeList.length; i++) {
            html +=  "<a href='#' class='tempinsert' id='" + i + "'>";
                html += sectionTypeList[i];
            html += "</a>";
            html += "&nbsp;&nbsp;|&nbsp;&nbsp;";
        }
        html += "</div>";
        html += "<div><button class='button' id='save_btn'>Save</button> Save list of sections, types and the order in which they should be displayed</div>";
        $('#sectionTypeList').html(html);
        
        // insert new section
        $('.tempinsert').click(function() {
            var type = $(this).html();
            var sectionType = $(this).attr('id');
            var reportID = $('#reportID').val();
            numItem++;
            html = sectionRow("", sectionTypeList[sectionType], sectionType, numItem, "");

            $('#template_table tbody').append(html);

            actionButtons(reportID);
        });
        
        // save sections and order
        $('#save_btn').click(function() {
            var formData = $('#report_template').serialize();
            $.ajax({
                url: modpath + "/admin_design_ajax.php",
                data: {
                    action: 'save',
                    reportID: reportID,
                    formData: formData
                },
                type: 'POST',
                success: function (data) {
                    console.log(data);
                    loadData(reportID);
                    if (data === '1') {
                        $('#status').html("Saved sections").addClass('success');
                    } else {
                        $('#status').html("Failed to save sections").addClass('warning');
                    }
                },
                error: function() {
                }
            });
        });
    }
}


////////////////////////////////////////////////////////////////////////////////
function setStatus(ok, action) {
    // set values for displaying message after save
    if (ok) {
        myMsg = action + " successful";
        myClass = "success";

    } else {
        myMsg = action + " failed";
        myClass = "warning";
    }
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function caretPos(el)
{
    var pos = 0;
    // IE Support
    if (document.selection) 
    {
    	el.focus ();
    	var Sel = document.selection.createRange();
    	var SelLength = document.selection.createRange().text.length;
    	Sel.moveStart ('character', -el.value.length);
    	pos = Sel.text.length - SelLength;
    }
    // Firefox support
    else if (el.selectionStart || el.selectionStart == '0')
    	pos = el.selectionStart;

    return pos;
}
////////////////////////////////////////////////////////////////////////////////
