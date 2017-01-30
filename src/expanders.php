<?php

namespace Yay\DSL\Expanders;

use Yay\Token;
use Yay\TokenStream;

function trim($string) : TokenStream {
    $string = \trim($string);

    return TokenStream::fromSequence(
        new Token(
            T_CONSTANT_ENCAPSED_STRING, $string
        )
    );
}
