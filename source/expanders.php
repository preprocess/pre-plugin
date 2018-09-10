<?php

namespace Yay\DSL\Expanders {
    use Yay\Engine;
    use Yay\TokenStream;

    function trim(TokenStream $stream, Engine $engine): TokenStream {
        return \Pre\Plugin\Expanders\trim($stream, $engine);
    }

    function studly(TokenStream $stream, Engine $engine): TokenStream {
        return \Pre\Plugin\Expanders\studly($stream, $engine);
    }

    function collapse(TokenStream $stream, Engine $engine): TokenStream {
        return \Pre\Plugin\Expanders\collapse($stream, $engine);
    }

    function functionModifiers(TokenStream $stream, Engine $engine): TokenStream {
        return \Pre\Plugin\Expanders\functionModifiers($stream, $engine);
    }

    function functionArgument(TokenStream $stream, Engine $engine): TokenStream {
        return \Pre\Plugin\Expanders\functionArgument($stream, $engine);
    }

    function functionReturn(TokenStream $stream, Engine $engine): TokenStream {
        return \Pre\Plugin\Expanders\functionReturn($stream, $engine);
    }
}

namespace Pre\Plugin\Expanders {
    use Yay\Engine;
    use Yay\TokenStream;

    function _stream(string $source, Engine $engine): TokenStream {
        return TokenStream::fromSource(
            $engine->expand($source, "", Engine::GC_ENGINE_DISABLED)
        );
    }

    function trim(TokenStream $stream, Engine $engine): TokenStream {
        $stream = \trim($stream);
        return _stream($stream, $engine);
    }

    function studly(TokenStream $stream, Engine $engine): TokenStream {
        $stream = \str_replace(["-", "_"], " ", $stream);
        $stream = \str_replace(" ", "", \ucwords($stream));
        return _stream($stream, $engine);
    }

    function collapse(TokenStream $stream, Engine $engine): TokenStream {
        $stream = \preg_replace("/\n\s+\n/", "\n\n", $stream);
        $stream = \preg_replace("/\n{3}/", "\n\n", $stream);
        return _stream($stream, $engine);
    }

    function functionModifiers(TokenStream $stream, Engine $engine): TokenStream {
        $parts = [];

        while ($token = $stream->current()) {
            \array_push($parts, (string) $token);
            $stream->next();
        }

        $source = \join(" ", $parts);

        return _stream($source, $engine);
    }

    function functionArgument(TokenStream $stream, Engine $engine): TokenStream {
        $parts = [];
        $previous = null;
        $nullable = false;

        while ($token = $stream->current()) {
            \array_push($parts, $token);

            if ($token->type() === T_NEW) {
                \array_pop($parts);
                $nullable = true;
                break;
            }

            if ($token->value() === "(" && $previous && $previous->type() === T_STRING) {
                \array_pop($parts);
                \array_pop($parts);
                $nullable = true;
                break;
            }

            $previous = $token;
            $stream->next();
        }

        if ($nullable) {
            \array_push($parts, "null");
        }

        $source = \join(" ", $parts);
        $source = \str_replace("[ ]", "[]", $source);

        return _stream($source, $engine);
    }

    function functionReturn(TokenStream $stream, Engine $engine): TokenStream {
        if ($stream->isEmpty()) {
            return _stream("", $engine);
        }

        // ...skip the :
        $stream->next();

        // ...get the return type
        $token = $stream->current();

        $source = ": {$token}";

        return _stream($source, $engine);
    }
}
