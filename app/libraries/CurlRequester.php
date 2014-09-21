<?php
class CurlRequester {

    public static function requestCURL($method, $url, $postData = '', $type = 'json'){
        $ch = curl_init($url);

        if($method != 'GET' ){

            switch($method){
                case 'POST': curl_setopt($ch, CURLOPT_POST, 1);
                break;
                case 'PUT' : curl_setopt($ch, CURLOPT_PUT, 1);
                break;
                case 'DELETE' : curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            }


            if($method == 'POST' || $method == 'PUT'){
                $contentTypes = array(
                    'json' => array('application/json','json'),
                    'xml' => array('application/xml','xml'),
                );

                $type = $contentTypes[$type];

                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-type: application/$type[0]",
                    "x-li-format: $type[1]",
                    'Connection: close'
                    )
                );
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }

        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        return $result;
    }

}