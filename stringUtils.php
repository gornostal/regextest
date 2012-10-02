<?php

class StrUtils{
    static function clean( $in ){
        return trim( $in );
    }

    static function startsWith( $haystack, $needle ){
        return substr( $haystack, 0, strlen($needle) ) == (string)$needle;
    }

    static function endsWith( $haystack, $needle ){
        return substr( $haystack, strlen($haystack)-strlen($needle) ) == (string)$needle;
    }

    static function contains( $haystack, $needle){
        return strpos($haystack, $needle);
    }

    /**
     * Remove the new line and HTML tag from a string
     * @param String $in The text to clean
     * @return String
     */
    static function stripHTMLTag( $text ){
        $text = utf8_encode( $text );
        $text = self::stripTags( $text );
        $text = html_entity_decode( $text, ENT_COMPAT, 'UTF-8' );
        $text = self::replaceSpecial( $text );
        $text = preg_replace( '/\s+/', ' ', $text );
        return $text;
    }

    /**
     * Replace tags with whitespace so that we can still have delimiters between the text inside them
     * @param String $text The text from which to strip tags
     */
    static function stripTags( $text ){
        $text = preg_replace( '/<[^>]*>/', ' ', $text );
        return $text;
    }

    /**
     * Replace special characters with ascii counterparts to make parsing easier
     * @param String $text The text from which to replace characters
     */
    static function replaceSpecial( $text ){
        // These mappings are from a unicode code point to zero or more other characters
        $unicodeMappings = array(
                                 '0080' => 'EUR',
                                 '0085' => ' ',
                                 '00B7' => ' ',
                                 '0091' => '\'',  // Left single quote
                                 '0092' => '\'',  // Right single quote
                                 '0093' => '"',   // Left double quote
                                 '0094' => '"',   // Right double quote
                                 '0096' => '-',   // Some kind of dash/hyphen/minus
                                 '0097' => '-',   // Some kind of dash/hyphen/minus
                                 '0099' => ' ',   // Trademark Symbol
                                 '00A0' => ' ',   // nbsp
                                 '00A3' => 'GBP',
                                 '00A5' => 'YEN',
                                 '00A9' => ' ',   // Copyright Symbol
                                 '00AE' => ' ',   // Registered Symbol
                                 '00B7' => ' ',
                                 '00C2' => ' ',
                                 '20AC' => 'EUR',
                                 );
        foreach( $unicodeMappings as $unicode => $replacement ){
            // A way to generate characters from their unicode code points is to json decode a string of the form "\uXXXX" (quotes included)
            $text = str_replace( json_decode( '"\u'.$unicode.'"' ), $replacement, $text );
        }
        return $text;
    }
}
