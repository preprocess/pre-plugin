<?php

namespace Yay {
    use Yay\Parser;

    function functionModifiers(): Parser {
        return \Pre\Plugin\Parsers\functionModifiers();
    }

    function functionArgument(): Parser {
        return \Pre\Plugin\Parsers\functionArgument();
    }

    function functionArguments(): Parser {
        return \Pre\Plugin\Parsers\functionArguments();
    }

    function functionReturn(): Parser {
        return \Pre\Plugin\Parsers\functionReturn();
    }
}

namespace Pre\Plugin\Parsers {
    use Yay\Parser;
    use function Yay\buffer;
    use function Yay\chain;
    use function Yay\either;
    use function Yay\layer;
    use function Yay\ls;
    use function Yay\ns;
    use function Yay\optional;
    use function Yay\repeat;
    use function Yay\token;

    function functionModifiers(): Parser {
        return optional(
            repeat(
                either(
                    buffer("public"),
                    buffer("protected"),
                    buffer("private"),
                    buffer("static")
                )->as("functionModifier")
            )
        )->as("functionModifiers");
    }

    function functionArgument(): Parser {
        return chain(
            optional(
                either(
                    ns(),
                    token(T_ARRAY),
                    token(T_CALLABLE)
                )
            )->as("functionArgumentType"),
            token(T_VARIABLE)->as("functionArgumentName"),
            optional(buffer("="))->as("functionArgumentEquals"),
            optional(token(T_NEW))->as("functionArgumentNew"),
            optional(
                either(
                    chain(
                        ns(),
                        buffer("("),
                        layer(),
                        buffer(")")
                    ),
                    token(T_CONSTANT_ENCAPSED_STRING),
                    token(T_LNUMBER),
                    token(T_DNUMBER),
                    token(T_STRING),
                    chain(
                        buffer("["),
                        layer(),
                        buffer("]")
                    )
                )
            )->as("functionArgumentValue")
        )->as("functionArgument");
    }

    function functionArguments(): Parser {
        return optional(
            ls(
                functionArgument(),
                buffer(",")
            )
        )->as("functionArguments");
    }

    function functionReturn(): Parser {
        return optional(
            chain(
                buffer(":"),
                either(
                    ns(),
                    token(T_ARRAY),
                    token(T_CALLABLE)
                )->as("functionReturnType")
            )
        )->as("functionReturn");
    }    
}
