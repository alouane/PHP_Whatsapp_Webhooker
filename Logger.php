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
 * Content: Logger controller
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

class Logger
{
    /**
     * Summary. Log message
     */
    public function info($log_msg)
    {
        #Init file name
        $log_filename = "log";

        #Check if file exist
        if (!file_exists($log_filename)) {
            // create directory/folder uploads.
            mkdir($log_filename, 0777, true);
        }

        $log_file_data = $log_filename . '/log_' . date('d-M-Y') . '.log';
        // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
        file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
    }
}
