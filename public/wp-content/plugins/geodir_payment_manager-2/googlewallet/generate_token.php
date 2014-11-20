<?php
/**
*  This code is generating JWT of a product.
*
* @copyright 2013  Google Inc. All rights reserved.
* @author Rohit Panwar <panwar@google.com>
*/

/**
 * JWT class to encode/decode payload into JWT format.
 */
include_once "JWT.php";

/**
 * Get payload of the product.
 */
include_once "payload.php";
$user_id = get_current_user_id();
$sellerIdentifier =  $merchantid;
$sellerSecretKey = $merchantkey;

$payload = new Payload();
$payload->SetIssuedAt(time());
$payload->SetExpiration(time()+3600);
$payload->AddProperty("name", "$post_title");
$payload->AddProperty("description","$post_title");
$payload->AddProperty("price", "$payable_amount");
$payload->AddProperty("currencyCode", "$currency_code");
$payload->AddProperty("sellerData","user_id:$user_id,post_id:$last_postid");

// Creating payload of the product.
$Token = $payload->CreatePayload($sellerIdentifier);

// Encoding payload into JWT format.
$jwtToken = JWT::encode($Token, $sellerSecretKey);
