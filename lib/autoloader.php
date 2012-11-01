<?php

/*
 * Copyright (c) 2012, Conor McDermottroe
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * Register an autoloader which will load classes that fit a standard pattern.
 *
 * FooController -> controller/FooController.php
 * FooModel -> model/FooModel.php
 * Bar -> lib/Bar.php
 * FooControllerTest -> test/phpunit/controller/FooControllerTest.php
 * FooModelTestCase -> test/phpunit/model/FooModelTestCase.php
 * BarTest -> test/phpunit/BarTest.php
 */
$root = dirname(__DIR__);
$loader = function ($name) use ($root) {
    $file = null;
    if (preg_match('/(Controller|Model)$/', $name, $match)) {
        $file = sprintf(
            "%s/%s/%s.php",
            $root,
            strtolower($match[1]),
            $name
        );
    } else if (preg_match('/(Controller|Model)Test(?:Case)?$/', $name, $match)) {
        $file = sprintf(
            "%s/test/phpunit/%s/%s.php",
            $root,
            strtolower($match[1]),
            $name
        );
    } else if (file_exists("$root/lib/$name.php")) {
        $file = "$root/lib/$name.php";
    } else if (file_exists("$root/test/phpunit/$name.php")) {
        $file = "$root/test/phpunit/$name.php";
    }
    if ($file) {
        if (file_exists($file) && is_readable($file)) {
            include $file;
        } else {
            throw new Exception("No such class: $name", 500);
        }
    }
};
spl_autoload_register($loader);

?>
