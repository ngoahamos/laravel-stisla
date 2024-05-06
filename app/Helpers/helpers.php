<?php

if (! function_exists('active_menu_class')) {

    function active_menu_class(string $menu): string
    {
        $path = explode('/', request()->path());
        $init = count($path) > 0 ? $path[0] : '';

        return $init == $menu ? 'active' : '';

    }
}

if (! function_exists('boolean_to_int')) {

    /**
     * @param $var
     * @return int
     */
    function boolean_to_int($var)
    {
        return ($var === 'true' ||
            $var === true   ||
            $var === '1'    ||
            $var === 1      ||
            $var === TRUE   ||
            $var === 'TRUE' ||
            $var === 'True')
            ? 1:0;
    }
}








