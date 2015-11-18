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

include_once __DIR__ . '/../../../../vendor/autoload.php';

use ccny\scidiv\cores\view\JSONMessageSender as JSONMessageSender;
use ccny\scidiv\cores\model\FacilityDataHandler as FacilityDataHandler;

$msg_sender = new JSONMessageSender();

$resource_name = null;

if (isset($_POST['rid']) && !empty($_POST['rid']))
    $resource_name = $_POST['rid'];

try {
    $data_handler = new FacilityDataHandler();
    $data = $data_handler->getServiceSelectorContent($resource_name);
} 
catch (SystemException $e){
    
    $client_error = $e->getUIMsg();
    
    if( empty($client_error)){
        $client_error = "Operation failed: Error code " . $e->getCode();
    }
    
    $msg_sender->onError(null, $client_error);
}
catch (\Exception $e) {
    $err_msg = "Unexpected error:  " . $e->getCode();
    $msg_sender->onError(null, $err_msg);
}

$msg_sender->onResult($data, null);
