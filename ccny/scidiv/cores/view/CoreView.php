<?php

/*
 * The MIT License
 *
 * Copyright 2015 Daniel Fimiarz <dfimiarz@ccny.cuny.edu>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace ccny\scidiv\cores\view;

require_once __DIR__ . '/../../../../ext/twig/lib/Twig/Autoloader.php';
/**
 * Description of SessionDetails
 *
 * @author WORK 1328
 */
class CoreView {
    //put your code here
    private $twig_env;
    private $tmpl;
    
    function __construct($template_dir) {

        \Twig_Autoloader::register();

        $loader = new \Twig_Loader_Filesystem($template_dir);
        $this->twig_env = new \Twig_Environment($loader, array('debug' => FALSE));
    }
    
    function loadTemplate($filename)
    {
        $this->tmpl = $this->twig_env->loadTemplate($filename);
    }

    public function render($arr_variables) {

        if (is_array($arr_variables)) {
            return $this->tmpl->render($arr_variables);
        } 
           
        return $this->tmpl->render([]);
        
    }

}
