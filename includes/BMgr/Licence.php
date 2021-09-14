<?php 
    namespace BMgr;
    class Licence {
        private const URL = 'http://apis.siisdz.com/billmgr.php';
        protected $data;
        public function setData($data) {
            $this->data = $data;
        }
        public function get($method = ''){
            $data = $this->data;
            $url = $this::URL;
            $curl = curl_init();
            switch ($method){
               case "POST":
                  curl_setopt($curl, CURLOPT_POST, 1);
                  if ($data)
                     curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                  break;
               case "PUT":
                  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                  if ($data)
                     curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
                  break;
               default:
                  if ($data)
                     $url = sprintf("%s?%s", $url, http_build_query($data));
            } 
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
               'APIKEY: 111111111111111111111',
               'Content-Type: application/json',
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPAUTH, 0);
            // EXECUTE:
            $result = curl_exec($curl);
            if(!$result){echo("Connection Failure");}
            curl_close($curl);
            return $result;
         }
    }
?>