<?php
namespace My\Payment\Service;

/**
 * UntxCrypt3Des.php
 * @author Xiemaomao
 * @DateTime 12-11-19 下午2:52
 * @version $Id: UntxCrypt3Des.php 790 2013-03-15 08:56:56Z maomao $
 */
class UntxCrypt3Des
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function encrypt($input)
    {
        $size  = mcrypt_get_block_size(MCRYPT_3DES, 'ecb');
        $input = $this->pkcs5Pad($input, $size);
        $key   = str_pad($this->key, 24, '0');
        $td    = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
        $iv    = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        //$data = base64_encode($this->PaddingPKCS7($data));
        //$data = base64_encode($data);
        return $data;
    }

    public function decrypt($encrypted)
    {
        // $encrypted = base64_decode($encrypted);
        $key = str_pad($this->key, 24, '0');
        $td  = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
        $iv  = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks  = mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        $decrypted = mdecrypt_generic($td, $encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $y = $this->pkcs5Unpad($decrypted);
        return $y;
    }

    protected function pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    protected function pkcs5Unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
}
