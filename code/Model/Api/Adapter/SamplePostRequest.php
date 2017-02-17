<?php

class Capita_TI_Model_Api_Adapter_SamplePostRequest extends Zend_Http_Client_Adapter_Test
{

    public function __construct()
    {
        $this->setResponse("HTTP/1.1 201 CREATED\r\n".
            "Content-Type: application/json\r\n\r\n".
            '{
    "Documents": [
        {
            "DocumentId": "746155792-85270",
            "DocumentName": "file-one-name.txt",
            "IsoCode": "en-US",
            "LanguageName": "English (USA)"
        },
        {
            "DocumentId": "746155792-85271",
            "DocumentName": "file-two-name.txt",
            "IsoCode": "en-US",
            "LanguageName": "English (USA)"
        }
    ],
    "RequestId": "1250936094-13321",
    "RequestNo": "CTI-160302-1",
    "RequestStatus": "onHold"
}');
    }
}
