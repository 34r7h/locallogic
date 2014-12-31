
<table class="table" style="margin-left:50px; margin-top:10px; display:none;" id="authorizenetoptions"  >
    <tr>
		<td>
		<div class="geodir_form_row clearfix">
      <label style="width:150px;"><?php _e('Card Holder Name :', GEODIRPAYMENT_TEXTDOMAIN);?></label>
				<input type="text" value="" size="25" id="cardholder_name" name="cardholder_name" class="textfield"/>
			</div>
			</td>
    </tr>
    <tr>
      <td class="row3">
			<div class="geodir_form_row clearfix">
			<label style="width:150px;"><?php _e('Card Type :', GEODIRPAYMENT_TEXTDOMAIN);?> </label>
					<select style="width:205px;" class="select_s" id="cc_type" name="cc_type" >
          <option value=""><?php _e('-- select card type --', GEODIRPAYMENT_TEXTDOMAIN);?></option>
          <option value="VISA"><?php _e('Visa', GEODIRPAYMENT_TEXTDOMAIN);?></option>
          <option value="DELTA"><?php _e('Visa Delta', GEODIRPAYMENT_TEXTDOMAIN);?></option>
          <option value="UKE"><?php _e('Visa Electron', GEODIRPAYMENT_TEXTDOMAIN);?></option>
          <option value="MC"><?php _e('Master Card', GEODIRPAYMENT_TEXTDOMAIN);?></option>
        </select>
				</div>
      </td>
    </tr>
    <tr>
      <td class="row3">
			<div class="geodir_form_row clearfix">
			<label style="width:150px;"><?php _e('Credit/Debit Card number :', GEODIRPAYMENT_TEXTDOMAIN);?></label>
			<input type="text" autocomplete="off" size="25" maxlength="25" id="cc_number" name="cc_number" class="textfield"/>
				</div>
				</td>
    </tr>
    <tr>
      <td class="row3">
      
			<div class="geodir_form_row clearfix">
			<label style="width:150px;"><?php _e('Expiry Date :', GEODIRPAYMENT_TEXTDOMAIN);?> </label>
			<select  class="select_s2" style="width:100px;" id="cc_month" name="cc_month">
          <option selected="selected" value=""><?php _e('month', GEODIRPAYMENT_TEXTDOMAIN);?></option>
          <option value="01">01</option>
          <option value="02">02</option>
          <option value="03">03</option>
          <option value="04">04</option>
          <option value="05">05</option>
          <option value="06">06</option>
          <option value="07">07</option>
          <option value="08">08</option>
          <option value="09">09</option>
          <option value="10">10</option>
          <option value="11">11</option>
          <option value="12">12</option>
        </select>
				
        <select class="select_s2" style="width:100px;" id="cc_year" name="cc_year">
          <option selected="selected" value="">year</option>
          <?php for($y=date('Y');$y<date('Y')+20;$y++){?>
          <option value="<?php echo $y;?>"><?php echo $y;?></option>
          <?php }?>
        </select></div>
      </td>
    </tr>
    <tr>
      <td class="row3">
			<div class="geodir_form_row clearfix">
			<label style="width:150px;"><?php _e('CV2 (3 digit security code) :', GEODIRPAYMENT_TEXTDOMAIN);?></label>
			<input type="text" style="width:85px;" autocomplete="off" size="4" maxlength="5" id="cv2" name="cv2" class="textfield2"/>
			</div>
			</td>
    </tr>
  </table>