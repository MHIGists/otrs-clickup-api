<?php


function logClickupTicketError(array $response): void
{
    file_put_contents('ticket_error_logs.json', json_encode($response) . PHP_EOL, FILE_APPEND);
}

function logClientJSError(array $post): void
{
    file_put_contents('client_error_logs.json', json_encode($post) . PHP_EOL, FILE_APPEND);
}

function logOTRSerror(string $error): void
{
    file_put_contents('otrs_error_log.log', json_encode($error) . PHP_EOL, FILE_APPEND);
}

function debug(string $data): void
{
    file_put_contents('debug.txt', $data . PHP_EOL, FILE_APPEND);
}


