<?php
/**
 * @var \Throwable $exception
 */
?>

<style>

.header {
    margin-top: 1px;
    background-color: #9B410E;
    padding: 8px;
}
.header h5 {
    margin: 0;
}

.body {
    border: 2px solid #9B410E;
    padding: 5px;
}
.trace {
    text-align: right;
}
.file {
    font-size: 18px;
    font-weight: bold;
}
</style>

<header class="header">
    <h5>Exception</h5>
</header>
<div class="body">
    <p><b>Message:</b> <?= $exception->getMessage() ?></p>
    <p><b>Class:</b> <?= get_class($exception) ?></p>
    <p><b>Code:</b> <?= $exception->getCode() ?></p>
    <p><b>File:</b> <?= $exception->getFile() ?></p>
    <p><b>Line:</b> <?= $exception->getLine() ?></p>
</div>


<header class="header">
    <h5>Stack trace</h5>
</header>
<div class="body">
    <table class="trace">
        <?php foreach ($exception->getTrace() as $trace): ?>
            <tr>
                <td>
                    <?php if (isset($trace['class']) && isset($trace['type']) && isset($trace['function'])): ?>
                        <span class="class"><?= 'at ' . $trace['class'] . $trace['type'] . $trace['function'] ?></span>
                    <?php endif ?>
                    <span class="file"><?= 'in ' . $trace['file'] . ':' . $trace['line'] ?></span>
                </td>
            </tr>
        <?php endforeach ?>
    </table>

</div>

<header class="header">
    <h5>Code</h5>
</header>
<div class="body">

    <div class="code">
        <?= highlight_string($code, true) ?>
    </div>
</div>

<header class="header">
    <h5>Info</h5>
</header>
<div class="body">

    <div>
        <p>$_GET</p>
        <p><?= highlight_string(print_r($_GET, true), true) ?></p>
    </div>

    <div>
        <p>$_POST</p>
        <p><?= highlight_string(print_r($_POST, true), true) ?></p>
    </div>

    <?php if (\PHP_SESSION_ACTIVE == session_status()): ?>
    <div>
        <p>$_SESSION</p>
        <p><?= highlight_string(print_r($_SESSION, true), true) ?></p>
    </div>
    <?php endif ?>
    <div>
        <p>$_COOKIE</p>
        <p><?= highlight_string(print_r($_COOKIE, true), true) ?></p>
    </div>

    <div>
        <p>$_SERVER</p>
        <p><?= highlight_string(print_r($_SERVER, true), true) ?></p>
    </div>

    <?php if (function_exists('pinba_get_info')): ?>
    <div>
        <p>Pinba</p>
        <p><?= highlight_string(print_r(pinba_get_info(), true), true) ?></p>
    </div>
    <?php endif ?>
</div>



