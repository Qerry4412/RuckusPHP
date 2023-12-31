<!--
©COPYRIGHT
RuckusPHP-API - index.php
This program is subject to the GPL3 and may therefore be modified and redistributed,
provided that the resulting code is licensed again under the GPL2.

Programmed by Gerrit Markl 08.10.2022
Email: gerrit.markl@t-online.de
-->

<?php

//Create a Token for accessing the Ruckus API
function _get_token() {

	//Variabels
	//URL for the OpenAIP
	$url = "https://myruckus.com/wsg/api/public/v11_0/serviceTicket";
	//Username Password
	$uname = 'api-user';
	$pword = 'api-user-password';
	//cURL Response / Decode Response
	$resp;
	$dec;

	//DataArray
	$data_array = array(
		'username' => $uname,
		'password' => $pword
	);

	//Concert DataArray in to Json
	$DATA = json_encode($data_array);

	//Initializes PHPcURL
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, ture);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $DATA);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("content-type: application/json;charset=UTF-8", 
		"cookie: JSESSIONID={JSESSIONID}",
		"Set-cookie: JSESSIONID={JSESSIONID}; Path=/wsg; Secure"));

	//Execution of Curl and its decoding from the JSON format
	$resp = curl_exec($ch);
	$dec = json_decode($resp, true);

	//Errorchecking
	if($resp === false){
		echo 'Curl error' . curl_error($ch);
	}
	else{
		//Returns the decoded Array
		return $dec;

		//DEBUG
		/*DEBUG
		foreach($dec as $key => $val){
			echo $key . ': ' . $val . ' <br> ';
       	}
		DEBUG*/

	}

	//CLose the PHPcURL session
	curl_close($ch);
}

//Get voucher from the API
function _getVoucher($vname){

	$token = _get_db_token();
	$url = "https://myruckus.com/wsg/api/public/v11_0/identity/guestpass?serviceTicket=$token&displayName=$vname";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_GET, ture);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("content-type: application/json;charset=UTF-8", 
            "cookie: JSESSIONID={JSESSIONID}",
            "Set-cookie: JSESSIONID={JSESSIONID}; Path=/wsg; Secure"));

    //Execution of Curl and its decoding from the JSON format
    $resp = curl_exec($ch);
    $dec = json_decode($resp, true);

	//Errorchecking
    if($resp === false){
        echo 'Curl error' . curl_error($ch);
		curl_close($ch);
		return false;
    }

    //Close the PHPcURL session
    curl_close($ch);

	return  $dec;
}

function _voucherExist($vname) {

        $token = _get_db_token();
        $url = "https://myruckus.com/wsg/api/public/v11_0/identity/guestpass?serviceTicket=$token&displayName=$vname";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_GET, ture);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("content-type: application/json;charset=UTF-8", 
                "cookie: JSESSIONID={JSESSIONID}",
                "Set-cookie: JSESSIONID={JSESSIONID}; Path=/wsg; Secure"));

        //Ausführung von Curl, sowie dessen Decodierung aus dem JSON Format
        $resp = curl_exec($ch);
        $dec = json_decode($resp, true);

        //Fehlerüberprüfung
        if($resp === false) {
                echo 'Curl error' . curl_error($ch);
        }
        else {
		curl_close($ch);
		return $dec["list"][0]["userId"];
	}

	curl_close($ch);
}

function _createVoucher($patID) {
	//Erzeugt ein Voucher anhand der PAD ID 
	$token = _get_db_token();
	$url = "https://myruckus.com/wsg/api/public/v11_0/identity/guestpass/generate?serviceTicket=$token";

	$ch = curl_init($url);

        //DataArray
        $data_array = array(
                'domainId' => "",
		'guestName' => "$patID",
		'wlan' => array(
			'name' => "WLANNAME"
			),
		'zone' => array(
			'name' => "ZONENAME"
			),
		'numberOfPasses' => 1,
		'passValidFor' => array(
			'expirationValue' => 30,
			'expirationUnit' => "DAY"
			),
		'autoGeneratedPassword' => true,
		'passEffectSince' => "CREATION_TIME",
		'passUseDays' => 7,
		'maxDevices' => array(
			'maxDevicesAllowed' => "LIMITED",
			'maxDevicesNumber' => 3
			),
		'sessionDuration' => array(
			'requireLoginAgain' => true,
			'sessionValue' => 3,
			'sessionUnit' => "DAY"
			),
		'remarks' => "CreateByAPI User"
        );

        //Konventiert das DataArray in das JSON Format
        $DATA = json_encode($data_array);

        //Initialisierung von PHPcURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, ture);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $DATA);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("content-type: application/json;charset=UTF-8", 
                "cookie: JSESSIONID={JSESSIONID}",
                "Set-cookie: JSESSIONID={JSESSIONID}; Path=/wsg; Secure"));

        //Ausführung von Curl, sowie dessen Decodierung aus dem JSON Format
        $resp = curl_exec($ch);
        $dec = json_decode($resp, true);

        //Fehlerüberprüfung
        if($resp === false) {
                echo 'Curl error' . curl_error($ch);
        }
	//DEBUG echo "<br>Voucher erstellt!!!<br>";
	//DEBUG echo var_dump($dec) . "<br";


	curl_close($ch);
}

?>
