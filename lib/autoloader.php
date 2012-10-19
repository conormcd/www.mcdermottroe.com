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

/**
 * Register an autoloader which will load model, view and controller classes
 * where available. If you refer to a class FooController then this autoloader
 * will attempt to load it from $root/controller/FooController.php. All other
 * classes are looked for in lib.
 *
 * @param string $root The directory where the model, view, controller and lib
 *                     directories are located.
 *
 * @return void
 */
function autoloader($root) {
    $loader = function ($name) use ($root) {
        if (preg_match('/(Controller|Model|View)$/', $name, $matches)) {
            $file = sprintf(
                "%s/%s/%s.php",
                $root,
                strtolower($matches[1]),
                $name
            );
            if (file_exists($file) && is_readable($file)) {
                include $file;
            } else {
                throw new Exception("No such class: $name", 500);
            }
        } else if (file_exists("$root/lib/$name.php")) {
            include "$root/lib/$name.php";
        }
    };
    spl_autoload_register($loader);
}

?>
