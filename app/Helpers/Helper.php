<?php

if (!function_exists('excerpt')) {
    function excerpt($string, $cutOffLength)
    {
        $string = strip_tags($string);
        $charAtPosition = "";
        $titleLength = strlen($string);
        do {
            $cutOffLength++;
            $charAtPosition = substr($string, $cutOffLength, 1);
        } while ($cutOffLength < $titleLength && $charAtPosition != " ");

        return substr($string, 0, $cutOffLength) . '...';
    }
}

if (!function_exists('thumbnail')) {
    function thumbnail($string)
    {
        preg_match('@src="([^"]+)"@', $string, $result);
        if (array_key_exists(1, $result)) {
            return $result[1];
        }
        return '';
    }
}

if (!function_exists('reading_time')) {
    function reading_time($string)
    {
        $content = preg_replace('/(<([^>]+)>)/', '', $string);
        $word = str_word_count($content);
        $reading_time = floor($word / 220);
        return floor($word / 220);
    }
}
