# Twilio Utility

## Usage




### With External Modules

It is useful to wrap this twilio function around an external module's own primary class.  The following code should be added to every new external module to add twilio texting capability. 
```php
class myEM extends \ExternalModules\AbstractExternalModule {

    /**
     *
     * TwilioUtility integration
     *
     */
    function emText($number, $text) {
        global $module;

        $emTexter = ExternalModules\ExternalModules::getModuleInstance('twilio_utility');
        $text_status = $emTexter->emSendSms($number, $text);
        return $text_status;
    }

    
    
}
```
