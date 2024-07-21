<?php

if (! function_exists('active_menu_class')) {

    function active_menu_class(string $menu): string
    {
        $path = explode('/', request()->path());
        $init = count($path) > 0 ? $path[0] : '';

        return $init == $menu ? 'active' : '';

    }
}









