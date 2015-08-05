<?php

// project Number : 759604048159
// SHA1 : C0:81:C1:92:46:65:A0:4E:30:D5:2B:ED:6C:98:32:80:C1:E0:04:62;com.appinest.pistache
// clé de l'API : AIzaSyDKPInEtvNUi6oXiglPp1OwDK2B9SusiRU

// API access key from Google API's Console
//define( 'API_ACCESS_KEY', 'AIzaSyDKPInEtvNUi6oXiglPp1OwDK2B9SusiRU' ); //cle android
define( 'API_ACCESS_KEY', 'AIzaSyCqOnRxHXukk-MzVnjO_a2gwMHR087osQY' ); //cle server
//define( 'API_ACCESS_KEY', '759604048159' );

$registrationIds = array("APA91bEw3M4XxLwcfv7-JZd-4jTuCpGP1TRmJ0JyG2MAPbOWUzW5_o-_eyUxVrP1Nds-yBIIZtdq0j4Ud7AvDRZeKWClV28fhQKFYZGYDi_3lzFFIaQqV4_jj2BGtrKAF2HFW7hSMtY1");
//$registrationIds = array("APA91bGjfYDAz8c-9eI6JNOpbQV6qswpuTG5okT0ChYtrLTgOKUwDjthkazzuPPO0W_A6vTfWk-RbrFfSERuVNqDFg9tozH_3Z5GgNLUtXU5Pi7l-5t45sJ2naVis3VGEUj8-m44LeX-oc6KvGxD8194ub7lT2wgqZK6Ey09wOC6Ixa-Y0hfRP4");

// prep the bundle
$msg = array
(
    'title'         => 'This is a title. title',
    'subtitle'      => 'This is a subtitle. subtitle',
    'message'       => 'here is a message. message'
);

$fields = array
(
    'registration_ids'  => $registrationIds,
    'data'              => $msg
);

$headers = array
(
    'Authorization: key=' . API_ACCESS_KEY,
    'Content-Type: application/json'
);

$ch = curl_init();
curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
curl_setopt( $ch,CURLOPT_POST, true );
curl_setopt( $ch,CURLOPT_RETURNTRANSFER, false );
curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
$result = curl_exec($ch );
curl_close( $ch );

echo $result;

?>