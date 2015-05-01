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

namespace ccny\scidiv\cores\components;

include_once __DIR__ . '/DbConnectInfo.php';

include_once __DIR__ . '/CoreComponent.php';
include_once __DIR__ . '/SystemConstants.php';
include_once __DIR__ . '/UserRoleManager.php';
include_once __DIR__ . '/../model/PermissionManager.php';
include_once __DIR__ . '/../model/CoreUser.php';
include_once __DIR__ . '/../model/CoreEventDetails.php';
include_once __DIR__ . '/../model/CoreEventDetailsDAO.php';

use ccny\scidiv\cores\components\CoreComponent as CoreComponent;
use ccny\scidiv\cores\model\CoreUser as CoreUser;
use ccny\scidiv\cores\model\PermissionManager as PermissionManager;
use ccny\scidiv\cores\components\DbConnectInfo as DbConnectInfo;
use ccny\scidiv\cores\components\UserRoleManager as UserRoleManager;
use ccny\scidiv\cores\model\CoreEventDetails as CoreEventDetails;
use ccny\scidiv\cores\model\CoreEventDetailsDAO as CoreEventDetailsDAO;

/**
 * Description of EventDetailsHandler
 *
 * @author WORK 1328
 */
class EventDetailsHandler extends CoreComponent {

    //put your code here
    private $pm;
    
    private $key = "lENb2bPRk)c&k0ebY0nSxiq9iKgg8WYU";
    
    private $connection;

    private $detailsDAO;

    public function __construct(CoreUser $core_user) {

        parent::__construct();

        $this->user = $core_user;

        $dbinfo = DbConnectInfo::getDBConnectInfoObject();

        @$this->connection = new \mysqli($dbinfo->getServer(), $dbinfo->getUserName(), $dbinfo->getPassword(), $dbinfo->getDatabaseName(), $dbinfo->getPort());

        if ($this->connection->connect_errno) {
            $this->throwDBError($this->connection->connect_error, $this->connection->connect_errno);
        }

        $this->pm = new PermissionManager($this->connection);
        $this->detailsDAO = new CoreEventDetailsDAO($this->connection);
    }

    /**
     * getEventDetails()
     * 
     * Returns array of values that can be then passed to the templating engine for rendering
     * 
     * @param type $encrypted_record_id
     * @return Array
     */
    public function getEventDetails(\stdClass $params) {

        
        $encrypted_record_id = (isset($params->encrypted_event_id) ? $params->encrypted_event_id : null);
        /* @var $timestamp \DateTime */
        $timestamp = (isset($params->timestamp) ? $params->timestamp : null);
        
        $is_owner = false;
        $logged_in_user_id = $this->user->getUserID();

        $now_dt = new \DateTime();

        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        $record_id = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, base64_decode($encrypted_record_id), MCRYPT_MODE_ECB, $iv));
        
        
        /* @var $details CoreEventDetails */
        $details = $this->detailsDAO->getCoreEventDetails($record_id,new \DateTime($timestamp));
        
        if( ! $details instanceof CoreEventDetails)
        {
            $this->throwExceptionOnError("Event not found", 0, \ERROR_LOG_TYPE);
        }
        
        $start_dt = $details->getStart();
        $end_dt = $details->getEnd();
        
        
        if ($logged_in_user_id == $details->getUserId()) {
            $is_owner = true;
        }

        $user_roles = UserRoleManager::getUserRolesForService($this->user, $details->getServiceId(), $is_owner);
        $permissions_a = $this->pm->getPermissions($user_roles, $details->getServiceId());

        $ArrDetails = [];
        
        $ArrDetails['username'] = $details->getUsername();
        /*
         * Compare dates to decide on the format of the time
         */
        $start_d = $start_dt->format("m/d/y");
        $end_d = $end_dt->format("m/d/y");
        
        if (\strcmp($start_d, $end_d) == 0) {
            $ArrDetails['time'] = $start_dt->format("M j, Y g:ia") . " - " . $end_dt->format("g:ia");
        } else {
            $ArrDetails['time'] = $start_dt->format("M j, Y g:ia") . " - " . $end_dt->format("M j, Y g:ia");
        }

        $ArrDetails['activity'] = $details->getService() . ', ' . $details->getResource();
        /* @var $timestamp_dt \DateTime */
        $timestamp_dt = $details->getTimestamp();
        $ArrDetails['timestamp'] = $timestamp_dt->format('Y-m-d H:i:s');


        if ($this->pm->hasPermission($permissions_a, \DB_PERM_VIEW_DETAILS)) {

            $ArrDetails['record_id'] = $encrypted_record_id;

            /*
             * Show full name and username with DB_PERM_VIEW_DETAILS
             */

            $ArrDetails['email'] = $details->getEmail();
            $ArrDetails['pi'] = $details->getPiname();
            $ArrDetails['note'] = $details->getNote();
        }

        if ($this->pm->hasPermission($permissions_a, \DB_PERM_DELETE_EVENT)) {
            $ArrDetails['can_cancel'] = true;
        }

        if ($start_dt < $now_dt) {
            if (!$this->pm->hasPermission($permissions_a, \DB_PERM_EDIT_PAST_EVENT)) {
                unset($ArrDetails['can_cancel']);
            }
        }

        if ($this->pm->hasPermission($permissions_a, \DB_PERM_CHANGE_OWNER)) {
            $user_id_enc = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $details->getUserId(), MCRYPT_MODE_ECB, $iv));
            $ArrDetails['user_id'] = $user_id_enc;
            $ArrDetails['can_edit_user'] = true;
        }


        //If user can change note
        if ($this->pm->hasPermission($permissions_a, \DB_PERM_CHANGE_NOTE)) {
            if ($start_dt > $now_dt) {
                $ArrDetails['can_edit_note'] = true;
            } else {
                if ($this->pm->hasPermission($permissions_a, \DB_PERM_EDIT_PAST_EVENT)) {
                    $ArrDetails['can_edit_note'] = true;
                }
            }
        }

        return $ArrDetails;
    }

}
