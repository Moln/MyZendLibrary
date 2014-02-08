<?php
/**
 *
 * @version: $id$
 */
namespace My\Crypt;

/**
 * PHP版3DES加解密
 * Class Crypt3Des
 * @package My\Crypt
 */
class Crypt3Des
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * 补全长度
     * @param $input
     *
     * @return string
     */
    private function pad($input)
    {
        $size = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $input .= str_repeat(chr(0), $size - strlen($input) % $size); //以chr(0)补全长度
        return $input;
    }

    /**
     * 加密
     * @param $input
     *
     * @return string
     */
    public function encrypt($input)
    {
        $key   = str_pad($this->key, 24, chr(0));
        $input = $this->pad($input);
        $td    = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        //使用MCRYPT_3DES算法,cbc模式
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);

        //初始处理
        $data = mcrypt_generic($td, $input);
        //加密
        mcrypt_generic_deinit($td);
        //结束
        mcrypt_module_close($td);
        return str_replace(["\n", "\r"], "", base64_encode($data));
    }

    /**
     * 解密
     * @param $encrypted
     *
     * @return string
     */
    public function decrypt($encrypted)
    {
        $key       = str_pad($this->key, 24, chr(0));
        $encrypted = base64_decode($encrypted);
        $td        = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        //使用MCRYPT_3DES算法,cbc模式
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        //初始处理
        $decrypted = mdecrypt_generic($td, $encrypted);
        //解密
        mcrypt_generic_deinit($td);
        //结束
        mcrypt_module_close($td);
        $decrypted = str_replace(chr(0), "", $decrypted);
        return $decrypted;
    }
}