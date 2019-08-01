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
 * Content: Account refill controller
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

class AccountRefills
{
    #Init refill methods
    private $refill_modes = array("orange", "inwi", "iam");

    /**
     * Summary. Check refill mode validity
     */
    public function checkValdity($mode)
    {
        return !in_array(strtolower($mode), $this->refill_modes);
    }


    /**
     * Summary. Check if we already sent a refill code to that user
     */
    public function checkRefillHistory($user_id)
    {
         #Init query
         $stmt = $conn->prepare("select id from refill_history where used = ? limit 1");
         $stmt->bind_param('i', $user_id);
         $stmt->execute();
 
         #Set the resulting array to associative
         $result = $stmt->get_result();
 
         #Check availability
         return $result->num_rows > 0 ; 
    }

    /**
     * Summary. Get new refill code => refill mode not supported in current app
     */
    public function getCode($mode)
    {
        global $conn;

        #Init query
        $stmt = $conn->prepare("select code from refills where used = 0 limit 1");
        $stmt->execute();

        #Set the resulting array to associative
        $result = $stmt->get_result();

        #Check availability
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            return $row['code'];
        }

        return false;
    }

}
