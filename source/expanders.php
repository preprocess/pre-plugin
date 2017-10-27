<?php

namespace Yay\DSL\Expanders;

use Yay\Engine;
use Yay\TokenStream;

function trim(TokenStream $stream, Engine $engine): TokenStream
{
    $stream = \trim($stream);

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

function collapse(TokenStream $stream, Engine $engine): TokenStream
{
    $stream = \preg_replace("/\n\s+\n/", "\n\n", $stream);
    $stream = \preg_replace("/\n{3}/", "\n\n", $stream);

    return TokenStream::fromSource(
        $engine->expand($stream, "", Engine::GC_ENGINE_DISABLED)
    );
}
