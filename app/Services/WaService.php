<?php
    namespace App\Services;


    class WaService{
        public function sendMsg($msg, $recevier_number, $nama){
            $msg_open="Kepada Yth. Bapak / Ibu ".$nama."\n\n";
            // $reciver="085880037948";
            // $msg="hello";
            $var['api_id'] = '4132';
            $var['api_key'] = '2NfSdNV3tagyBrFcmA7kAXezOY6ICYeA';
            $var['phone'] = $recevier_number;
            $var['text'] = $msg_open."\n".str_replace("\r", "\n", $msg);
            $ch = curl_init('https://wa3.otomat.web.id');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $var);
            $response = curl_exec($ch);
            curl_close($ch);
            $decode_response=json_decode($response);
            $status=$decode_response->status;
            if($status==="success"){
                $status=true;
                $success=1;
            }
            // var_dump($decode_response->status." ".$recevier_number);
            return $decode_response->status;
        }
    }

?>