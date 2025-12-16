<?php
require __DIR__ . "/../src/utils.php";

use Wwwzne\WzServer\utils;

it('utils', function () {
    ob_start();
    utils::echo_bool(true);
    expect(ob_get_clean())->toBe("true\r\n");
    ob_start();
    utils::echo_bool(false);
    expect(ob_get_clean())->toBe("false\r\n");
});
