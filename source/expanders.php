<?php

namespace Yay\DSL\Expanders {
    use Yay\Engine;
    use Yay\TokenStream;

    function trim(TokenStream $stream, Engine $engine): TokenStream
    {
        return \Pre\Plugin\Expanders\trim($stream, $engine);
    }
}

namespace Pre\Plugin\Expanders {
    use Yay\Engine;
    use Yay\TokenStream;

    function _stream(string $source, Engine $engine): TokenStream
    {
        return TokenStream::fromSource($engine->expand($source, "", Engine::GC_ENGINE_DISABLED));
    }

    function trim(TokenStream $stream, Engine $engine): TokenStream
    {
        $stream = \trim($stream);
        return _stream($stream, $engine);
    }
}
