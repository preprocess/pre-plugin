--DESCRIPTION--

Test process macro

--GIVEN--

process "one.pre";

$val = process "two.pre";

process "one" . "two" . "three" . THING;
process "one" ."two". "three".THING;

process __DIR__ . "thing";
process .."thing";

--EXPECT--

\Pre\Plugin\process("one.pre");
$val = \Pre\Plugin\process("two.pre");
\Pre\Plugin\process("one" . "two" . "three" . THING);
\Pre\Plugin\process("one" . "two" . "three" . THING);
\Pre\Plugin\process(__DIR__ . "thing");
\Pre\Plugin\process(__DIR__ . "thing");
