<?php
class Input
{
    static function clean(string $data) : string
    {
        return htmlspecialchars($data, ENT_QUOTES, 'utf-8');
    }

    static function test_input(string $data) : string
    {
        $data = trim($data);
        $data = stripslashes($data);
        return Input::clean($data);
    }
}
