<?php
const CLICKUP_API_KEY = '';

//TODO Add all lists from clickup

//Real list
const OTRS_LIST = '99999999999999';
const OTRS_USERNAME = 'root@localhost';
const OTRS_PASSWORD = 'password';

function clearVariables(): void
{
    //Unset all variables
    $vars = array_keys(get_defined_vars());
    foreach ($vars as $var) {
        unset($$var);
    }
}