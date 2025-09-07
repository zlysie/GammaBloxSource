
<?php
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	UserUtils::LockOutUserIfNotLoggedIn();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>ROBLOX Error Reporting</title>
    <link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.css" />
</head>
<body style="background-color: ButtonFace">
    <form name="aspnetForm" method="post" action="ReportDialog.aspx" id="aspnetForm">

<script type="text/javascript">
//<![CDATA[
var theForm = document.forms['aspnetForm'];
if (!theForm) {
    theForm = document.aspnetForm;
}
function __doPostBack(eventTarget, eventArgument) {
    if (!theForm.onsubmit || (theForm.onsubmit() != false)) {
        theForm.__EVENTTARGET.value = eventTarget;
        theForm.__EVENTARGUMENT.value = eventArgument;
        theForm.submit();
    }
}
//]]>
</script>
    <div style="margin:12px">
        

<div id="ErrorReporting" class="ErrorReporting" style="display: none">
    <p>
        ROBLOX crashed on your computer recently! We would like to find out why so that
        we can make ROBLOX better.
    </p>
    <p>
        Help us fix bugs by clicking Send Error Report</p>
    <div class="YesNoButtons">
        <input name="ctl00$ContentPlaceHolder1$ErrorReporting1$ButtonYes" type="button" id="ctl00_ContentPlaceHolder1_ErrorReporting1_ButtonYes" class="YesButton" value="Send Error Report" onclick="sendRobloxErrorFiles(); return false" />
        <input id="ButtonNo" class="NoButton" type="button" value="Don't Send" onclick="dontSendRobloxErrorFiles(); return false" />
    </div>
</div>
<div id="ErrorReportingThanks" class="ErrorReporting" style="display: none">
    <p>
        Your ROBLOX crashed recently. An error report is being sent to ROBLOX.
    </p>
    <p>
        This operation will take a few minutes, but you may continue doing other things.
    </p>
    <div class="YesNoButtons">
        <input id="Button2" class="NoButton" type="button" value="Cancel" onclick="dontSendRobloxErrorFiles(); return false" />
    </div>
</div>

<script type="text/javascript">
        var robloxAppReport;
        
        function dontSendRobloxErrorFiles()
        {
	        robloxAppReport.DeleteErrorFiles();
	        window.close();
        }
        
        function sendRobloxErrorFiles()
        {
	        robloxAppReport.SendErrorFiles("http://gamma.lambda.cam/Error/Dmp.ashx");
	        window.close();
        }
                
        function updateRobloxError()
        {
            var er = robloxAppReport.ErrorReporting;
            if (er==0)
            {
                // Skip error reporting
                return false;
            }
            if (robloxAppReport.HasErrorFiles)
            {
                if (er==1)
                    document.getElementById("ErrorReporting").style.display = "block";
                else if (er==2)
                    sendRobloxErrorFiles();
            }
            else
            {
	            document.getElementById("ErrorReporting").style.display = "none";
            }
            
            //document.getElementById("ErrorReportingThanks").style.display = robloxAppReport.IsUploadingErrorFiles ? "block" : "none";
            
            return true;
        }

        function updateRobloxErrorTimer()
        {
            if (updateRobloxError())
                window.setTimeout("updateRobloxErrorTimer()", 1000);
        }
        
        try {
            robloxAppReport = new ActiveXObject("Roblox.App");
            robloxAppReport.IsUploadingErrorFiles;  // Quick check that the API exists
	        window.setTimeout("updateRobloxErrorTimer()", 100);
        }  catch(e) {}
</script>

    </div>
    
<div>
	<input type="hidden" name="__EVENTTARGET" id="__EVENTTARGET" value="" />
	<input type="hidden" name="__EVENTARGUMENT" id="__EVENTARGUMENT" value="" />
</div>
</form>
</body>
</html>
