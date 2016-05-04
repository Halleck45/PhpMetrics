<?php
namespace Hal\Component\Token;

class Tokenizer
{
    public function tokenize($code)
    {
        // espace chars
        $code = preg_replace('!(\}|\)|;)\?!', '$1 ?', $code);

        // remove one line comments
        $code = preg_replace('!((\/\/|#).*\n)!', Token::T_COMMENT . ' ', $code);

        // remove EOL
        $code = preg_replace('!(\s+)!', ' ', $code);

        // replace strings
        $code = preg_replace('/"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"/s', Token::T_VALUE_STRING, $code);
        $code = preg_replace("/'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'/s", Token::T_VALUE_STRING, $code);

        // replace booleans
        $code = preg_replace('/(true|false)/i', Token::T_VALUE_BOOLEAN, $code);

        // remove code which is not between <?php ? > tags
        $code = preg_replace('!^(.*?)<\?!', '<?', $code);
        $code = preg_replace('!\?>(.*?)<\?!', '<?', $code);
        $code = preg_replace('!(<\?php|<\?)!', '', $code);

        // remove multiline comments
        $code = preg_replace('!/\*+!', Token::T_COMMENT_OPEN, $code);
        $code = preg_replace('!\*/!', Token::T_COMMENT_CLOSE, $code);

        // type cast
        $code = preg_replace('!(\((string|bool|float|object|array)\))!', Token::T_CAST, $code);

        // return void
        $code = preg_replace('!return;!i', Token::T_RETURN_VOID, $code);

        // split tokens
        return preg_split('!\s|;|,|(\{|\}|\(|\))!', $code, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }
}