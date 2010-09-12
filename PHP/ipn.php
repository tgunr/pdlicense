<?php
error_reporting(E_ALL ^ E_NOTICE); 
	
$abs_path = dirname(__FILE__);
//include $abs_path."../../../global.php";
//require_once $abs_path."/aqprime_user/PHP/Config.php";
require_once $abs_path."/aqprime_user/PHP/AquaticPrime.php";
// require_once $abs_path."/aqprime_user/PHP/AquaticPrimePayPal.php";
$admin_email = "davec@polymicro.net"; 
$dcmac = "davec@mac.com";
$dcpms = "davec@polymicrosystems.com";
$pms_email = "davec@polymicrosystems.com";
$pms_merchantid = "XCUQDTBS2G6G2";
$pms_notify_version = "3.0";
	
if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$DATA =$_GET;
	$test=1;
	echo "<p>Testing";
} else {
	$DATA =$_POST;
	$test=0;
}
		
$htmlheader="<?xml version=\"1.0\"?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
        \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
	<title>Untitled</title>
</head>
<body> 
";

$htmlend="
</body>
</html>
";

// MARK: Functions
function email_admin($message)
{
	$pieces = explode("\n", $message);
	mail("davec@polymicro.net", "IPN POST", $message); 
}

function dump_table($var, $title=false, $level=0)
{
    if($level==0)
    {
        echo '<table width="400" border="0" cellspacing="1" cellpadding="3" class="dump">';
        
        if($title)
			echo '<tr>
			<th align="center" colspan="2">'.$title.'</th>
			</tr>';
		
        echo '
		<tr>
		<th align="right">VAR NAME</th>
		<th align="left">VALUE</th>
		</tr>';
    }
    else
    {
        echo '<tr>
		<td colspan="2">
		<table width="100%" border="0" cellspacing="3" cellpadding="3" class="dump_b">
		</td>
		</tr>';
    }
    
    foreach($var as $i=>$value)
    {
        if(is_array($value) or is_object($value))
        {
            dump_table($value, false, ($level +1));
        }
        else
        {
			echo '<tr>
			<td align="right" width="50%" >'.$i.'</th>
			<td align="left" width="50%" >'.$value.'</th>
			</tr>';
        }
    }
    echo '</table>';
}

function checkResult($result)
{
	if (!$result) {
	    $message  = 'Invalid query: ' . mysql_error() . "\n";
	    $message .= 'Whole query: ' . $query;
	    email_admin($message);
		echo "<p>".$message;
		die;
	}

}
function isLicensed($productid)
{
	$query = sprintf("SELECT productname, version, url, licensed FROM pms_sunshop.pm_products WHERE productid = '%s'",
					 mysql_real_escape_string($product_id));
	$result = mysql_query($query);
	checkResult($result);
	$row = mysql_fetch_assoc($result);
	return $row;
}

$link = mysql_connect('mysql.polymicrosystems.com', 'tgunr', 'bat1d93k');
if (!$link) {
	email_admin('\nCould not connect: ' . mysql_error());
} else
	echo '<p>Connected successfully';

// MARK: Record the query
// Read the post from PayPal and add 'cmd' 
$req = 'cmd=_notify-validate'; 

// Run through the posted array 
foreach ($_POST as $key => $value) 
{ 
	// If magic quotes is enabled strip slashes 
	if (get_magic_quotes_gpc()) 
	{ 
		$_POST[$key] = stripslashes($value); 
		$value = stripslashes($value); 
	} 
	$value = urlencode($value); 
	// Add the value to the request parameter 
	$req .= "&$key=$value"; 
} 
	
$server_query = $req;
$server_script_filename = $_SERVER['REQUEST_URI'];

$query = sprintf("INSERT INTO pms_sunshop.pm_ipn (url, query)
				 VALUES (\"%s\", \"%s\")",
				 mysql_real_escape_string($server_script_filename), 
				 mysql_real_escape_string($server_query));
$result = mysql_query($query);
checkResult($result);

// MARK: Callback PP
// Don't try to call back to PP while testing
if ($test == 0)
{
	$url = "https://www.paypal.com/cgi-bin/webscr"; 
	$ch = curl_init();    // Starts the curl handler 
	curl_setopt($ch, CURLOPT_URL,$url); // Sets the paypal address for curl 
	curl_setopt($ch, CURLOPT_FAILONERROR, 1); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // Returns result to a variable instead of echoing 
	curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Sets a time limit for curl in seconds (do not set too low) 
	curl_setopt($ch, CURLOPT_POST, 1); // Set curl to send data using post 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $req); // Add the request parameters to the post 
	$result = curl_exec($ch); // run the curl process (and return the result to $result 
	curl_close($ch); 
	
	
	// Process validation from PayPal 
	// TODO: This sample does not test all the HTTP response code. All 
	// HTTP response codes must be handles or you should use an HTTP 
	// library, such as cUrl 
	if (strcmp ($result, "VERIFIED") == 0) // It may seem strange but this function returns 0 if the result matches the string So you MUST check it is 0 and not just do strcmp ($result, "VERIFIED") (the if will fail as it will equate the result as false) 
	{ 
		$message = "IPN VERIFIED\n"; 
		email_admin($message);
	} else if (strcmp ($result, "INVALID") == 0) { 
		// If 'INVALID', send an email.
		$message = "IPN Invalid\n"; 
		foreach ($_POST as $key => $value){ 
			$message .= $key . " = " .$value ."\n"; 
		} 
		email_admin($message);
		die;
		
	} else { 
		$message = "IPN FAILED\n";
		email_admin($message);
		die;
	} 
}

// MARK: Gather up data 
// in case we need to email it to the admin
$data_copy = "";
foreach ($DATA as $key => $value) {
	$value = urlencode(stripslashes($value));
	if ($test)
		$data_copy .= "<br/>$key=$value";
	else
		$data_copy .= "$key=$value\n";
}

echo "<p>PROCESSING IPN DATA<p>" . $data_copy;

// MARK: Get POST data
//$pp_test_ipn = $DATA['test_ipn'];
$pp_payment_type = $DATA['payment_type'];
$pp_payment_date = $DATA['payment_date'];
$pp_payment_status = $DATA['payment_status'];
$pp_address_status = $DATA['address_status'];
$pp_payer_status = $DATA['payer_status'];
$pp_first_name = $DATA['first_name'];
$pp_last_name = $DATA['last_name'];
$pp_payer_email = $DATA['payer_email'];
$pp_payer_id = $DATA['payer_id'];
$pp_address_name = $DATA['address_name'];
$pp_address_country = $DATA['address_country'];
$pp_address_country_code = $DATA['address_country_code'];
$pp_address_zip = $DATA['address_zip'];
$pp_address_state = $DATA['address_state'];
$pp_address_city = $DATA['address_city'];
$pp_address_street = $DATA['address_street'];
$pp_business = $DATA['business'];
$pp_receiver_email = $DATA['receiver_email'];
$pp_receiver_id = $DATA['receiver_id'];
$pp_residence_country = $DATA['residence_country'];
$pp_item_name = $DATA['item_name'];
$pp_item_number = $DATA['item_number'];
$pp_quantity = $DATA['quantity'];
$pp_shipping = $DATA['shipping'];
$pp_tax = $DATA['tax'];
$pp_mc_currency = $DATA['mc_currency'];
$pp_mc_fee = $DATA['mc_fee'];
$pp_mc_gross = $DATA['mc_gross'];
$pp_mc_gross_1 = $DATA['mc_gross_1'];
$pp_txn_type = $DATA['txn_type'];
$pp_txn_id = $DATA['txn_id'];
$pp_notify_version = $DATA['notify_version'];
$pp_custom = $DATA['custom'];
$pp_charset = $DATA['charset'];
$pp_reason_code = $DATA['reason_code'];
$pp_notify_version = $DATA['notify_version'];
$pp_parent_txn_id = $DATA['parent_txn_id'];

// MARK: Create sunshop data
$ss_name = $pp_first_name." ".$pp_last_name;
$ss_lic_date = date("y-m-d", strtotime($pp_payment_date)); // RFC 2822 formatted date
$ss_orderID = date("ymd");
$ss_transactionID = $pp_txn_id;
$ss_password = uniqid();

// MARK: Verify notify method
if ($pp_notify_version != $pms_notify_version) 
	email_admin("Notify version has changed: $pp_notify_version");

// Do some checks to ensure that the payment has been sent to the correct person 
// Check and ensure currency and amount are correct 
// Check that the transaction has not been processed before 
// Ensure the payment is complete 

// MARK: Verify POST
// Verify this is for us not some imposter
if ($pp_receiver_email != $pms_email || $pp_receiver_id != $pms_merchantid) {
	$message = "IPN CORRUPT\n";
	$message .= "\n".$data_copy;
	email_admin($message);
	echo "".$message;
	die;
}

// Has this transaction been processed before?
// Is the product licensed?
// Get the product info for the license
$query = sprintf("SELECT id FROM pms_sunshop.orders WHERE pm_orderid = '%s'",
				 mysql_real_escape_string($pp_txn_id));
$result = mysql_query($query);
checkResult($result);

// MARK: Order not found
if (mysql_num_rows($result) == 0) {
	echo "Order not found";
	
	// Find the product in DB
	// First try by lookup up SKU
	$product_sku = htmlspecialchars($DATA["item_number"]);
	$query = sprintf("SELECT * FROM pms_sunshop.products WHERE sku='%s'",
	    mysql_real_escape_string($product_sku));
	$result = mysql_query($query);
	checkResult($result);
	if (mysql_num_rows($result) != 0) {
		$row = mysql_fetch_assoc($result);
		$product_id = $row['id'];
		mysql_free_result($result);
		
	} else {
		// FIXME: didn't find by sku, try by description?
		die;
	}
	
	echo "product_id: " . $product_id;
		
    // MARK: Check if product is licensed?
	// Get the product info for the license
	$row = isLicensed($product_id);
	$productname = $row['productname'];
	$version = $row['version'];
	$producturl = $row['url'];
	$licensed = (bool) $row['licensed'];
	
	// MARK: Get product license keys
	if ($licensed) {
		$query = sprintf("SELECT public, private FROM pms_sunshop.pm_keys WHERE productid = '%s'",
						 mysql_real_escape_string($product_id));
		$result = mysql_query($query);
		checkResult($result);
		$row = mysql_fetch_assoc($result);
		$public_key = $row['public'];
		$private_key = $row['private'];
		
		
	}
	
	// MARK: Find the userid
	$query = sprintf("SELECT id FROM pms_sunshop.users WHERE email = '%s'",
					 mysql_real_escape_string($pp_payer_email));
	$result = mysql_query($query);
	checkResult($result);
	if (mysql_num_rows($result) != 0) {
		// use this row as the $userid
		$row = mysql_fetch_assoc($result);
		$userid = $row['id'];
	} else // MARK: Find username
	{
		// if can't find the user, make a new one from the POST
		// first find a unique username using the first part of the email
		$pieces = explode("@", $pp_payer_email);
		$username = $pieces[0];
		$query = sprintf("SELECT id FROM pms_sunshop.users WHERE username = '%s'",
						 mysql_real_escape_string($username));
		$result = mysql_query($query);
		checkResult($result);		
		if (mysql_num_rows($result) != 0) {
			// already have a username with this string
			// try making a username from the reset of the email parts
			$domain_pieces = explode(".", $pieces[1]);
			foreach($domain_pieces as $dpiece) {
				$username .= $dpiece;			
			}
			$query = sprintf("SELECT id FROM pms_sunshop.users WHERE email = '%s'",
							 mysql_real_escape_string($username));
			$result = mysql_query($query);
			checkResult($result);		
			// if still can't find a username, invent one
			if (mysql_num_rows($result) != 0) {
				$username = uniqid();
			}
		}
        
        // MARK: Create new user
		$query = sprintf("INSERT INTO pms_sunshop.users (username, password, ship_first_name, ship_last_name, ship_company_name, ship_address1, ship_city, ship_state, ship_zip, ship_country, bill_first_name, bill_last_name, bill_company_name, bill_address1, bill_city, bill_state, bill_zip, bill_country, email)
						 VALUES (\"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\")",
						 mysql_real_escape_string($username),
						 mysql_real_escape_string($ss_password),
						 mysql_real_escape_string($pp_first_name),
						 mysql_real_escape_string($pp_last_name),
						 mysql_real_escape_string($pp_address_name),
						 mysql_real_escape_string($pp_address_street),
						 mysql_real_escape_string($pp_address_city),
						 mysql_real_escape_string($pp_address_state),
						 mysql_real_escape_string($pp_address_zip),
						 mysql_real_escape_string($pp_address_country_code),
						 mysql_real_escape_string($pp_first_name),
						 mysql_real_escape_string($pp_last_name),
						 mysql_real_escape_string($pp_address_name),
						 mysql_real_escape_string($pp_address_street),
						 mysql_real_escape_string($pp_address_city),
						 mysql_real_escape_string($pp_address_state),
						 mysql_real_escape_string($pp_address_zip),
						 mysql_real_escape_string($pp_address_country_code),
						 mysql_real_escape_string($pp_payer_email));
		$result = mysql_query($query);
		checkResult($result);	
		// grab the last entry for userid
		$query = sprintf("SELECT LAST_INSERT_ID()");
		$result = mysql_query($query);
		checkResult($result);	
		if (mysql_num_rows($result) != 0) {
			$row = mysql_fetch_row($result);
			$userid = $row[0];
		} else {
			$message = 'Failed to create userid' . $userid." for ".$username;
			email_admin($message);		
			echo "".$message;
			die;
		}
	}
	
	// MARK: Find the orderid
    // If not found create new order
	$query = sprintf("SELECT id FROM pms_sunshop.orders WHERE pm_orderid = '%s'",
					 mysql_real_escape_string($ss_transactionID));
	$result = mysql_query($query);
	checkResult($result);
	
	if (mysql_num_rows($result) != 0) {
		$row = mysql_fetch_assoc($result);
		$ss_orderID = $row['id'];
	} else // MARK: Create new order
	{
		
		$ss_pm_notes = "payment_type:".$pp_payment_type." payer_id: ".$pp_payer_id." txn_type: ".$pp_txn_type." custom: ".$pp_custom;
		$query = sprintf("INSERT INTO pms_sunshop.orders 
						 (userid, 
						 order_date,
						 ship_method,
						 ship_price,
						 tax,
						 subtotal,
						 total,
						 pay_method,
						 cc_status,
						 status,
						 ship_first_name,
						 ship_last_name,
						 ship_company_name,
						 ship_address1,
						 ship_city,
						 ship_state,
						 ship_zip,
						 ship_country,
						 bill_first_name,
						 bill_last_name,
						 bill_company_name,
						 bill_address1,
						 bill_city,
						 bill_state,
						 bill_zip,
						 bill_country,
						 email,
						 pm_orderid,
						 pm_notes
						 )
						 VALUES 
						 (\"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\")",
						 mysql_real_escape_string($userid),				// userid
						 mysql_real_escape_string($ss_lic_date),	// order_date
						 mysql_real_escape_string("Download"),			// ship_method
						 mysql_real_escape_string($pp_shipping),		// ship_price
						 mysql_real_escape_string($pp_tax),				// tax
						 mysql_real_escape_string($pp_mc_gross - $pp_tax),	// subtotal
						 mysql_real_escape_string($pp_mc_gross),			// total
						 mysql_real_escape_string("PayPal (Via PayPal Site)"),		// pay_method
						 mysql_real_escape_string("Payment Received"),		// cc_status
						 mysql_real_escape_string($pp_payment_status),		// status
						 mysql_real_escape_string($pp_first_name),			// ship_first_name
						 mysql_real_escape_string($pp_last_name),			// ship_last_name
						 mysql_real_escape_string($pp_address_name),			// ship_company_name
						 mysql_real_escape_string($pp_address_street),			// ship_address1
						 mysql_real_escape_string($pp_address_city),			// ship_city
						 mysql_real_escape_string($pp_address_state),			// ship_state
						 mysql_real_escape_string($pp_address_zip),			// ship_zip
						 mysql_real_escape_string($pp_address_country_code),			// ship_country
						 mysql_real_escape_string($pp_first_name),			// bill_first_name
						 mysql_real_escape_string($pp_last_name),			// bill_last_name
						 mysql_real_escape_string($pp_address_name),			// bill_company_name
						 mysql_real_escape_string($pp_address_street),			// bill_address1
						 mysql_real_escape_string($pp_address_city),			// bill_city
						 mysql_real_escape_string($pp_address_state),			// bill_state
						 mysql_real_escape_string($pp_address_zip),			// bill_zip
						 mysql_real_escape_string($pp_address_country_code),			// ship_country
						 mysql_real_escape_string($pp_payer_email),			// email
						 mysql_real_escape_string($pp_txn_id),						// pm_orderid
						 mysql_real_escape_string($ss_pm_notes)						// pm_notes
						 );		
		$result = mysql_query($query);
		
		checkResult($result);		
		
		// grab the last entry for orderid
		$query = sprintf("SELECT LAST_INSERT_ID()");
        if ($test)
            echo "Last orderid: $query";
		
		$result = mysql_query($query);
        if ($test)
            echo "Last orderid: ".$result;
		checkResult($result);		
		if (mysql_num_rows($result) != 0) {
                    if ($test)
            echo "OrderID found";

			$row = mysql_fetch_row($result);
			$ss_orderID = $row[0];
		} else {
			$message = 'Failed to create userid' . $userid." for ".$username;
			email_admin($message);		
			echo "".$message;
			die;
		}
		
	}
	
	if ($licensed) {
		// MARK: Create license dictionary
		$dict = array("Email" => $pp_payer_email,
					  "Name" => $ss_name,
					  "Date" => $ss_lic_date,
					  "TransactionID" => $ss_transactionID,
					  "Application" => $productname,
					  "Version" => $version,
					  "OrderID" => $ss_orderID);
		$license = licenseDataForDictionary($dict, $public_key, $private_key);
		$signature = getSignature($dict, $public_key, $private_key);
		
		// MARK: Create license file
		$filecount=1;
		$licenseFileName = $abs_path."/license/".$pp_payer_email.".padlicense";
		while (file_exists($licenseFileName)) { 
			$licenseFileName = $abs_path."/license/".$pp_payer_email."-".$filecount.".padlicense";
			$filecount += 1;
		} 			
        $fh = fopen($licenseFileName, 'w');
		if (!$fh) {
			email_admin("can't create license file");
		} else {
	        fwrite($fh, $license);
	        fclose($fh);
        }
 	
		// MARK: Store license in DB
		$query = sprintf("INSERT INTO pms_sunshop.pm_licenses (orderid, productid, date, userid, application, signature, email, transactionid, version, name, license)
						 VALUES (\"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\")",
						 mysql_real_escape_string($ss_orderID), 
						 mysql_real_escape_string($product_id), 
						 mysql_real_escape_string($ss_lic_date), 
						 mysql_real_escape_string($userid), 
						 mysql_real_escape_string($productname), 
						 mysql_real_escape_string(""), 
						 mysql_real_escape_string($pp_payer_email), 
						 mysql_real_escape_string($ss_transactionID), 
						 mysql_real_escape_string($version), 
						 mysql_real_escape_string($ss_name), 
						 mysql_real_escape_string($license));
		$result = mysql_query($query);
		checkResult($result);		
		// MARK: Mail license data
		$to = $pp_payer_email;
		$aq_bcc = "davec@polymicro.net";
		$aq_from = "support@polymicrosystems.com";
		$aq_subject = $productname." License";
		$aq_message =
		"Hello ".$ss_name."
		
		Here is your license for ".$productname.".
		
		If you have not already downloaded ".$productname." please do so now at: 
		".$producturl."
		
		Just double-click on the license to register ".$productname.".
		
		Thanks,
		
		PolyMicro Systems";
		$aq_licenseName = $pp_payer_email.".padlicense";
		
		sendMail($to, $aq_from, $aq_subject, $aq_message, $license, $aq_licenseName, $aq_bcc);
		
		if ($test) {
			echo "TO:".$to."<br>FROM:".$aq_from."<br>SUBJECT:".$aq_subject."<br>LICENSENAME:".$aq_licenseName."<br>BCC:".$aq_bcc."<br>MESSAGE:".$aq_message."<br>LICENSE:".$license;
			echo "IPN PROCESSING COMPLETED";
//			echo $htmlend;
		}
		
	}
} 
else // MARK: Check for refund
{
	echo "<p>Order already processed, checking for refund";
	if ($pp_reason_code == "refund") {
		echo "<p>parent_txn_id: $pp_parent_txn_id";
		$query = sprintf("SELECT * FROM pms_sunshop.orders WHERE pm_orderid = '%s'",
						 mysql_real_escape_string($pp_parent_txn_id));
		$result = mysql_query($query);
		checkResult($result);
		
		// MARK: Order not found
		if (mysql_num_rows($result) != 0) {
			$row = mysql_fetch_assoc($result);
//			dump_table($row);
			if ($row['status'] == "Refunded")
				echo "<p>Order already refunded";
			else
			{	
				echo "<p>Refunding order";
				$pm_orderid = $row['id'];
				$pm_tax = $row['tax'];
				$pm_total = $row['total'];
				$pm_subtotal = $row['subtotal'];
				$query = sprintf("UPDATE pms_sunshop.orders SET 
								 cc_status=\"Refunded\",
								 status=\"Refunded\",
								 tax=\"".-$pm_tax."\",
								 total=\"".-$pm_total."\",
								 subtotal=\"".-$pm_subtotal."\"
								 WHERE id=\"".$pm_orderid."\"");
				$result = mysql_query($query);
				checkResult($result);
				$query = sprintf("UPDATE pms_sunshop.pm_licenses SET 
								 blacklisted='yes'
								 WHERE orderid='".$pm_orderid."'");
				$result = mysql_query($query);
				checkResult($result);
				echo "<p>Order refunded";
			}
		}
	}
}
mysql_close($link);

//phpinfo();

// Secure Merchant ID XCUQDTBS2G6G2

// IPN URL TEST
// http://mp.polymicro.net/sites/polymicroshop.com/shop/include/ipn/ipn.php

// IPN URL LIVE
// http://mp.polymicro.net/sites/polymicroshop.com/shop/include/ipn/ipn.php

// IPN Corrupt
//http://mp.polymicro.net/sites/polymicroshop.com/shop/include/ipn/ipn.php?test_ipn=1&payment_type=instant&payment_date=13%3A21%3A49+Sep+06%2C+2010+PDT&payment_status=Completed&address_status=confirmed&payer_status=verified&first_name=John&last_name=Tester&payer_email=tester%40polymicro.net&payer_id=TESTBUYERID01&address_name=John+Tester&address_country=United+States&address_country_code=US&address_zip=95941&address_state=CA&address_city=Forbestown&address_street=18806+Challenge+Cut+Off+Rd&business=seller%40polymicrosystems.com&receiver_email=%40paypalsandbox.com&receiver_id=TESTSELLERID1&residence_country=US&item_name=PickADisk&item_number=900%200001&quantity=1&shipping=0.0&tax=2.06&mc_currency=USD&mc_fee=0.44&mc_gross=24.95&mc_gross_1=22.45&txn_type=web_accept&txn_id=49962021&notify_version=2.1&custom=xyz123&charset=windows-1252&verify_sign=A.vc3XyI0Ld.eh2fxFPovmLZb39CACtsThELUGzlOeN008-y4DVvrMvr

//http://mp.polymicro.net/sites/polymicroshop.com/shop/include/ipn/ipn.php?test_ipn=1&payment_type=instant&payment_date=13%3A21%3A49+Sep+06%2C+2010+PDT&payment_status=Completed&address_status=confirmed&payer_status=verified&first_name=John&last_name=Tester&payer_email=tester%40polymicro.net&payer_id=TESTBUYERID01&address_name=John+Tester&address_country=United+States&address_country_code=US&address_zip=95941&address_state=CA&address_city=Forbestown&address_street=18806+Challenge+Cut+Off+Rd&business=davec%40mac.com&receiver_email=davec%40mac.com&receiver_id=TESTSELLERID1&residence_country=US&item_name=PickADisk&item_number=900+0001&quantity=1&shipping=0.0&tax=2.06&mc_currency=USD&mc_fee=0.44&mc_gross=24.95&mc_gross_1=22.45&txn_type=web_accept&txn_id=49962022&notify_version=2.1&custom=xyz123&charset=windows-1252&verify_sign=A.vc3XyI0Ld.eh2fxFPovmLZb39CACtsThELUGzlOeN008-y4DVvrMvr

// PP1 http://mp.polymicro.net/sites/polymicroshop.com/shop/include/ipn/ipn.php?mc_gross=1.03&protection_eligibility=Eligible&address_status=confirmed&payer_id=RMXAVDMX7LWKN&tax=0.08&address_street=18806+Challenge+Cut+Off+Rd&payment_date=02:33:30+Sep+09,+2010+PDT&payment_status=Completed&charset=windows-1252&address_zip=95941&first_name=Dave&mc_fee=0.33&address_country_code=US&address_name=polyMicro+Systems&notify_version=3.0&custom=&payer_status=unverified&business=davec@polymicrosystems.com&address_country=United+States&address_city=Forbestown&quantity=1&verify_sign=A21kmBLyRcQpDSjYvmswlm3ckjahA0CMFuktsKR5Un7wnqJCF-6jcMLQ&payer_email=tgunr@polymicro.net&txn_id=9SF73001P68015714&payment_type=instant&payer_business_name=polyMicro+Systems&btn_id=1494716&last_name=Carlton&address_state=CA&receiver_email=davec@polymicrosystems.com&payment_fee=0.33&shipping_discount=0.00&insurance_amount=0.00&receiver_id=XCUQDTBS2G6G2&txn_type=web_accept&item_name=PickADisk&discount=0.00&mc_currency=USD&item_number=900+0001&residence_country=US&shipping_method=Default&handling_amount=0.00&transaction_subject=PickADisk&payment_gross=1.03&shipping=0.00

// PP2 http://mp.polymicro.net/sites/polymicroshop.com/shop/include/ipn/ipn.php?handling_amount=0.00&discount=0.00&insurance_amount=0.00&payer_id=RMXAVDMX7LWKN&address_country_code=US&btn_id=1494716&address_zip=95941&shipping=0.00&charset=windows-1252&payment_gross=1.03&address_status=confirmed&shipping_discount=0.00&address_street=18806+Challenge+Cut+Off+Rd&verify_sign=AiPC9BjkCyDFQXbSkoZcgqH3hpacA1U7Nm-N0oIT8n7XDO7eD2CvMXhs&item_name=PickADisk&txn_type=web_accept&receiver_id=XCUQDTBS2G6G2&payment_fee=0.33&mc_currency=USD&transaction_subject=PickADisk&shipping_method=Default&custom=&protection_eligibility=Eligible&address_country=United+States&payer_status=unverified&first_name=Dave&address_name=polyMicro+Systems&mc_gross=1.03&payment_date=02:33:30+Sep+09,+2010+PDT&payment_status=Completed&quantity=1&business=davec@polymicrosystems.com&item_number=900+0001&last_name=Carlton&address_state=CA&payer_business_name=polyMicro+Systems&txn_id=9SF73001P68015714&mc_fee=0.33&resend=true&payment_type=instant&notify_version=3.0&payer_email=tgunr@polymicro.net&receiver_email=davec@polymicrosystems.com&address_city=Forbestown&tax=0.08&residence_country=US

// Refund http://mp.polymicro.net/sites/polymicroshop.com/shop/include/ipn/ipn.php?mc_gross=-1.03&protection_eligibility=Eligible&payer_id=KTL78NU9F7WGG&address_street=888+O'Farrell+St.+206&payment_date=10:36:17+Sep+11,+2010+PDT&payment_status=Refunded&charset=windows-1252&address_zip=94109&first_name=Jack&mc_fee=-0.33&address_country_code=US&address_name=Jack+Small&notify_version=3.0&reason_code=refund&custom=&business=davec@polymicrosystems.com&address_country=United+States&address_city=San+Francisco&verify_sign=AynlJ2zsmflH.74VEfIBZZJsEArxAtZWx6aX.T1CopooKGT8L-JpmBGE&payer_email=jaxs@jaxs.com&parent_txn_id=41Y14799L86009316&txn_id=7BS526278N628645G&payment_type=instant&btn_id=1494716&last_name=Small&address_state=CA&receiver_email=davec@polymicrosystems.com&payment_fee=-0.33&shipping_discount=0.00&receiver_id=XCUQDTBS2G6G2&insurance_amount=0.00&item_name=PickADisk&discount=0.00&mc_currency=USD&item_number=900+0001&residence_country=US&handling_amount=0.00&shipping_method=Default&transaction_subject=PickADisk&payment_gross=-1.03&shipping=0.00

?> 

