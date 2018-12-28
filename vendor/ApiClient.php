<?php
/**
 * Created by IntelliJ IDEA.
 * User: maestro
 * Date: 02.11.15
 * Time: 4:11
 */
class ApiClient
{

    const SCHEMA = 'https';
    const API_URI = '/api';
    private $host;
    private $port;
    private $api_url;
    private $api_key;
    private $api_secret;
    private $debug = false;

    /**
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * ApiClient constructor.
     * @param $host string hostname
     * @param $port int port
     */
    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
        $this->api_url = self::SCHEMA . '://' . $host . ':' . $port . self::API_URI;
    }

    /**
     * @param mixed $api_key
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * @param mixed $api_secret
     */
    public function setApiSecret($api_secret)
    {
        $this->api_secret = $api_secret;
    }

    /**
     * Sends echo request.
     *
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function Request($request_uri, $data)
    {
        
        return $this->sendRequest($request_uri, $data);
    }

    private function sendRequest($request_uri, $data)
    {
      
           $ch = curl_init($this->api_url . $request_uri);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($this->debug) {
                curl_setopt($ch, CURLOPT_VERBOSE, true);
            }
            $payload = json_encode($data);
            $date = gmdate(DATE_RFC2822);
		
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        
		   //post ok
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

			//get
			//curl_setopt($ch, CURLOPT_HTTPGET, true);

			//DELETE
			//curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");   
        
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Date: ' . $date,
                    'Content-Length: ' . strlen($payload),
                    'Authorization: ' . $this->createAuthHeader(
                        self::API_URI . $request_uri,
                        $date,
                        $payload
                    ))
            );
            
            $res = curl_exec($ch);
            $error_code = curl_errno($ch);
            $error = curl_error($ch);
            
            if ($error_code !== 0) {
                throw new \Exception('Error #' . $error_code . ': ' . $error);
            }

            $info = curl_getinfo($ch);
             var_dump($res);exit;
            $http_code = $info['http_code'];
            if ($http_code != 200) {
                if ($this->debug) {
                    syslog(LOG_DEBUG, 'Response: ' . print_r($info) . $http_code);
                }
                throw new \Exception('Server Status: ' . $http_code);
            }
           
            curl_close($ch);
            return json_decode($res);

        
    }

    private function createAuthHeader($uri, $date, $payload)
    {
        $string_to_sign =
            "POST\n" .
            self::SCHEMA . "\n" .
            $this->host . ':' . $this->port . "\n" .
            $uri . "\n" .
            "application/json\n" .
            $this->api_key . "\n" .
            $date . "\n" .
            $payload . "\n";

        if ($this->debug) {

            syslog(LOG_DEBUG, "Signing:\n" . $string_to_sign);
        }


        $digest = hash_hmac('sha512', $string_to_sign, $this->api_secret, true);
        return 'HmacSHA512 ' . $this->api_key . ':' . base64_encode($digest);
    }

}