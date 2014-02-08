<?php
namespace My\Captcha;
use Zend_Session_Namespace, Zend_Captcha_Image;

/**
 * 验证码
 *
 * @author maomao
 * @version $Id: Image.php 1275 2014-01-23 23:10:26Z maomao $
 */
class Image
{
    private static $sessionName = 'My_Captcha';

    /**
     * @return Zend_Captcha_Image Image
     */
    public static function create()
    {
//        $session = new Zend_Session_Namespace(self::$sessionName);
        $setting = array(
            'imgDir'    => './cache/captcha/',
            'imgUrl'    => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
                . '/cache/captcha/',
            'font'      => APPLICATION_PATH . '/../data/ttf/font' . mt_rand(1, 8) . '.ttf',
            'fontsize'  => 18,
//            'session'   => $session,
            'width'     => 80,
            'height'    => 50,
            'wordlen'   => 4,
            'dotNoiseLevel' => 3,
            'lineNoiseLevel' => 3,
//            'useNumbers' => false,
//            'expiration' => 600
        );
        $captcha = new Zend_Captcha_Image($setting);
//         $captcha->setMessages(array(
//             Zend_Captcha_Image::BAD_CAPTCHA => '验证码不正确',
//             Zend_Captcha_Image::MISSING_VALUE => '验证码不能为空',
//         ));
//        var_dump($captcha->getWord(), 1);
        return $captcha;
    }
}