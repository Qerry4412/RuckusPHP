<!--
©COPYRIGHT
RuckusPHP-API - index.php
This program is subject to the GPL3 and may therefore be modified and redistributed,
provided that the resulting code is licensed again under the GPL2.

Programmed by Gerrit Markl 08.10.2022
Email: gerrit.markl@t-online.de
-->


<?php

//Create a SQL Connection an return the Connection Object
function _createSqlCon(){

        //Variables
        $servername = "MYSQLHOST";
        $dbname = "SQLDB";
        $username = "DBUser";
        $password = "DB-User-Password";

        //Initializes the SQL connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        //Error in the SQL Connection? 
        if($conn->connect_error){
                echo("SQL Connection faild: " . $conn->connect_error);
        }
        else{
                //DEBUG echo "Connection successfull" . '<br>';
                return $conn;
        }

}

//Closes the SQL connection
function _closeSqlCon($conn){

        //Close Connection
        mysqli_close($conn);

        //Error? 
        if($conn->connect_error){
                echo("Close faild: " . $conn->connect_error);
        }
        else{
               // echo "<br>Close Success" . '<br>';
        }
}

//Saves a API Token in to the SQL Database
function _store_token($token, $timestamp){

        //Create SQL Conn
        $conn = _createSqlCon();

        //SQL Statement
        $sql_insert = "INSERT INTO Token (Token_ID, Time_ST) VALUES('$token', '$timestamp')";

        if(mysqli_query($conn, $sql_insert)){
               //DEBUG echo "Token saved to Database" . '<br>';
        }
        else{
                echo "ERROR: " . $sql_insert . '<br>' . mysqli_error($conn);
        }

        //Close SQL Conn
        _closeSqlCon($conn);
}

//Returns a valid Token, if the token ist not older then 8H
function _tokenAmStart($timestamp){

        //Create SQL Conn
        $conn = _createSqlCon();

        //SQL Statement
        $sql_insert = "SELECT Token_ID, Time_ST FROM Token WHERE Time_ST=(SELECT MAX(Time_ST) FROM Token)";
        $result = mysqli_query($conn, $sql_insert);

        if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                        //DEBUG echo "Token_ID: " . $row["Token_ID"] . "  ||   Time_ST: " . $row["Time_ST"]  . '<br>';

                        //Get Time_ST from SQL SELECT
                        $akt_ts = $row["Time_ST"];
                        //DEBUG echo "Time_ST From Database: " .  $timestamp . ' < ' . $akt_ts . '<br>';

                        //Add 8H to the Timestamp 
                        $akt_ts = date('Y-m-d H:i:s', strtotime('+8 hours', strtotime($akt_ts))) . '<br>';
                        //DEBUG echo $timestamp . ' > ' . $akt_ts . '<br>';

                        //Check if the Timestamp + 8H is bigger then timestamp from the Token
                        if($timestamp < $akt_ts){
                               //DEBUG echo "Database Token valid until: " . $akt_ts . '<br>' . "Request at: " . $timestamp . '<br>';
                                _closeSqlCon($conn);
                                return true;
                        }
                }
        }
        else{
                //Need some Logic for a empty SQL return!!! (Empty database)
                echo "sql recive faild" . '<br>';
        }

        //Close Connection
        _closeSqlCon($conn);

}

//Return the newes TokenID from the Database
function _get_db_token(){

        //Create SQL Conn
        $conn = _createSqlCon(); 

        //SQl Statement
        $sql_insert = "SELECT Token_ID FROM Token WHERE Time_ST=(SELECT MAX(Time_ST) FROM Token)";
        $result = mysqli_query($conn, $sql_insert);

        //Returns the TokenID
        while($row = mysqli_fetch_assoc($result)){
                //DEBUG echo $row["Token_ID"];
                _closeSqlCon($conn);
                return $row["Token_ID"];
        }

        //Close SQL Conn
        _closeSqlCon($conn);
}

//Check if the given Voucher exists
function _voucherAmStart($vName, $timeSamp){

        //Create SQL Conn
        $conn = _createSqlCon();

        //SQL Statement
        $sql_insert = "SELECT guestname, expirationDate FROM Voucher WHERE guestname='$vName'";
        $result = mysqli_query($conn, $sql_insert);

        //SQL Result empty?
        if(mysqli_num_rows($result) < 0){
                echo "<br>No SQL Results <br>";
        }

        //Parse in to Associativ Array
        $result = mysqli_fetch_assoc($result);

        //Check if the Voucher is expired?
        if($result["expirationDate"] > $timeSamp){
                //DEBUG echo "<br>Valid voucher found!<br>";
                _closeSqlCon($conn);
                return true;
        }
        else{
                //DEBUG echo "<br>No valid Voucher found -> need to Create one<br>";
                _closeSqlCon($conn);
                return false;
        }

        //Close SQL Conn
        _closeSqlCon($conn);

}



//Saves a given Voucher to the Database
function _saveVoucherDB($voucher) {

        //Create SQL Conn
        $conn = _createSqlCon();


        //Fill Variables 
        $id = $voucher["list"][0]["id"];
        $userid = $voucher["list"][0]["userId"];
        $key_pf = $voucher["list"][0]["key"];
        $guestname = $voucher["list"][0]["guestName"];
        $wlan_id = $voucher["list"][0]["wlan"]["id"];
        $wlan_name = $voucher["list"][0]["wlan"]["name"];
        $zone_id = $voucher["list"][0]["zone"]["id"];
        $zone_name = $voucher["list"][0]["zone"]["name"];
        $ssid = $voucher["list"][0]["ssid"];
        $expirationValue = $voucher["list"][0]["passValidFor"]["expirationValue"];
        $expirationUnit = $voucher["list"][0]["passValidFor"]["expirationUnit"];
        $passEffectSince = $voucher["list"][0]["passEffectSince"];
        $passUseDays = $voucher["list"][0]["passUseDays"];
        $maxDevicesAllowed = $voucher["list"][0]["maxDevices"]["maxDevicesAllowed"];
        $maxDevicesNumber = $voucher["list"][0]["maxDevices"]["maxDevicesNumber"];
        $autoGeneratePassword = $voucher["list"][0]["autoGeneratedPassword"];
        $remarks = $voucher["list"][0]["remarks"];
        $generatedOn = date('Y-m-d H:i:s', strtotime($voucher["list"][0]["generatedOn"]));
        $expirationDate = date('Y-m-d H:i:s', strtotime($voucher["list"][0]["expirationDate"]));
        $wlanRestriction = $voucher["list"][0]["wlanRestrition"];
        $requireLoginAgain = $voucher["list"][0]["sessionDuration"]["requireLoginAgain"];
        $sessionValue = $voucher["list"][0]["sessionDuration"]["sessionValue"];
        $sessionUnit = $voucher["list"][0]["sessionDuration"]["sessionUnit"];
        $domainId = $voucher["list"][0]["domainId"];
        $creatorUsername = $voucher["list"][0]["creatorUsername"];
        $isDisabled = $voucher["list"][0]["isDisabled"];

        //SQL Statement
        $sql_insert = "INSERT INTO Voucher (id, userid, key_pf, guestname, wlan_id, wlan_name, zone_id, 
                        zone_name, ssid, expirationValue, expirationUnit, passEffectSince, passUseDays, 
                        maxDevicesAllowed, maxDevicesNumber, autoGeneratedPassword, remarks, generatedOn, 
                        expirationDate, wlanRestriction, requireLoginAgain, sessionValue, sessionUnit, domainId, 
                        creatorUsername, isDisabled) VALUES('$id', '$userid', '$key_pf', '$guestname', '$wlan_id', 
                        '$wlan_name', '$zone_id', '$zone_name', '$ssid', '$expirationValue', '$expirationUnit', '$passEffectSince', 
                        '$passUseDays', '$maxDeviceAllowed', '$maxDevicesNumber', '0', '$remarks', '$generatedOn', '$expirationDate', 
                        '$wlanRestriction', '0', '$sessionValue', '$sessionUnit', '$domainId', '$creatorUsername', '$isDisabled')";

        //DEBUG echo "<br>" . $sql_insert . "<br>";
        if(mysqli_query($conn, $sql_insert)){
                //DEBUG echo "<br>SqlInsert Success<br>";
        }
        else{
                echo "<br>Import token FAIL!: " . mysqli_error($conn) . "<br>";
        }

        //DEBUG Prints out the Voucherdata
        /*DEBUG
        echo "<br>";
        echo $id . "<br>";
        echo $userid . "<br>";
        echo $key_pf . "<br>";
        echo $guestname . "<br>";
        echo $wlan_id . "<br>";
        echo $wlan_name . "<br>";
        echo $zone_id . "<br>";
        echo $zone_name . "<br>";
        echo $ssid . "<br>";
        echo $expirationValue . "<br>";
        echo $expirationUnit . "<br>";
        echo $passEffectSince . "<br>";
        echo $passUseDays . "<br>";
        echo $maxDevicesAllowed . "<br>";
        echo $maxDevicesNumber . "<br>";
        echo "autogenerated: " . $autoGeneratePassword . "<br>";
        echo $passUseDays . "<br>";
        echo $remarks . "<br>";
        echo "generatedOn: " . $generatedOn . "<br>";
        $stuff = date('Y-m-d H:i:s', strtotime($voucher["list"][0]["generatedOn"]));
        echo "TestTimestamp: " . $stuff . "<br>"; 
        echo $expirationDate . "<br>";
        echo $wlanRestriction . "<br>";
        echo "Reqireloginagain: " . $requrieLoginAgain . "<br>";
        echo $sessionValue . "<br>";
        echo $sessionUnit . "<br>";
        echo $domainId . "<br>";
        echo $creatorUsername . "<br>";
        echo $isDisabled . "<br>";
        DEBUG*/

        //Close SQL Conn
        _closeSqlCon($conn);

}

?>
