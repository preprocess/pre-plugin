<?php

namespace Yay\DSL\Expanders;

use Yay\Token;
use Yay\TokenStream;

function unvar(TokenStream $ts) : TokenStream {
    $str = str_replace('$', '', (string) $ts);

    return
        TokenStream::fromSequence(
            new Token(
                T_CONSTANT_ENCAPSED_STRING, $str
            )
        )
    ;
}
