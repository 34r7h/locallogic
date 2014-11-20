jQuery(document).ready( function() {
		document.getElementById('log').innerHTML = '<p style="margin:15px;color:red;">NO LOGS YET NOW.</p>';
		var current_module =  document.getElementById('current_step').value;
		if(current_module == 'content_mapping') {
		content_mapping();
		}

		});

function goto_mapping(id){
	if(id == 'importfile'){
		var currentURL = document.URL; 
		var go_to_url = currentURL.replace("uploadfile","mapping_settings");
		window.location.assign(go_to_url);
		document.getElementById('sec-one').style.display='none';
		document.getElementById('sec-two').style.display='';
	}
}
function show_user_form(value) {
	if(value == 'emailuser') {
		document.getElementById('createuser').style.display = '';
	}
	else {
		document.getElementById('createuser').style.display = 'none';

	}
}

function debugoption()
{
	var debugname = document.getElementById('debug_mode').value;
	var postdata = new Array();
	var postdata = debugname;
		jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
		'action'   : 'enable_debug_option',
		'postdata' : postdata,
		},
		success:function(data) {
	//		alert(data);
		},
		error: function(errorThrown){
		console.log(errorThrown);
		       }
		});

}


function user_mapping() {
	var simple_user = document.getElementById('simple').checked;
	var adv_user    = document.getElementById('adv').checked;
	var siteuser    = document.getElementById('siteuser').checked;
	var xmluser    = document.getElementById('xmluser').checked;
	var emailuser    = document.getElementById('emailuser').checked;
	if(simple_user == true) {
		var user = 'impall';
		var type = 'simple';
	}
	else if(adv_user == true) {
		if(siteuser == true) {
			var user_val = document.getElementById('site_user').value;
			if(user_val == 'select') {
				showMapMessages('error','Kindly select user from the list');
				return false;
			}
			else {
				var user = user_val;
			}
		}
		else if(xmluser == true) {
			var xml_val = document.getElementById('xml_user').value;
			if(xml_val == 'select') {
				showMapMessages('error','Kindly select user from the list');
				return false;
			}
			else {
				var user = xml_val;
			}
		}
		else if(emailuser == true) {
			var email_name = document.getElementById('new_user_name').value;
			var user_email = document.getElementById('emailuser').value;

			if( (empty(user_val)) || (empty(user_email)))  {
				showMapMessages('error','Kindly Provide the User Email / Name ');
				return false;
			}
			else {
				var user = email_name+'|'+user_email;
			}
		}


	} 
}
function media_check() {
	var url = document.getElementById('ex_attachment').checked;
	var zip = document.getElementById('zip_upload').checked;
	if((url == false) && (zip == false)) {
		showMapMessages('error','Kindly select the attachment ');
		return false;
	}
}
function user_extension(val) {
	var user = val;

}
function check_allnumeric(inputtxt) {
	var numbers = /^[0-9]+$/;
	if (inputtxt.match(numbers)) {
		document.getElementById('importtype').disabled = false;
	}
	else {
		if (inputtxt == '')
			alert('Fill all mandatory fields.');
		else
			alert('Please enter numeric characters only');
	}

}


function content_mapping(id)  {
	document.getElementById('module').value = content_mapping;
	if(id == 'all') {
		document.getElementById('posts').checked = true;
		document.getElementById('pages').checked = true;
		document.getElementById('customposts').checked = true;
	}
	document.getElementById('edit_mapping').innerHTML = '<option value = "select"> -- select -- </option>';
	if(jQuery('#posts').prop('checked')) {
		var posts =  document.getElementById('posts').value; 
	} else { var posts = 'NULL'; }
	if(jQuery('#pages').prop('checked')) {
		var pages = document.getElementById('pages').value;
	} else { var pages = 'NULL'; }
	if(jQuery('#customposts').prop('checked')) {
		var custom = document.getElementById('customposts').value;
	} else { var custom = 'NULL'; }

	var postdata = new Array( {'post':posts,'page':pages,'custom':custom } );

	jQuery.ajax({
type: 'POST',
url: ajaxurl,
data: {
'action'   : 'choose_post_types',
'postdata' : postdata,
},
success:function(data) {
var i = '';
var data = JSON.parse(data); 
var len = data.length;
for(i = 0; i<len; i++) {
if( (data[i] != 'attachment') ) {
document.getElementById('edit_mapping').innerHTML += '<option value = '+data[i]+'>'+data[i]+'</option>';
}
}  
return false;

},
error: function(errorThrown){
console.log(errorThrown);
       }
});


}

function check_mapping(id) {
	var cal = document.getElementsByTagName('input');
	for (var i = 0; i < cal.length; i++) {
		if (cal[i].type == 'checkbox') {
			if (cal[i].name == 'map_arr[]') {
				cal[i].checked = true;
			}
		}
	}


}
function dwnld_attachment(id) {
	if(id == 'zip_upload') {
		document.getElementById('adv_media').style.display = '';
	}
	else {
		document.getElementById('adv_media').style.display = "none";

	} 
}

function uncheck_mapping(id) {
	var cbs = document.getElementsByTagName('input');
	for (var i = 0; i < cbs.length; i++) {
		if (cbs[i].type == 'checkbox') {
			if (cbs[i].name == 'map_arr[]') {
				cbs[i].checked = false;
			}
		}
	}
}
function save_mapping() {
	var my_arr = new Array();
	var post_type = document.getElementById('save_type').value;
	var map_arr = document.getElementsByTagName('input');
	for (var i = 0; i < map_arr.length; i++) {
		if (map_arr[i].type == 'checkbox') {
			if (map_arr[i].name == 'map_arr[]') {
				if( map_arr[i].checked == false) {
					var excl = map_arr[i].value;
					var my_arr     = my_arr.concat(excl);
				}
			}
		}
	}
	var postdata = new Array( {'excludeArray':my_arr,'post_type':post_type } );

	jQuery.ajax({
type: 'POST',
url: ajaxurl,
data: {
'action'   : 'save_mapping',
'postdata' : postdata,
},
success:function(data) {
return false;
},
error: function(errorThrown){
console.log(errorThrown);
}
});

}
function view_mapping(val) {
	var ptype = val;
	var postdata = new Array( {'post_type':ptype } );

	jQuery.ajax({
type: 'POST',
url: ajaxurl,
data: {
'action'   : 'view_mapping',
'postdata' : postdata,
},
success:function(data) {
var view = JSON.parse(data); 
document.getElementById('view').innerHTML = view;
},
error: function(errorThrown){
console.log(errorThrown);
}
});

}
function selecttype(id)
{
	var file_name = document.getElementById('uploadFileName').value;
	if(file_name.length != 0) { 
		if(jQuery('#typepost').prop('checked') || jQuery('#typepage').prop('checked') || jQuery('#typecustom').prop('checked'))
		{
			jQuery('#importtype').removeAttr('disabled');
		}
	}
	else
	{
		jQuery('#importtype').attr('disabled','disabled');
	}
}
function choosetype(form)
{
	var type = document.getElementByName('type').value;
	alert(form);
}

function shownotification(msg, alerts) {
	var newclass;
	var divid = "notification_wp_csv";

	if(alerts == 'success')
		newclass = "alert alert-success";
	else if(alerts == 'danger')
		newclass = "alert alert-danger";
	else if(alerts == 'warning')
		newclass = "alert alert-warning";
	else
		newclass = "alert alert-info";

	jQuery('#'+divid).removeClass()
		jQuery('#'+divid).html(msg);
	jQuery('#'+divid).addClass(newclass);
	// Scroll
	jQuery('html,body').animate({
scrollTop: jQuery("#"+divid).offset().top},
'slow'); 
}


function showMapMessages(alerttype, msg) {
	jQuery("#showMsg").addClass("maperror");
	document.getElementById('showMsg').innerHTML = msg;
	document.getElementById('showMsg').className += ' ' + alerttype;
	document.getElementById('showMsg').style.display = '';
	jQuery("#showMsg").fadeOut(10000);
}

function show_user(val) {
	if(val == 'adv') {
		document.getElementById('adv_user').style.display = '';
	}
	else {
		document.getElementById('adv_user').style.display = 'none';

	}

}

function importRecordsbySettings() {
	var get_file = document.getElementById('file_name').value;
	var get_log = document.getElementById('log').innerHTML;
	var postcount = document.getElementById('post_cnt').value;
	var authorcount = document.getElementById('auth_cnt').value;
	var total = parseInt(postcount) + 1;
	var ex_user = document.getElementById('ex_user').value;
	var user_type = document.getElementById('user_type').value;
	var tmpCnt = document.getElementById('tmpcnt').value;
	var currentlimit = document.getElementById('currentlimit').value;
	var authcnt = document.getElementById('authcnt').value;
	var implimit = document.getElementById('implimit').value;
	var attach = document.getElementById('attach').value;
	var duptitle = document.getElementById('duptitle').checked;
	var dupcontent = document.getElementById('dupcontent').checked;
	if(get_log == '<p style="margin:15px;color:red;">NO LOGS YET NOW.</p>'){
		document.getElementById('log').innerHTML = '<p style="margin:15px;color:red;">Your Import Is In Progress...</p>';
	}
	document.getElementById('ajaxloader').style.display="";
	var tempCount = parseInt(tmpCnt);
	var totalCount = parseInt(total);
	if(tempCount>totalCount){
		document.getElementById('ajaxloader').style.display="none";
		return false;
	}

	var postdata = new Array( {'get_file':get_file,'postcount':postcount,'authorcount':authorcount,'total':total,'ex_user':ex_user,'authcnt':authcnt,'implimit':implimit,'attach':attach,'duptitle':duptitle,'dupcontent':dupcontent,'type':user_type} );


	jQuery.ajax({
type: 'POST',
url: ajaxurl,
data: {
'action'   : 'importXmlRequest',
'postdata' : postdata,
//	    'siteurl'  : siteurl
},
success:function(data) {

if (parseInt(tmpCnt) < parseInt(total) ) {
currentlimit = parseInt(currentlimit) + parseInt(tmpCnt);
document.getElementById('currentlimit').value = currentlimit;
console.log('impLmt: ' + tmpCnt + 'totRecds: ' + total);
document.getElementById('tmpcnt').value = parseInt(tmpCnt) + 1 ;
document.getElementById('implimit').value = parseInt(implimit) + 1 ;
setTimeout(function () { importRecordsbySettings() }, 0);
}
else if(parseInt(tmpCnt) == parseInt(total) ){
document.getElementById('importtype').disabled= true;
document.getElementById('ajaxloader').style.display="none";
document.getElementById('log').innerHTML += data+ "<br/>"+"<p style = 'color:green'> Your Import has been Completed . </p>";
return false;
}
document.getElementById('authcnt').value = 0 ;
document.getElementById('log').innerHTML += data+'<br/>';

},
error: function(errorThrown){
	       console.log(errorThrown);
       }
});
}


function Reload(){
	window.location.reload();
}


function inArray(needle, haystack) {
	var length = haystack.length;
	for(var i = 0; i < length; i++) {
		if(typeof haystack[i] == 'object') {
			if(arrayCompare(haystack[i], needle)) {
				return true;
			}
		} else {
			if(haystack[i] == needle) {
				return true;
			}
		}
	}
	return false;
}

function import_again(){
	var get_current_url = document.getElementById('current_url').value;
	window.location.assign(get_current_url);
}
function sendemail2smackers(){
	//	var useremail = document.getElementById('usermailid').value;
	var message_content = document.getElementById('message').value;
	var firstname = document.getElementById('firstname').value;
	var lastname = document.getElementById('lastname').value;
	if(message_content != '' && firstname != '' && lastname != '')
		return true;
	else
		document.getElementById('showMsg').style.display = '';
	document.getElementById('showMsg').innerHTML = '<p id="warning-msg" class="alert alert-warning">Fill all mandatory fields.</p>';
	jQuery("#showMsg").fadeOut(10000);
	return false;
}

