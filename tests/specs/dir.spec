--DESCRIPTION--

Test process macro

--GIVEN--

process "one.pre";

$val = process "two.pre";

process "one" . "two" . "three" . CONST;

--EXPECT--

Pre\processAndRequire("one.pre");

$val= Pre\processAndRequire("two.pre");

Pre\processAndRequire("one"."two"."three".CONST);
