<?php

class MY_Input extends CI_Input {

    /**
     * Clean Keys
     *
     * This is a helper function. To prevent malicious users
     * from trying to exploit keys we make sure that keys are
     * only named with alpha-numeric text and a few other items.
     * 
     * Extended to allow: 
     *      - '.' (dot), 
     *      - '[' (open bracket),
     *      - ']' (close bracket)
     * 
     * @access  private
     * @param   string
     * @return  string
     */
    function _clean_input_keys($str) {
        if (!preg_match("/^[~a-z0-9:_\/\.\[\]-]+$/i", $str)) {
            /**
             * Check for Development enviroment - Non-descriptive 
             * error so show me the string that caused the problem 
             */
            if (getenv('ENVIRONMENT') && getenv('ENVIRONMENT') == 'DEVELOPMENT')
                var_dump($str);
            exit('Disallowed Key Characters.');
        }

        // Clean UTF-8 if supported
        if (UTF8_ENABLED === TRUE) {
            $str = $this->uni->clean_string($str);
        }

        return $str;
    }

}

// /?/> /* Should never close php file - if you have a space after can mess your life up */