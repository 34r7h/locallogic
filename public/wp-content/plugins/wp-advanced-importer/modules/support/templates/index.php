<?php
$impCE = new WPAdvImporter_includes_helper(); 
?>
<div style="width:100%;">
<div class= "contactus" id="contactus" style="width:98%">
<div class="accordion-group" >
<div class="accordion-heading">
<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo" id="headeralign"> CONTACT US </a>
</div>
<div class="accordion-body in collapse">
<div class="accordion-inner">
<form action='<?php echo admin_url().'admin.php?page='.WP_CONST_ADVANCED_XML_IMP_SLUG.'/index.php&__module='.$_REQUEST['__module'].'&step=sendmail2smackers'?>' id='send_mail' method='post' name='send_mail' onsubmit="return sendemail2smackers();" >
<table class="tablealign">
<tr>
<td id="textalign">First name <span class="mandatory">*</span></td><td id="optiontext"><input type="text" id="firstname" placeholder="First name" name="firstname" /></td>
<td id="textalign">Last name <span class="mandatory">*</span></td><td id="optiontext"><input type="text" id="lastname" placeholder="Last name" name="lastname" />
<input type="hidden" id="smackmailid" name="smackmailid" value="info@smackcoders.com" />
</td>

<!--<tr>
<td>From <span class="mandatory">*</span></td><td><input type="email" id="usermailid" placeholder="sample@gmail.com" name="usermailid" /></td>
<td></td><td></td>
</tr> -->
<td id="textalign">Related To</td>
<td>
<select name="subject" id="optiontext">
<option>Support</option>
<option>Feature Request</option>
<option>Customization</option>
</select>
</td>
</tr>
<tr>
<td id="textalign">Message <span class="mandatory">*</span></td>
<td colspan=5 style="padding-top:22px" id="optiontext">
<textarea class="form-control" rows="3" name="message" id="message"></textarea>
</td>
</tr>
</table>
<div style="float:right;padding:10px;"><input class="btn btn-primary" type="submit" name="send_mail" /></div>
</form>
</div>
</div>
</div>
</div>

<?php
 /* Put your code here */ 

 ?>
