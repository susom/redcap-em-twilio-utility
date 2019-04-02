<?php
namespace Stanford\TwilioUtility;

use REDCap;

/**
 *
 * This is called from Twilio Webhook set up from the calling number
 *
 * Set this up under
 * Messaging: WEbhook
 * https://redcap.stanford.edu/api/?type=module&prefix=twilio_texter&page=HandleTextReceipt&pid=8384&NOAUTH
 */

/** @var \Stanford\TwilioUtility\TwilioUtility $module */

$module->emDebug('--- Incoming Text to TwilioUtility ---');

$webhook = $module->getUrl("src/HandleTextReceipt.php", true, true);
$module->emDebug($webhook);
//$module->emDebug($_POST);

//handle-incoming-text
$handle_incoming_text = $module->getProjectSetting('handle-incoming-text');
$to =$module->getProjectSetting('email-to');

//if handle_incoming_text and $to  is not set then abort
if ((!isset($handle_incoming_text )) or (!isset($to))) {
    $module->emDebug("incoming text not to be handled or To field not set");
    exit();
}

$subject = $module->getProjectSetting('forwarding-email-subject');
$phone_field = $module->getProjectSetting('phone-lookup-field');
$phone_field_event = $module->getProjectSetting('phone-lookup-field-event');

// Get the body of the message
$body = isset($_POST['Body']) ? $_POST['Body'] : '';
if (empty($body)) {
    $module->emDebug(json_encode($_POST), "Received incoming text without a body: ". $from_10 . " Exiting.");
    //exit();
}

// Get the phone number to search REDCap
$from = $_POST['From'];
if (!isset($from)) {
    $module->emLog("Twilio Webhook received but there is no phone number reported");

    $from_10 = null;
    $rec_id = null;
    //exit;
} else {
    $from_10 = substr($from, -10);

    //use the phone number to look for the record id
    $rec_id = $module->findRecordByPhone($module->formatToREDCapNumber($from_10), $phone_field, $phone_field_event);
    $module->emDebug("Rec ID is $rec_id");
}

// email coordinator to let them know
$to = $module->getProjectSetting('email-to');
$from = "no-reply@stanford.edu";
$subject = $module->getProjectSetting('forwarding-email-subject');

//$module->emDebug("TO: ". $to . ' and '. $subject);

//Looks like there is no record affiliated with that phone number
if (!$rec_id) {
    $info =  "We have received a text from a phone number that is not in the project: ";
} else {
    $info = "We have received a text: ";
}

$module->emLog("Text from phone " . $from_10 . " with entry: " . $body. " forwarding to ". $module->getProjectSetting('email-to'));

$msg = $info.
    "<br><br>PROJECT ID: " . $pid .
    "<br>RECORD_ID: " . $rec_id .
    "<br>PHONE NUMBER: " . $from_10 .
    "<br>BODY OF TEXT: ".$body ;

$module->sendEmail($to, $from, $subject, $msg);

//log this event
$module->logEvent($info . " PHONE: " . $from_10 . "  / TEXT: ". $body, $rec_id);

