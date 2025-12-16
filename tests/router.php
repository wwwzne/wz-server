<?php
require __DIR__ . "/../src/utils.php";

use Wwwzne\WzServer\utils;

it('router', function () {
    expect(utils::http_get("http://localhost:8080"))
        ->toBe("a")
        ->and(utils::http_get("http://localhost:8080/a"))
        ->toBe("b")
        ->and(utils::http_get("http://localhost:8080/1"))
        ->toBe("c")
        ->and(utils::http_get("http://localhost:8080/a/a"))
        ->toBe("d")
        ->and(utils::http_get("http://localhost:8080/1/1"))
        ->toBe("e")
        ->and(utils::http_post("http://localhost:8080/b/b"))
        ->toBe("f")
        ->and(utils::http_post("http://localhost:8080/2/2"))
        ->toBe("g")
        ->and(utils::http_get("http://localhost:8080/c/h"))
        ->toBe("h")
        ->and(utils::http_get("http://localhost:8080/d/i"))
        ->toBe("i")
        ->and(utils::http_post("http://localhost:8080/f/j"))
        ->toBe("j")
        ->and(utils::http_post("http://localhost:8080/g/k"))
        ->toBe("k")
        ->and(utils::http_get("http://localhost:8080/h/l"))
        ->toBe("l")
        ->and(utils::http_get("http://localhost:8080/i/m"))
        ->toBe("m")
        ->and(utils::http_post("http://localhost:8080/i/n"))
        ->toBe("n")
        ->and(utils::http_get("http://localhost:8080/j/o"))
        ->toBe("o")
        ->and(utils::http_post("http://localhost:8080/j/p"))
        ->toBe("p")
        ->and(utils::http_post("http://localhost:8080/k/q"))
        ->toBe("q");
});