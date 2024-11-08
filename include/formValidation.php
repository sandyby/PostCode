<?php
function isValid($data)
{
    return isset($data) && !empty(trim($data));
}

function isString($data)
{
    return preg_match('/^[a-zA-Z0-9 .,?!-]+$/', $data);
}

function isUsername($data)
{
    return preg_match('/^[a-zA-Z0-9._]{3,16}+$/', $data);
}

function isEmail($data)
{
    return preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i', $data);
}

function isPassword($data)
{
    return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&_])[A-Za-z\d@$!%*?&_]{8,}$/', $data);
}

function containAdmin($data)
{
    return preg_match('/admin/i', $data);
}

function isEventName($data)
{
    return preg_match('/^[a-zA-Z0-9\s\.,\'-]{1,100}$/', $data);
}

function isLocation($data)
{
    return preg_match('/^[a-zA-Z0-9\s,.\-!&\']{1,100}$/', $data);
}

function isDescription($data)
{
    return preg_match('/^[a-zA-Z0-9\s,.\-!&\']{1,500}$/', $data);
}

function isDigits($data)
{
    return preg_match('/^\d+$/', $data);
}

function isDate($data)
{
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d && $d->format('Y-m-d') === $data;
}
