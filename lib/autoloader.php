<?php

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
    } else if (preg_match('/^Fake.*/', $name)) {
        $file = "$root/test/phpunit/fakes/$name.php";
    } else if (file_exists("$root/lib/$name.php")) {
        $file = "$root/lib/$name.php";
    } else if (file_exists("$root/test/phpunit/$name.php")) {
        $file = "$root/test/phpunit/$name.php";
    } else if (preg_match("#^Klein\\\\#", $name, $match)) {
        $class = preg_replace('/\\\\/', '/', $name);
        $file = "$root/lib/klein/src/$class.php";
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
