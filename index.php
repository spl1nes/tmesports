<?php

include __DIR__ . '/db.php';

use phpOMS\Message\Http\HttpRequest;

$request = HttpRequest::createFromSuperglobals();
$page = $request->getDataString('page') ?? 'ranking';

?>
<!DOCTYPE html>
<html>
    <head>

    </head>
    <body>
        <?php include __DIR__ . '/tpl/' . $page . '.tpl.php'; ?>