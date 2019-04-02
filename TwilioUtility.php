<?php
namespace Stanford\TwilioUtility;

use Services_Twilio;
use Exception;
use ExternalModules\ExternalModules;
use Message;
use REDCap;

class TwilioUtility extends \ExternalModules\AbstractExternalModule
{

    public $twilio_account_sid;
    public $twilio_auth_token;
    public $twilio_from_number;

    public $deleteSmsFromLog;        // Set to true to delete the SMS log (warning - this can cause things to slow down as it waits up to 30 sec for the message to be delivered)

    public $client;                    // This is the twilio client!

/**
    function __construct()
    {
        parent::__construct();

        // Initialize the twilio library if needed
        if (!class_exists("Services_Twilio")) self::init();

        $this->twilio_account_sid = $this->getProjectSetting("twilio-sid");
        $this->twilio_auth_token = $this->getProjectSetting("twilio-token");
        $this->twilio_from_number = self::formatNumber($this->getProjectSetting("twilio-number"));
        $this->deleteSmsFromLog = $this->getProjectSetting("delete-sms-from-log");

        $this->client = new Services_Twilio($this->twilio_account_sid, $this->twilio_auth_token);

    }
*/
    /**
     * Initialize Twilio classes and settings (using REDCap ones since they also use the proxy for outgoing communication)
     */
    public static function init()
    {
        global $rc_autoload_function;
        // Call Twilio classes
        require_once APP_PATH_DOCROOT . "/Libraries/Twilio/Services/Twilio.php";
        // Reset the class autoload function because Twilio's classes changed it
        spl_autoload_register($rc_autoload_function);
    }


    public function setup() {
     //if (!class_exists("Services_Twilio")) self::init();

        $this->twilio_account_sid = $this->getProjectSetting("twilio-sid");
        $this->twilio_auth_token = $this->getProjectSetting("twilio-token");
        $this->twilio_from_number = self::formatNumber($this->getProjectSetting("twilio-number"));
        $this->deleteSmsFromLog = $this->getProjectSetting("delete-sms-from-log");

        $this->client = new Services_Twilio($this->twilio_account_sid, $this->twilio_auth_token);

    }

    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    function emSendSms($destination_number, $text) {
               //if not yet set, initialize twilio settings
        if (is_null($this->client)) {

            // Initialize the twilio library if needed
            if (!class_exists("Services_Twilio")) self::init();

            $this->setup();
        }

        try {
            $sms = $this->client->account->messages->sendMessage(
                $this->twilio_from_number,
                self::formatNumber($destination_number),
                $text
            );

            // Wait till the SMS sends completely and then remove it from the Twilio logs
            if ($this->deleteSmsFromLog) {
                sleep(1);
                $result = $this->deleteLogForSMS($sms->sid);
                print "<pre>RESULT:" . (int)$result . "</pre>";
            }

            // Successful, so return true
            return true;
        } catch (Exception $e) {
            // On failure, return error message
            return $e->getMessage();
        }
    }



      /**
     * TODO: NOT HANDLING / NOT EXPOSED IN CONFIG
     * Delete the Twilio back-end and front-end log of a given SMS (will try every second for up to 30 seconds)
     * @param $sid
     * @return bool
     */
    public function deleteLogForSMS($sid)
    {
        // Delete the log of this SMS (try every second for up to 30 seconds)
        for ($i = 0; $i < 30; $i++) {
            // Pause for 1 second to allow SMS to get delivered to carrier
            if ($i > 0) sleep(1);
            // Has it been delivered yet? If not, wait another second.
            $log = $this->client->account->sms_messages->get($sid);

            print "<pre>Log $i: " . print_r($log, true) . "</pre>";
            if ($log->status != 'delivered') continue;
            // Yes, it was delivered, so delete the log of it being sent.
            $this->client->account->messages->delete($sid);
            return true;
        }
        // Failed
        return false;
    }


    /**
     * Convert phone nubmer to E.164 format before handing off to Twilio
     * @param $phoneNumber
     * @return mixed|string
     */
    public static function formatNumber($phoneNumber)
    {
        // If number contains an extension (denoted by a comma between the number and extension), then separate here and add later
        $phoneExtension = "";
        if (strpos($phoneNumber, ",") !== false) {
            list ($phoneNumber, $phoneExtension) = explode(",", $phoneNumber, 2);
        }
        // Remove all non-numerals
        $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumber);
        // Prepend number with + for international use cases
        $phoneNumber = (isPhoneUS($phoneNumber) ? "+1" : "+") . $phoneNumber;
        // If has an extension, re-add it
        if ($phoneExtension != "") $phoneNumber .= ",$phoneExtension";
        // Return formatted number
        return $phoneNumber;
    }

    /**
     * The filter in the REDCap::getData expects the phone number to be in
     * this format (###) ###-####
     *
     * @param $number
     * @return
     */
    public static function formatToREDCapNumber($number)
    {
        $formatted = preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $number);
        return trim($formatted);

    }


    public function findRecordByPhone($phone, $phone_field, $phone_field_event) {

        $this->emDebug("Locate record for this phone: ".$phone);
        $get_fields = array(
            REDCap::getRecordIdField(),
            $phone_field
        );
        $event_name = REDCap::getEventNames(true, false, $phone_field_event);
        $filter = "[" . $event_name . "][" .$phone_field . "] = '$phone'";


        $records = REDCap::getData('array', null, $get_fields, null, null, false, false, false, $filter);
        //$this->emDebug($filter, $records, $project_id, $pid, $filter, $event_name);

        // return record_id or false
        reset($records);
        $first_key = key($records);
        return ($first_key);
    }

    function sendEmail($to, $from, $subject, $msg)
    {

        // Prepare message
        $email = new Message();
        $email->setTo($to);
        $email->setFrom($from);
        $email->setSubject($subject);
        $email->setBody($msg);

        //logIt("about to send " . print_r($email,true), "DEBUG");

        // Send Email
        if (!$email->send()) {
            $this->emLog('Error sending mail: ' . $email->getSendError() . ' with ' . json_encode($email));
            return false;
        }

        return true;
    }

    function logEvent($msg, $record) {
        $action = "Twilio Utility: Text Message Received";

        REDCap::logEvent(
            $action,  //action
            $msg, //changes
            NULL, //sql optional
            $record //record optional
        );
    }
    /*******************************************************************************************************************/
    /* EXTERNAL MODULES METHODS                                                                                                    */
    /***************************************************************************************************************** */


    function emLog()
    {
        global $module;
        $emLogger = ExternalModules::getModuleInstance('em_logger');
        $emLogger->emLog($module->PREFIX, func_get_args(), "INFO");
    }

    function emDebug()
    {
        // Check if debug enabled
        if ($this->getSystemSetting('enable-system-debug-logging') || ( !empty($_GET['pid']) && $this->getProjectSetting('enable-project-debug-logging'))) {
            $emLogger = ExternalModules::getModuleInstance('em_logger');
            $emLogger->emLog($this->PREFIX, func_get_args(), "DEBUG");
        }
    }

    function emError()
    {
        $emLogger = ExternalModules::getModuleInstance('em_logger');
        $emLogger->emLog($this->PREFIX, func_get_args(), "ERROR");
    }

} // class