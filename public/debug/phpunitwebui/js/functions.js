/**
 * Copyright (c) 2009  Radu Gasler <miezuit@gmail.com>
 *
 *  This file is free software: you may copy, redistribute and/or modify it  
 *  under the terms of the GNU General Public License as published by the  
 *  Free Software Foundation, either version 2 of the License, or (at your  
 *  option) any later version.
 *
 *  This file is distributed in the hope that it will be useful, but  
 *  WITHOUT ANY WARRANTY; without even the implied warranty of  
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU  
 *  General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License  
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 *
 * JavaScript functions used by Test Runner.
 *
 * $Id: functions.js 2 2009-03-06 11:11:06Z miezuit $
 *
 * $Rev: 2 $
 *
 * $LastChangedBy: miezuit $
 *
 * $LastChangedDate: 2009-03-06 13:11:06 +0200 (V, 06 mar. 2009) $
 *
 * @author Radu Gasler <miezuit@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html     GPL License
 * @version 0.1
 */

/**
 * Function called when document loaded.
 * Activates the context menu on the menu component.
 */
function onDocumentLoad()
{
  $("#menu").contextMenu({
        menu: 'myContextMenu'
    },
        function(action, el, pos) {
            if (action == 'menuRun') {
                runTests();
            } else if (action == 'menuReload') {
                resetTester();
            }
    });
}

/**
 * Shows or hides a group.
 *
 * @param string id of the group
 */
function toggleGroup(id)
{
    var group = document.getElementById(id);
    var arrow = document.getElementById('arrow_' + id);
    if(group.style.display == 'block') {
        group.style.display = 'none';
        arrow.src = 'images/collapsed.png'
    } else {
        group.style.display = 'block';
        arrow.src = 'images/expanded.png';
    }
    var arrows = $('.arrow_suite');
	arrows.attr('src', 'images/collapsed.png');
	var length = arrows.length;
	for(var i = 0; i< length; i++){
		id = arrows[i].id;
		var name = id.split('_')[1];
		var top = arrows[i].offsetTop;
		var left = arrows[i].offsetLeft + 20;
		$('#tests_'+name).css('top', top+'px').css('left', left+'px');
	}
}

/**
 * Checks or uncheckes a checkbox.
 *
 * @param string id of the checkbox
 * @param booolean true or false [optional] if not given will reverse the current value
 */
function setCheckBox(id, val)
{
    var checkbox = document.getElementById(id);
    if(val == null) {
        val = !checkbox.checked;
    }
    checkbox.checked = val;
    var checkboxFront = checkbox.nextSibling;
    checkboxFront.className = 'checkboxFront' + (val ? 'Set' : 'Unset');
}

/**
 * Sets the suite checkbox based on checked tests
 * If there is no test checked for the suite will uncheck the suite
 *
 * @param string tests checkbox id
 */
function setCheckBoxSuite(id)
{
   checkboxes = $('#tests_' + id + ' :checkbox');
   var checked = false;
   for (var i = 0; i < checkboxes.length; i++) {
        if(checkboxes[i].checked) {
            checked = true;
            break;
        }
    }
    setCheckBox(id, checked);
}

/**
 * Checks or uncheckes all checkboxes in the specified group (of suites or tests).
 *
 * @param string group the id of the group
 * @param boolean value true or false
 */
function setCheckBoxes(id, val)
{
    checkboxes = $('#' + id + ' :checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        setCheckBox(checkboxes[i].id, val);
    }
}

/**
 * Toggles all tests in a suite on or off.
 *
 * @param string id suite id
 * @param boolean val
 */
function toggleAllTests(id, val)
{
    setCheckBoxes('tests_' + id, val);
    setCheckBox(id, val);
}

/**
 * Controls for a suite.
 *
 * @param string suite name
 * @param boolean run only/exlude suite
 * @param string testCase used to run a single test case
 * @param boolean generate code coverage
 */
function controlSuite(suite, val, testCase, coverage)
{
    // run only
    if (val == true)
        setCheckBoxes('menuForm', false); // uncheck all checkboxes
    if (testCase != null) {
        setCheckBox(testCase, true);
        setCheckBox(suite, true);
    } else {
        toggleAllTests(suite, val);
    }
    if (coverage == true)
        document.getElementById('coverage').value = "true";
    runTests();
    document.getElementById('coverage').value = "false";
}

/**
 * Shows or hides a suite result
 *
 * @param string name of the suite
 */
function toggleSuite(name)
{
    var a = document.getElementById('a_' + name);
    if (typeof(a) != 'object')
        return;
    var tests = document.getElementById('suite_' + name);
    if (typeof(tests) != 'object')
        return;
    var hidden = document.getElementById('keepOpen');
    if (typeof(hidden) != 'object' || !(hidden.tagName == 'INPUT' || hidden.tagName == 'input')) {
        return;
    }
    var keepOpen = hidden.value.split(' ');
    if (a.innerHTML.valueOf() == '+') {
        tests.style.display = 'block';
        a.innerHTML = '-';
        keepOpen[keepOpen.length] = name;
    } else {
        tests.style.display = 'none';
        a.innerHTML = '+';
        var tmp_array = new Array();
        for (i in keepOpen) {
            if (keepOpen[i].valueOf() == name || keepOpen[i].valueOf() == '')
                continue;
            tmp_array[i] = keepOpen[i];
        }
        keepOpen = tmp_array;
    }
    hidden.value = keepOpen.join(' ');
}

/*
 * Shows or hides the tests for a suite.
 *
 * @param string suite name
 * @param event object
 * @param boolean show
 */
function toggleTests(name, event, show)
{
	var arrows = $('.arrow_suite');
	if (arrows.length == 0){
		var arrows = $('.arrow_suite');
	}
	arrows.attr('src', 'images/collapsed.png');
	var length = arrows.length;
	
	var testsDiv = $('#tests_'+name)
	if (show){
		var arrows = $('.arrow_suite');
		var length = arrows.length;
		for(i = 0; i< length; i++){
			$('#tests_'+name).hide(2);
		}
		testsDiv.show(2);
	}else{
		var mouseX = event.clientX;
		var mouseY = event.clientY;
		
		var position  = testsDiv.position();
		var maxY = position.top + testsDiv.height();
		var maxX = position.left + testsDiv.width();
		
/*		alert('x = '+ mouseX + ' y='+ mouseY+"\n" + 
				"x1 ="+position.left + ' y1 =' + position.top +"\n"+
				"x2 = "+ maxX + " y2 = "+ maxY +" \n"+
				"width = "+$('#tests_'+name).width() + "heigth = " + $('#tests_'+name).height());
				*/
		var divSelector = $('#suiteSelect_'+name);
		var divPosition = divSelector.position();
		var divWidth = divSelector.width();
		var divHeight = divSelector.height();
		
		if (
				mouseX < divPosition.left || mouseX > maxX ||//conditii pe orizontala
				( 
					(mouseX > ( divPosition.left+10 ) && mouseX < position.left) &&// e pe div-ul de selector
					( 
						( mouseY > (divPosition.top + divHeight + 10)) ||
						( mouseY < (divPosition.top + 10) )
					) // adica e in afara div-ului pe verticala
				) ||
				(
					(mouseX > position.left && mouseX < maxX) &&
					(
						mouseY > maxY || mouseY < position.top
					)
				)
			){
				/*$('#teste').show();
				$('#teste').html('MouseX: ' + mouseX + "<br>" +
								 "MouseY: " + mouseY + "<br>" +
								 "divCuTeste:<br/>&nbsp;&nbsp;&nbsp;" +
								 "X: " + position.left + "<br/>&nbsp;&nbsp;&nbsp;" + 
								 "Y: " + position.top + "<br/>&nbsp;&nbsp;&nbsp;" +
								 "MaxX: " + maxX + "<br/>&nbsp;&nbsp;&nbsp;" + 
								 "MaxY: " + maxY + "<br>" +
								 "divSelector:<br/>&nbsp;&nbsp;&nbsp;" +
								 "X: " + divPosition.left + "<br/>&nbsp;&nbsp;&nbsp;" + 
								 "Y: " + divPosition.top + "<br/>&nbsp;&nbsp;&nbsp;" +
								 "MaxX: " + (divPosition.left+divWidth+10) + "<br/>&nbsp;&nbsp;&nbsp;" + 
								 "MaxY: " + (divPosition.top+divHeight+10) + "<br/>&nbsp;&nbsp;&nbsp;");
			 	/**/
				$('#tests_'+name).hide('fast');
		}else{
			$('#tests_'+name).show(2);
		}
		//
	}
}

/**
 * Resets the tester.
 */
function resetTester()
{
    document.getElementById('reset').value = "true";
    document.getElementById('menuForm').submit();
}

/**
 * Starts tests run.
 */
function runTests()
{
    if(useAjax) {
        // show loader
        document.getElementById('status').style.display = 'block';
        document.getElementById('suites').innerHTML = '';
        document.getElementById('runnedTest').innerHTML = '';

        stopClicked = false;
        var query = 'index.php?action=START';
        $('#runnedTest').load(query, $('#menuForm').serializeArray(),
            function (responseText, textStatus, XMLHttpRequest) {
                if(responseText.length) {
                    stopTests();
                } else {
                    continueTests();
                }
            }
        );
    } else {
        document.getElementById('menuForm').submit();
    }
}

/**
 * Continues tests run.
 */
function continueTests()
{
    var query = 'index.php?action=CONTINUE';
    // errorCode is defined in the script at the beginning of the page
    $('#buffer').load(query, null,
        function (responseText, textStatus, XMLHttpRequest) {
            if(!stopClicked) {
                eval(responseText);
            } else {
                stopClicked = false;
                stopTests();
            }
        }
    );
}

/**
 * Reset loader components.
 */
function resetLoader()
{
    // reset loader
    var loader = document.getElementById('loader');
    loader.style.width = '0px';
    loader.innerHTML = '';

    var runnedTest = document.getElementById('runnedTest');
    runnedTest.innerHTML = '';

    var status = document.getElementById('status');
    status.style.display = 'none';
}

/**
 * Stop tests run and display results.
 */
function stopTests()
{
    resetLoader();

    var query = 'index.php?action=STOP';
    $('#suites').load(query, null, null);
}

/**
 * Sets the stop button clicked to true.
 */
function forceStop()
{
    stopClicked = true;
}

function loadCodeCoverage(url){
	$('#codeCoverage').hide('slow');
	
	$('#subCodeCoverage').html('<center> Loading...</center>');
	$('#codeCoverage').show('slow');
	$.post(url,'', function(data){
		$('#subCodeCoverage').show();
		var position = data.search('<center>');
		var scriptStartPosition = data.search(/\/<!\[CDATA\[/);
		var scriptStopPosition = data.search(' //]]>');
		var javaScriptPiece = data.slice(scriptStartPosition+12, scriptStopPosition);
		javaScriptPiece = javaScriptPiece + "\n" + "init()";
		//alert ("start = " + scriptStartPosition + "stop = " + scriptStopPosition);
		//position = 0;
		data = data.slice(position);
		
		$('#subCodeCoverage').html(data);
		eval(javaScriptPiece);
	  },'text');
	
//	$('#codeCoverage').load(url);
}

function closeCoverage(){
	$('#codeCoverage').hide('slow');
	$('#subCodeCoverage').html('');
}
$(document).ready(function(){
	var arrows = $('.arrow_suite');
	arrows.attr('src', 'images/collapsed.png');
	var length = arrows.length;
	for(var i = 0; i< length; i++){
		id = arrows[i].id;
		var name = id.split('_')[1];
		var top = arrows[i].offsetTop;
		var left = arrows[i].offsetLeft + 20;
		$('#tests_'+name).css('top', top+'px').css('left', left+'px');
	}
});