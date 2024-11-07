<?php
function generateFileName()
{
    if (!empty($_FILES['event_image']['name'])) {
        $file_ext = htmlspecialchars(strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION)));
        $file_name = uniqid(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVW0123456789'), true) . '.' . $file_ext;
        $file_to_be_checked = '../uploads/' . $file_name;
        while (file_exists($file_to_be_checked)) {
            $file_name = uniqid('file_', true) . '.' . $file_ext;
            $file_to_be_checked = '../uploads/' . $file_name;
        }
        return $file_name;
    }
    return null;
}
