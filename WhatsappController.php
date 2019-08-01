<?php
/**
 * @author: ALOUANE Nour-Eddine
 *
 * @version 0.1
 *
 * @email: alouane00@gmail.com
 * @date: 01/08/2019
 * @company: Arkia
 * @country: Morocco
 * Copyright (c) Richmedia
 * Content: Whatsapp controller
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

class WhatsappController
{
    #Init wp user credentials
    private $USERNAME = "XXXXX";
    private $PASSWORD = "XXXXX";

    #Init Wahtsapp urls
    private $HOST = "XXXXXXX";
    private $AUTH_URL = "/v1/users/login";
    private $MESSAGE_URL = "/v1/messages";

    #Whatsapp message templates
    private $REFILL_CODE_TEMPLATE = "Félicitation! vous venez de recevoir votre code de recharge: ";

    
    /**
     * Summary. Send new message
     */
    public function sendMessage($token, $user_id, $code)
    {
        #Init body
        $body = array(
            'recipient_type' => 'individual',
            'to' => $user_id,
            'type' => 'text',
            'text' => array('body' => $this->REFILL_CODE_TEMPLATE.$code)
        );

        #Call the message api
        $res = $this->CallAPI('POST', $this->MESSAGE_URL, $token, 'Bearer', $body);

    }

    /**
     * Summary. Check if we have a valid wp token
     */
    public function checkValidToken()
    {
         #Init videos query
         $stmt = $conn->prepare("select token from whatsapp_tokens where expires_after > timestampadd(hour, 1, now()) limit 1");
         $stmt->execute();
 
         #Set the resulting array to associative
         $result = $stmt->get_result();
 
         #Check availability
         if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            return $row['token'];
         }

         return  false; 
    }

    /**
     * Summary. Store a new token
     */
    public function storeToken($token, $expires_after)
    {
         #Init videos query
         $stmt = $conn->prepare("insert into whatsapp_tokens set token = ?, expires_after = ?");
         $stmt->bind_param('ss', $token, $expires_after);
         $stmt->execute();
 
         return $stmt->affected_rows != -1;
    }

    /**
     * Summary. Get a valid wp token
     */
    public function getValidToken()
    {
        #Check if we have a valid token somewhere in the database
        $token = $this->checkValidToken();
        if($token) return $token;
        else{
            /*No valid token was found => We need to generate a new one*/
            #Generate base64 authorization
            $token = base64_encode($this->USERNAME.':'.$this->PASSWORD);

            #Call the login api
            $res = $this->CallAPI('POST', $this->AUTH_URL, $token, 'Basic');
            
            #Decode data
            $result = json_decode($res);

            #init user
            $user = $result->users[0];
            #Store the new generated token
            $this->storeToken($user->token, $user->expires_after);

            #return token
            return $token;
        }
    }

    /**
     * Summary. Standard API call
     */
    function CallAPI($method, $url, $token, $type, $data = false)
    {
        $curl = curl_init();

        switch ($method)
        {
            case "POST":

                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        //Authorization
        $authorization = "Authorization: $type $token";
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));


        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_VERBOSE, TRUE);

        // Optional Authentication:
        // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
}