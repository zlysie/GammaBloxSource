if(!String.prototype.trim) { //fix for ie
	String.prototype.trim = function() {
    	return this.replace(/^\s+|\s+$/g, '');
	}
}

var msgsPerPage = 20;           // The number of messages shown per page

$(function () {
	changeTab();
});


// Change style of tab header to reflect new view, and load messages in new view
function changeTab() {
	var pagenum = 1;
	// Clear out messages from previous view
	$('#Processing').attr("style", "text-align: center; vertical-align: middle");
	if (document.getElementById("MessageTable") != null)
		document.getElementById("Messages").removeChild(document.getElementById("MessageTable"));
	document.getElementById("EmptyInbox").style.display = "none";
	document.getElementById("pager").innerHTML = "";
	document.getElementById("pager").style.display = "none";
	document.getElementById("selectAll").checked = false;

	// Fetch messages
	$.ajax({
		url: "MessagesHandler.ashx?page=" + pagenum + "&msgsPerPage=" + msgsPerPage + "&type=GetInbox",
		cache: false,
		dataType: "json",
		success: function (msgs) {
			showMessages(msgs, pagenum);
		}
	});
}


// Populate messages
function showMessages(msgs, pagenum) {
	// Copy table to be filled
	var table = document.getElementById("tableTemplate");
	var newTable = table.cloneNode(true);
	newTable.id = "MessageTable";

	// If there are no messages, let the user know
	if (msgs.Messages[0] == null) {
		document.getElementById("tableHeader").style.display = "none";
		document.getElementById("messageButton").style.display = "none";
		document.getElementById('Processing').style.display = "none";
		document.getElementById("EmptyInbox").innerHTML = "You have no messages in your inbox.";
		document.getElementById("EmptyInbox").style.display = "block";
	}

	else {
		for (i = 0; i < pagenum * msgsPerPage; i++) {
			// Get message information
			if (msgs.Messages[i] == null)
				break;
			var from = msgs.Messages[i][0];
			var subject = msgs.Messages[i][1];
			if (subject.trim().length == 0)
				subject = "[none]";
			var date = msgs.Messages[i][2];
			var id = msgs.Messages[i][3];
			var authorid = msgs.Messages[i][4];
			var unread = msgs.Messages[i][5];
			var tooltip = "Visit " + from + "'s Profile";
			if (from == "GAMMA [System Message]") {
				var rowClass = "SystemAlertMessage";
				if (unread == "true")
					var rowClass = "SystemAlertMessage_Unread";
				tooltip = from;
			}
			else {
				var rowClass = "InboxRow";
				if (unread == "true")
					var rowClass = "InboxRow_Unread";
			}

			// Create new row in table
			var newRow = document.getElementById("trTemplate").cloneNode(true);
			newRow.id = id;
			newRow.style.display = "";
			newRow.className = rowClass;
			var cols = newRow.getElementsByTagName("td");
			var checkbox = cols[0].getElementsByTagName("input")[0];
			checkbox.className += "Inbox";
			var authorcol = cols[1].getElementsByTagName("a")[0];
			if (from == "GAMMA [System Message]")
				authorcol.href = "#";
			else
				authorcol.href = "/User.aspx?ID=" + authorid;
			authorcol.title = tooltip;
			var subjectcol = cols[2].getElementsByTagName("a")[0];
			subjectcol.href = "PrivateMessage.aspx?MessageID=" + id;
			var datecol = cols[3].getElementsByTagName("a")[0];
			if (typeof (document.getElementById('trTemplate').text) != 'undefined') {
				authorcol.text = from;
				subjectcol.text = subject;
				datecol.text = date;
			}
			else if (typeof (document.getElementById('trTemplate').textContent) != 'undefined') {
				authorcol.textContent = from;
				subjectcol.textContent = subject;
				datecol.textContent = date;
			}
			else if (typeof (document.getElementById('trTemplate').innerText) != 'undefined') {
				authorcol.innerText = from;
				subjectcol.innerText = subject;
				datecol.innerText = date;
			}
			cols[3].style.width = "180px";

			newTable.getElementsByTagName('tbody')[0].appendChild(newRow);
		}
		document.getElementById('Messages').appendChild(newTable);

		// Update button text and populate pager if necessary
		var beginPages = 1;
		var tooManyMsgsRight = false;
		var tooManyMsgsLeft = false;
		document.getElementById('messageButton').innerHTML = "Delete";
		if (msgs.numMessages > msgsPerPage) {
			var endPages = Math.ceil(msgs.numMessages / msgsPerPage);
			if (endPages > Math.ceil(pagenum / 10) * 10) {
				endPages = Math.ceil(pagenum / 10) * 10;
				tooManyMsgsRight = true;
			}
			if ((Math.ceil((pagenum - 10) / 10) * 10) > 0) {
				if (beginPages < Math.ceil((pagenum - 10) / 10) * 10) {
					beginPages = Math.ceil((pagenum - 10) / 10) * 10 + 1;
					tooManyMsgsLeft = true;
				}
			}
		}
		if (tooManyMsgsLeft)
			document.getElementById("pager").innerHTML += '<a onclick="changePage(' + (beginPages - 1) + ')" style="padding: 1px 1px 1px 1px; cursor: pointer">...</a>';
		for (j = beginPages; j <= endPages; j++) {
			if (j == pagenum)
				document.getElementById("pager").innerHTML += '<span style="padding: 1px 1px 1px 1px">' + j + '</span>';
			else
				document.getElementById("pager").innerHTML += '<a onclick="changePage(' + j + ')" style="padding: 1px 1px 1px 1px; cursor: pointer">' + j + '</a>';
		}
		if (tooManyMsgsRight)
			document.getElementById("pager").innerHTML += '<a onclick="changePage(' + (endPages + 1) + ')" style="padding: 1px 1px 1px 1px; cursor: pointer">...</a>';
		document.getElementById("pager").style.display = "";

		// Update header and button to reflect new view
		document.getElementById('messageButton').style.display = "";
		document.getElementById('tableHeader').style.display = "";
		document.getElementById('DateCol').style.display = "";
		document.getElementById('DateCol').style.width = "180px";
		document.getElementById('Processing').style.display = "none";
	}
}

// Select proper checkboxes when "selectAll" box is clicked
function selectAllCheckboxesFn() {
	var checked = document.getElementById('selectAll').checked;
	var checkboxes = $('.checkboxInbox');
	for (var i = 0; i < checkboxes.length; i++) {
		checkboxes[i].checked = checked;
	}
}


// Archive or unarchive selected messages
function deleteMessages() {
	// Determine appropriate action to take
	var checkboxes = $('.checkboxInbox');
	var type = "DeleteButton";

	// Gather IDs of selected messages
	var selected = new Array();
	var j = 0;
	for (var i = 0; i < checkboxes.length; i++) {
		if (checkboxes[i].checked) {
			selected[j] = checkboxes[i].parentNode.parentNode.id;
			j++;
		}
	}

	if (selected.length > 0) {
		// Archive/unarchive messages and update view to reflect changes
		var ids = selected.toString();
		var pagenum = 1;

		// Clear out messages from previous view
		$('#Processing').attr("style", "text-align: center; vertical-align: middle");
		if (document.getElementById("MessageTable") != null)
			document.getElementById("Messages").removeChild(document.getElementById("MessageTable"));
		document.getElementById("EmptyInbox").style.display = "none";
		document.getElementById("pager").innerHTML = "";
		document.getElementById("pager").style.display = "none";
		document.getElementById("selectAll").checked = false;

		// Fetch messages
		$.ajax({
			url: "MessagesHandler.ashx?page=" + pagenum + "&msgsPerPage=" + msgsPerPage + "&selected=" + selected + "&type=" + type,
			cache: false,
			dataType: "json",
			success: function (msgs) {
				showMessages(msgs, pagenum);
			}
		});
	}
}


// Change the currently viewed page of messages
function changePage(newPage) {
	// Clear out messages from previous view
	$('#Processing').attr("style", "text-align: center; vertical-align: middle");
	if (document.getElementById("MessageTable") != null)
		document.getElementById("Messages").removeChild(document.getElementById("MessageTable"));
	document.getElementById("EmptyInbox").style.display = "none";
	document.getElementById("pager").innerHTML = "";
	document.getElementById("pager").style.display = "none";
	document.getElementById("selectAll").checked = false;

	// Fetch messages
	$.ajax({
		url: "MessagesHandler.ashx?page=" + newPage + "&msgsPerPage=" + msgsPerPage + "&type=GetInbox",
		cache: false,
		dataType: "json",
		success: function (msgs) {
			showMessages(msgs, newPage);
		}
	});
}