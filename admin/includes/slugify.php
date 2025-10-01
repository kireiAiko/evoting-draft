<?php
function slugify($string) {
    // Remove common prepositions (optional)
    $preps = array('in', 'at', 'on', 'by', 'into', 'off', 'onto', 'from', 'to', 'with', 'a', 'an', 'the', 'using', 'for');
    $pattern = '/\b(?:' . join('|', $preps) . ')\b/i';
    $string = preg_replace($pattern, '', $string);

    // Replace spaces and non-letter/digit characters with hyphens
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);

    // Transliterate to ASCII
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);

    // Convert to lowercase and trim
    $string = strtolower($string);
    $string = trim($string, '-');

    // Remove unwanted characters
    $string = preg_replace('~[^-\w]+~', '', $string);

    // Fallback if result is empty
    return !empty($string) ? $string : 'slug';
}
?>
