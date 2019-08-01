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
 * Content: RefillListner
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE);

require 'AccountRefills.php';
require 'Logger.php';
require 'WhatsappController.php';

#Init models
$LG = new Logger();
$refill = new AccountRefills();

#Init whastapp controller
$WC = new WhatsappController();

/**
 * TODO. FIND a way to verify request authencity
 */
#Check sender identity
// $verified = $WC->verifyRequest();

if ($verified) {
    #Get body aprams
    $body = $_POST;

    #Log body content
    $LG->info("Call : " . json_encode($body));

    #Check webhook'stype
    if (isset($body['messages'])) {
        #Init message
        $message = $body['messages'][0];

        #Check mssg inboud type
        if (isset($message->text)) {
            #Init text obj
            $text = $message->text;

            #Check body's type (Orange | Inwi | IAM)
            if ($refill->checkValdity($text->body)) {
                #Get user id
                $user_id = $message->from;

                #Check if no refill was sent to that user
                if (!$refill->checkRefillHistory($user_id)) {
                    #Get new refill code
                    $code = $refill->getCode($text->body);

                    /**
                     * TODO. Add a wait time to secure sent messages & prevent sync errors
                     */

                    #Get a valid wp token
                    $token = $WC->getValidToken();

                    #Send message to client if code was found
                    if ($code) {
                        $WC->sendMessage($token, $user_id, $code);
                    }

                }

            }
        }
    }

}

// Reply with an empty 200 response to indicate to whatsapp the message was received correctly.
header("HTTP/1.1 200 OK");
