--DESCRIPTION--

Test trim and studly macros

--GIVEN--

macro ·unsafe {
    private T_VARIABLE·name {
        ·repeat(
            ·chain(
                get,
                ·between(
                    ·token("{"), ·layer(), ·token("}")
                )·getter_body
            )·getter
        )·accessors
    }
    ·optional(·token(";"))
} >> {
    private T_VARIABLE·name;

    ··trim(
        ·accessors ··· {
            ·getter ?··· {
                private function ··concat(get ··studly(··unvar(T_VARIABLE·name)))() {
                    ·getter_body
                }
            }
        }
    )
}

class Sprocket
{
    private $name {
        get {
            return "chris";
        }
    }

    private $age {
        get {
            return 30;
        }
    }

    private $country_of_birth {
        get {
            return "South Africa";
        }
    }
}

--EXPECT--

class Sprocket
{
    private $name;
    private function getName()
    {
        return "chris";
    }
    private $age;
    private function getAge()
    {
        return 30;
    }
    private $country_of_birth;
    private function getCountryOfBirth()
    {
        return "South Africa";
    }
}
