<?php
return array(
    Zend_Captcha_Image::BAD_CAPTCHA => '验证码不正确!',
    Zend_Captcha_Image::MISSING_VALUE => '请输入验证码',
    Zend_Validate_StringLength::TOO_SHORT => '长度至少%min%位',
    Zend_Validate_StringLength::TOO_LONG  => '长度最多%max%位',

    Zend_Validate_NotEmpty::IS_EMPTY => '此项必填，不能为空',

    Zend_Validate_EmailAddress::INVALID            => "Invalid type given. String expected",
    Zend_Validate_EmailAddress::INVALID_FORMAT     => "邮箱格式不正确",
    Zend_Validate_EmailAddress::INVALID_HOSTNAME   => "'%hostname%' 无效的域名",
    Zend_Validate_EmailAddress::INVALID_MX_RECORD  => "'%hostname%' does not appear to have a valid MX record for the email address '%value%'",
    Zend_Validate_EmailAddress::INVALID_SEGMENT    => "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network",
    Zend_Validate_EmailAddress::DOT_ATOM           => "'%localPart%' can not be matched against dot-atom format",
    Zend_Validate_EmailAddress::QUOTED_STRING      => "'%localPart%' can not be matched against quoted-string format",
    Zend_Validate_EmailAddress::INVALID_LOCAL_PART => "'%localPart%' is no valid local part for email address '%value%'",
    Zend_Validate_EmailAddress::LENGTH_EXCEEDED    => "'%value%' exceeds the allowed length",
);