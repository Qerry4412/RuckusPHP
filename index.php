<!--
©COPYRIGHT
RuckusPHP-API - index.php
This program is subject to the GPL3 and may therefore be modified and redistributed,
provided that the resulting code is licensed again under the GPL2.

Programmed by Gerrit Markl 08.10.2022
Email: gerrit.markl@t-online.de
-->


<!DOCTYPE html>
<html>
        <head>
                <title>Ruckus PHP API</title>
        </head>
        <body>
                <?php

                /*
                Comments with //DEBUG: can be used for Debug purposes
                */

                //Includes
                require "sql.php";
                require "ruckusapi.php";

                //Get PatienID from the KIS System via php GET
                /*
                Attention, experienced users may understand how the call works and can thus create a voucher for themselves. 
                My idea is to distribute the ID over several variables, but this has to be supported by the KIS. 
                */
                $patid = htmlspecialchars($_GET["pat"]);

                //DEBUG: Write the Patient data 
                //DEBUG: echo "<br>PadID: " . $patid . "<br>";

                //--------------------Functions---------------------

                //Create a timestamp like: 2022-10-08 22:30:15
                function _create_TimeStamp(){
                        return date('Y-m-d H:i:s', time());
                }

                //Prints out a Voucher 
		function _printVoucher($patid){

                        /*
                        Here is the Place to create a nice Voucher Page
                        This Method is called to Print out any Voucher
                        */

                        $_voucher = _getVoucher($patid);
                        echo "Hotspot" . "<br>";
                        echo "" . "<br>";
                        echo "" . "<br>";
                        echo "Gastzugang: " . $_voucher["list"][0]["guestName"]  . "<br>";
                        echo "Kennwort: " . $_voucher["list"][0]["key"]  . "<br>";
                        echo "" . "<br>";
                        echo "Ruckus PHP API" . "<br>";
		}

                //--------------------Main Programm---------------
                //Session Management
                //Check if there is a valid Ruckus API Token available in the Database
                if(!_tokenAmStart(_create_timeStamp())){

                        //No valid Token, so create one
                        $ergebnis = _get_token();
                        //Save the created Token in the database
                        foreach($ergebnis as $key => $val){
                                //DEBUG echo $key . ': ' . $val . ' <br> ';
                                if($key === 'serviceTicket'){
                                        _store_token($val, _create_timeStamp());
                                }
                        }

                }

                //Check if there is a valid Voucher for the patientID
                if(_voucherAmStart($patid, _create_TimeStamp())){
                        //DEBUG echo "<br>Voucher for: " . $patid . " found in Database!<br>";
			_printVoucher($patid);

                }else{
                        //DEBUG echo "<br>No valid Voucher for: " . $patid . " , so get one! <br>";
			//DEBUG echo "<br>Search Voucher in RUCKUSAPI<br>";
			if(_voucherExist($patid)){
				//DEBUG echo "<br>Voucher found over the RuckusAPI<br>";
				//DEBUG echo "<br>Save Voucher to Database<br>";
				_saveVoucherDB(_getVoucher($patid));
				//DEBUG echo "<br>Voucher save Success!<br>";
				_printVoucher($patid);

			}else{
				//DEBUG echo "<br>Create new Voucher in the Ruckus API<br>";
				_createVoucher($patid);
	                        _printVoucher($patid);

			}
                }

                ?>
        </body>
</html>