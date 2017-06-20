<?php 
error_reporting(-1);
session_start();

echo "The ProtectPay SOAP API live URL is: <a href='https://api.propay.com/protectpay/sps.svc' target='_blank' >https://api.propay.com/protectpay/sps.svc</a>";
echo "<br>Authentication Token : 44a47c68-2ede-41f1-b6a5-e522e809d52d";
echo "<br>Biller ID : 2022435248186914";
echo "<br>Merchant / Profile ID : 715035";

echo "<br>ProtectPay PMI Seamless Payment Interface : <a href='https://3.basecamp.com/3402392/blobs/0dbf58cc924310e12b728b24942d71870010/download/ProtectPay%20PMI%20Seamless%20Payment%20Interface%20Manual%204.0.0.pdf' target='_blank' >https://3.basecamp.com/3402392/blobs/0dbf58cc924310e12b728b24942d71870010/download/ProtectPay%20PMI%20Seamless%20Payment%20Interface%20Manual%204.0.0.pdf</a>";
echo "<br>Temp Token : <a href='https://developer.propay.com/Merchant-Services/Tokenization/Encrypt-Your-Information' target='_blank' >https://developer.propay.com/Merchant-Services/Tokenization/Encrypt-Your-Information</a>";

echo "<hr><pre>";
print_r("
Step 1 :- we will create a TempToken 
	(ProtectPay API 4.7.1 - ProtectPay API Manual 4.0.0.pdf)
		It will response Credential id,payerID and TempToken.
			
Step 2 :- we will generate Encrypted string(settingsCipher Method) using TempToken and payer_id which we created from step 1  
	(ProtectPay PMI Seamless Payment 3.3 - ProtectPay PMI Seamless Payment Interface Manual 4.0.0.pdf)
		It will response Encrypted string
			
Step 3 :- we will create a form for propay with this Encrypted string and credential id which we created from step 1 
	(ProtectPay PMI Seamless Payment 3.4 - ProtectPay PMI Seamless Payment Interface Manual 4.0.0.pdf)
		It will submit the form to propay SPI and response the ResponseCipher which is also Encrypted string 
			
step 4 :- we will decrypt ResponseCipher using TempToken id which we created from step 1
	(ProtectPay PMI Seamless Payment 3.5 - ProtectPay PMI Seamless Payment Interface Manual 4.0.0.pdf)
		It will response values that are returned by the SPI.
");
echo "</pre><hr>";

if((!empty($_REQUEST['ResponseCipher'])) && (!empty($_SESSION))  ){
	
	/*
	echo "<hr><pre><br>_REQUEST: ";
		print_r($_REQUEST);
	echo "</pre><hr>";
	echo "<pre><br>_SESSION: ";
		print_r($_SESSION);
	echo "</pre>";
	*/
	if(!empty($_SESSION)){
		$responseCipher= $_REQUEST['ResponseCipher'];
		$tempToken = $_SESSION["temp_token"];
		
		/*
		echo "<hr><pre>";
			print_r($tempToken);
		echo "</pre>";
		echo "<hr><pre>";
			print_r($responseCipher);
		echo "</pre>";
		*/
		$response = decryptResponseCipher($tempToken,$responseCipher);

		parse_str($response,$parseArray);

		echo "<pre><br>Propay Response: ";
			print_r($parseArray);
		echo "</pre>";
		
		/*
		echo "<hr><pre><br>response: ";
			print_r($response);
		echo "</pre>";
		echo "<hr><pre><br>responseCipher: ";
			print_r($responseCipher);
		echo "</pre>";
		*/
	}	
	session_destroy();
} else {
	/*
	$Args = array(
		"44a47c68-2ede-41f1-b6a5-e522e809d52d",			//Authentication Token
		"2022435248186914", 							//Biller ID
		"",					 							//Payer ID	//3145560420338995
		"test111",										//Payer Name
		"6000");										//Time To Live
	*/	
	$Args = array(
		"44a47c68-2ede-41f1-b6a5-e522e809d52d",			//Authentication Token
		"2022435248186914", 							//Biller ID
		"",					 							//Payer ID	//3145560420338995
		"test111",										//Payer Name
		"6000");										//Time To Live

	Get_Temp_Token($Args);
	//$_SESSION['post'] == false;
	//session_destroy();
	$_SESSION["credential_id"] = $credential_id;
	$_SESSION["payer_id"] = $payer_id;
	$_SESSION["temp_token"] = $temp_token;
		
	$settingCipherResponse = getSettingCipher($temp_token, $payer_id );

	/*
	echo "<pre><br>settingCipherResponse: ";
		print_r($settingCipherResponse);
	echo "</pre>";
	
	echo "<pre><br>SESSION: ";
		print_r($_SESSION);
	echo "</pre>";*/
	?>
	
		<form method="POST" action="https://protectpay.propay.com/pmi/spr.aspx" id="propay-signup">
			<table>
				
				<?php /*
				<input size="100" value="44a47c68-2ede-41f1-b6a5-e522e809d52d" id="fname" name="AuthToken" required type="hidden" class="form-control no_radius">
				<tr><td>
				<label for="fname" class="p-t-5">AuthToken *</label></td><td>
				<input size="100" value="44a47c68-2ede-41f1-b6a5-e522e809d52d" id="fname" name="AuthToken" required type="text" class="form-control no_radius">
				</td></tr>	
				*/ ?>
				<tr><td>
				<label for="source-email" class="p-t-5">Amount *</label></td><td>
				<input size="100" id="evenphone" value="1.00" name="Amount" type="text" class="form-control no_radius" readonly>		
				</td></tr>
				
				<tr><td>
				<label for="source-email" class="p-t-5">CardHolder Name *</label></td><td>
				<input size="100" id="source-email" name="CardHolderName" type="text" class="form-control no_radius">		
				</td></tr>
				
				<tr><td>
				<label for="evenphone"  class="p-t-5">PaymentTypeId *</label></td><td>
				<input size="100" id="evenphone" value="Visa" name="PaymentTypeId" type="text" class="form-control no_radius">		
				</td></tr>
				
				<tr><td>
				<label for="source-email" class="p-t-5">CardNumber *</label></td><td>
				<input size="100" id="source-email" name="CardNumber" type="text" class="form-control no_radius">		
				</td></tr>
				
				<tr><td>
				<label for="ssn" class="p-t-5">ExpMonth</label></td><td>
				<input size="100" id="ssn" name="ExpMonth" type="text" class="form-control no_radius">		
				</td></tr>
				
				<tr><td>
				<label for="ssn" class="p-t-5">ExpYear</label></td><td>
				<input size="100" id="ssn" name="ExpYear" type="text" class="form-control no_radius">
				</td></tr>
				
				<tr><td>
				<label for="ssn" class="p-t-5">CVV</label></td><td>
				<input size="100" size="100" id="ssn" name="CVV" type="text" class="form-control no_radius">
				</td></tr>
				
				<input size="100" id="ssn" name="CID" type="hidden" value="<?php echo $credential_id;?>" class="form-control no_radius">
				<input size="100" id="ssn" name="SettingsCipher" type="hidden" value="<?php echo $settingCipherResponse;?>" class="form-control no_radius">
				
				<?php /*
				<tr><td>
				<label for="ssn" class="p-t-5">CID</label></td><td>
				<input size="100" id="ssn" name="CID" type="text" value="<?php echo $credential_id;?>" class="form-control no_radius">
				</td></tr>
				
				<tr><td>
				<label for="ssn" class="p-t-5">SettingsCipher</label></td><td>
				<input size="100" id="ssn" name="SettingsCipher" type="text" value="<?php echo $settingCipherResponse;?>" class="form-control no_radius">
				</td></tr>
				*/ ?>
				
				<tr><td></td><td>
					<button type="submit" class="btn bg-green btn-block waves-effect waves-light text-white btn-rounded"><span>&nbsp;&nbsp;&nbsp;&nbsp;Pay Now&nbsp;&nbsp;&nbsp;&nbsp;</span></button>
				</td></tr>
			</table>
		</form>
		<hr>
	<?php
	
}	
function Get_Temp_Token($Arguments)
{ 
	
	$envelope=
	'<?xml version="1.0"?>
	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:con="http://propay.com/SPS/contracts" xmlns:typ="http://propay.com/SPS/types">
	<soapenv:Header/>
	<soapenv:Body>
	<con:GetTempToken>
	 <con:tempTokenRequest>
	   <typ:Identification>
	     <typ:AuthenticationToken>' . $Arguments[0] .'</typ:AuthenticationToken>
	     <typ:BillerAccountId>' . $Arguments[1] . '</typ:BillerAccountId>
	   </typ:Identification>
	   <typ:PayerInfo>
	     <typ:Id>' . $Arguments[2] . '</typ:Id>
	     <typ:Name>' . $Arguments[3] .'</typ:Name>
	   </typ:PayerInfo>
	   <typ:TokenProperties>
	     <typ:DurationSeconds>' . $Arguments[4] . '</typ:DurationSeconds>
	   </typ:TokenProperties>
	 </con:tempTokenRequest>
	</con:GetTempToken>
	</soapenv:Body>
	</soapenv:Envelope>';

	$SOAP_Action = "GetTempToken"; 
	
	Submit_Request($envelope, $SOAP_Action);
}
function getSettingCipher($tempToken,$payerID,$profileID = '715035')
{    
	//$reqURL = "http://developmentbox.co/propay/propay-soap.php"; 2092969
	//$reqURL = "http://developmentbox.co/propay/propay-using-SPI.php";
	//$reqURL = "http://localhost/corephp/propay-using-SPI.php";
	$reqURL = "https://app.spera.io/propay-using-SPI.php";
	$keyValuePair = 
	"AuthToken=".$tempToken."&PayerID=".$payerID."&CurrencyCode=USD&ProcessMethod=Capture&PaymentMethodStorageOption=OnSuccess&InvoiceNumber=Invoice123&Comment1=comment1&Comment2=comment2&echo=echotest&ReturnURL=".$reqURL."&ProfileId=".$profileID."&PaymentProcessType=CreditCard&StandardEntryClassCode=&DisplayMessage=True&Protected=False";
	$settingsCipher = spiEncrypt($tempToken, $keyValuePair);
	return $settingsCipher;
}
function Submit_Request($envelope, $SOAP_Action)
{
	/* The HTTP header must include the SOAPAction */ 
	$header = array(
	"Content-type:text/xml; charset=\"utf-8\"",
	"Accept: text/xml",
	"SOAPAction: http://propay.com/SPS/contracts/SPSService/".$SOAP_Action
	);
	$soap_do = curl_init();
	/*Change the following URL to point to production instead of integration */
	//curl_setopt($soap_do, CURLOPT_URL, "https://protectpaytest.propay.com/API/SPS.svc");
	curl_setopt($soap_do, CURLOPT_URL, "https://api.propay.com/protectpay/sps.svc");
	curl_setopt($soap_do, CURLOPT_TIMEOUT, 30);
	curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($soap_do, CURLOPT_POST, true);
	curl_setopt($soap_do, CURLOPT_POSTFIELDS, $envelope);
	curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);
	curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($soap_do, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

	$response = curl_exec($soap_do);
	$err = curl_error($soap_do);
	curl_close($soap_do);
	/*Call Parse Function for the XML response*/
	
	Parse_Results($response);
}
function Parse_Results($api_response)
{
	$doc = new DOMDocument();
	$doc->loadXML($api_response);
	//Pretty Print response
	$api_result = new DOMDocument('1.0');
	$api_result->preserveWhiteSpace = false;
	$api_result->formatOutput = true;
	$api_result->loadXML($api_response);
	
	if(isset($doc->getElementsByTagName('ResultCode')->item(0)->nodeValue))
	{
		$result_code = $doc->getElementsByTagName('ResultCode')->item(0)->nodeValue;
		$result_value = $doc->getElementsByTagName('ResultValue')->item(0)->nodeValue;
		$result = "";
		//$result = "Request Results:";
		//$result .= "\nResult Code: " . $result_code;
		//$result .= "\nResult Value: " . $result_value;
		if($result_code != '00' || $result_value == "FAILURE")
		{
			$result_message = $doc->getElementsByTagName('ResultMessage')->item(0)->nodeValue;
			$result .= "\nResult Message: " . $result_message;
			$result .= "\n";
			echo "<hr><pre>1: ";
			print_r($result);
			echo "</pre>";
		}
		else
		{
			global $credential_id,$payer_id,$temp_token;
			$credential_id = $doc->getElementsByTagName('CredentialId')->item(0)->nodeValue;
			$payer_id = $doc->getElementsByTagName('PayerId')->item(0)->nodeValue;
			$temp_token = $doc->getElementsByTagName('TempToken')->item(0)->nodeValue;			
			//$result .= "\nTransaction Results:";
			$result .= "\nCredential ID: " . $credential_id;
			$result .= "\nPayer ID: " . $payer_id; 
			$result .= "\nTemp Token: " . $temp_token;
			$result .= "\n";
			
			echo "<pre>";
				print_r($result);
			echo "</pre><hr>";
		} 		
	}	
	else
	{
		echo "<hr><pre>3: ";
		print_r($api_result->saveXML());
		echo "</pre>";
	};	
} 
function spiEncrypt($tempToken, $keyValuePair)
{
	$key = hash('MD5', utf8_encode($tempToken), true);  //generate an MD5 hash 
	$iv = $key;
	$settingsCipher = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, padData($keyValuePair), MCRYPT_MODE_CBC, $iv);
	return base64_encode($settingsCipher);
}
function padData($data)
{
	$padding = 16 - (strlen($data) % 16);
	$data .= str_repeat(chr($padding), $padding); 
	return $data;
}
function decryptResponseCipher($tempToken,$responseCipher)
{   
	$key = hash('MD5', utf8_encode($tempToken), true);
	$iv = $key;
	$spiResponse = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($responseCipher), MCRYPT_MODE_CBC, $iv);
	return unPadData($spiResponse);      
}
function unPadData($data)
{
	$padding = ord($data[strlen($data) - 1]);
	return substr($data, 0, -$padding);
}