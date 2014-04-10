<?php
namespace My\Captcha;
use Zend_Captcha_Image;

/**
 * 验证码
 *
 * @author maomao
 * @version $Id: Image.php 1337 2014-03-25 23:41:18Z maomao $
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
            'font'      => APPLICATION_PATH . '/../data/ttf/font' . mt_rand(1, 7) . '.ttf',
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
        array_splice(Zend_Captcha_Image::$CN, array_search('u', Zend_Captcha_Image::$CN), 1);
        array_splice(Zend_Captcha_Image::$VN, array_search('u', Zend_Captcha_Image::$VN), 1);
        array_splice(Zend_Captcha_Image::$VN, array_search('o', Zend_Captcha_Image::$VN), 1);
//         $captcha->setMessages(array(
//             Zend_Captcha_Image::BAD_CAPTCHA => '验证码不正确',
//             Zend_Captcha_Image::MISSING_VALUE => '验证码不能为空',
//         ));
        return $captcha;
    }
}