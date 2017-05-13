<?php

namespace Yay\DSL\Expanders;

use Yay\Engine;
use Yay\Token;
use Yay\TokenStream;

function trim(TokenStream $stream, Engine $engine): TokenStream
{
    $stream = \trim($stream);

    return TokenStream::fromSource(
        $engine->expand($stream, "", Engine::GC_ENGINE_DISABLED)
    );
}

function collapse(TokenStream $stream, Engine $engine): TokenStream
{
    $stream = \preg_replace("/\n+/", "", $stream);

    return TokenStream::fromSource(
        $engine->expand($stream, "", Engine::GC_ENGINE_DISABLED)
    );
}

function studly(TokenStream $stream, Engine $engine): TokenStream
{
    $stream = \str_replace(["-", "_"], " ", $stream);
    $stream = \str_replace(" ", "", \ucwords($stream));

    return TokenStream::fromSource(
        $engine->expand($stream, "", Engine::GC_ENGINE_DISABLED)
    );
}
