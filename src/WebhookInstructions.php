<?php

/** @var \Stanford\RepeatingSurveyPortal\RepeatingSurveyPortal $module */

$url = $module->getUrl('src/HandleTextReceipt.php', true, true);
//echo "<br><br>This is the HandleTextReceipt Link: <br>".$url . "&s=0";



?>

<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <title>Instructions Page</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="<?php echo $module->getUrl("pages/sRAP.css") ?>" />

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</head>

<body>

<!-- Top nav bar -->
<div class="container">

<hr>
<h4>Instructions on setting up the Twilio Webhook</h4>
<br><br>
<h5>SETTING UP A SCRIPT TO HANDLE INCOMING TEXTS TO YOUR TWILIO NUMBER: </h5>
    <p>
    You can add a webhook to forward incoming texts to the emails entered in the EM configuration.

    <ol>
        <li>Navigate to Phone Numbers / Manager Numbers / Active Numbers /</li>
        <li>Scroll down to Messaging and set these values</li>
        <ol type="a">
            <li>CONFIGURE WITH : Select 'Webhooks, TwiML Bins, Function, Studo or Proxy' from dropdown</li>
            <li>A MESSAGE COMES IN : Select 'Webhook' from dropdown and enter your url in the textbox</li>

        </ol>
    </ol>
    </p>

<h7>Enter this link in the webhooks field: <br> <?php echo $url; ?></h7>

<hr>
</div>

</body>
