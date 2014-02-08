<?php
namespace My\Token;

/**
 * 密保卡
 * Class Matrix
 * @package My\Token
 * @author Xiemaomao
 * @version $Id: Matrix.php 790 2013-03-15 08:56:56Z maomao $
 */
class Matrix extends TokenAbstract
{
    private $font;
    private $customCode = false;
    private $data;
    private $sn;
    private $imageDir = './images/';
    private $imageUrl = '/images/';
    private $suffix = '.png';

    protected $messages
        = [
            'bind'    => [
                'systemFailed' => '绑定失败,数据库连接异常',
                'expire'       => '密保卡过期, 请重新获取',
                'success'      => '绑定成功',
            ],
            'valid'   => [
                'failed'     => '密码验证失败',
                'unbind'     => '账号未绑定密保卡',
                'disaccord'  => '序列号与绑定的不一致',
                'success'    => '验证成功',
            ],
            'unbind'  => [
                'success'  => '解绑成功',
            ],
        ];
    const SUCCESS           = 1;
    const VALID_FAILED      = -1;
    const VALID_UNBIND      = -2;
    const VALID_DISACCORD   = -3;
    const BIND_ERROR_EXPIRE       = -10;
    const BIND_ERROR_SYSTEM       = -11;

    /**
     * 验证
     * @param string $account
     * @param string $password
     *
     * @return bool|void
     */
    public function auth($account, $password)
    {
        return $this->isValid(
            $account, $password, function ()
            {
                $this->setMessage($this->messages['valid']['success']);
                return true;
            }
        );
    }

    private $code = self::SUCCESS;

    /**
     * 获取验证代号
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * 绑定
     * @param string $account
     * @param string $password
     * @param null   $sn
     *
     * @return bool
     */
    public function bind($account, $password, $sn = null)
    {
        if (!$this->hasNew()) {
            $this->code = self::BIND_ERROR_EXPIRE;
            $this->setMessage($this->messages['bind']['expire']);
            return false;
        }

        $data = $this->getData();

        if ($this->isValidPassword($password, $data)) {
            $this->getTable()->bind($account, $this->getSn(), $this->getData());
            $this->setMessage($this->messages['bind']['success']);
            return true;
        }

        $this->code = self::VALID_FAILED;
        $this->setMessage($this->messages['valid']['failed']);
        return false;
    }

    /**
     * @var DbTable\Matrix
     */
    protected $table;

    /**
     * @return DbTable\Matrix
     */
    protected function getTable()
    {
        if (!$this->table) {
            $this->table = new DbTable\Matrix();
        }

        return $this->table;
    }

    /**
     * 验证密码
     * @param $password
     * @param $data
     *
     * @return bool
     */
    private function isValidPassword($password, $data)
    {
        if ($this->customCode) {
            if (strlen($password) != 12) {
                return false;
            }
            list($code, $password) = str_split($password, 6);
        }

        if (strlen($password) != 6) {
            return false;
        }

        $code   = isset($code) ? str_split(strtoupper($code), 2) : $this->getSession()->code;
        $result = '';
        foreach ($code as $pos) {
            $ascii = ord($pos{0});
            if ($ascii < 65 || $ascii > 70 || $pos{1} < 1 || $pos{1} > 6) {
                return false;
            }
            $result .= substr($data, ($pos{1} - 1) * 12 + ($ascii - 65) * 2, 2);
        }
        return $result == $password;
    }

    private function isValid($account, $password, $callback, $sn = null)
    {
        $row = $this->getTable()->getByAccount($account);

        if (!$row) {
            $this->code = self::VALID_UNBIND;
            $this->setMessage($this->messages['valid']['unbind']);
            return false;
        }

        if (!empty($sn) && $sn != $row->sn) {
            $this->code = self::VALID_DISACCORD;
            $this->setMessage($this->messages['valid']['disaccord']);
            return false;
        }

        if ($this->isValidPassword($password, $row->data)) {
            return $callback();
        }

        $this->code = self::VALID_FAILED;
        $this->setMessage($this->messages['valid']['failed']);
        return false;
    }

    /**
     * @var \Zend_Session_Namespace
     */
    private $session;

    /**
     * @return \Zend_Session_Namespace
     */
    private function getSession()
    {
        if (!$this->session) {
            $this->session = new \Zend_Session_Namespace(__CLASS__);
        }

        return $this->session;
    }

    public function hasNew()
    {
        return isset($this->getSession()->sn);
    }

    public function __construct()
    {
        $this->font = APPLICATION_PATH . '/../data/ttf/MSYH.TTF';
    }

    private function generateData()
    {
        $str = '';
        for ($i = 0; $i < 8; $i++) {
            $str .= mt_rand(100000000, 999999999);
        }

        return $str;
    }

    private function generateSerialNumber()
    {
        return '10' . str_pad(str_replace('.', '', microtime(true)), 14, '0');
    }

    /**
     * 创建新的密保卡
     * @return $this
     */
    public function createNew()
    {
        $this->getSession()->data = $this->generateData();
        $this->getSession()->sn   = $this->generateSerialNumber();
        return $this;
    }

    /**
     * 获取图片URL路径
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl . md5($this->getSn()) . $this->suffix;
    }

    private static $backgroundImage = null;

    /**
     * 设置图片背景图
     * @param $src
     */
    public static function setBackgroundImage($src)
    {
        self::$backgroundImage = $src;
    }

    /**
     * 生成图片
     * @param bool $returnStream
     *
     * @return null|string
     */
    public function generateImage($returnStream = false)
    {
        $data = array_chunk(str_split($this->getData(), 2), 6);
        $sn   = $this->getSn();

        $width  = 40;
        $height = 30;
        if (self::$backgroundImage) {
            $img = imagecreatefromjpeg(self::$backgroundImage); #(290, 250);
        } else {
            $img = imagecreatetruecolor(290, 250);
            //填空背景
            imagefilltoborder($img, 1, 1, 0x96C2F1, 0xFFFFFF);
        }

        //画表格数据
        foreach ($data as $i => $row) {
            $this->imageGrid(
                $img, 40, 60 + 30 * $i, $width, $height, [$row], 0x0, 11, 0x96C2F1,
                $i % 2 ? 0xFFFFFF : 0xEFF7FF, 1
            );
        }

        //Y 轴
        $this->imageGrid($img, 0, 62, $width, $height, [[1], [2], [3], [4], [5], [6]]);

        //X 轴
        $this->imageGrid($img, 40, 30, $width, $height, [['A', 'B', 'C', 'D', 'E', 'F']]);

        //标题
        $title = '序列号: ' . implode('-', str_split($sn, 4));
        imagettftext($img, 12, 0, 12, 20, 0, $this->font, $title);
        imageline($img, 0, 30, 290, 30, 0x96C2F1);

        if ($returnStream) {
            ob_start();
            imagepng($img);
            imagedestroy($img);
            $stream = ob_get_contents();
            ob_end_clean();
            return $stream;
        } else {
            imagepng($img);
            imagedestroy($img);
            return null;
        }
    }

    /**
     * 获取图片物理路径
     * @return string
     */
    public function getImagePath()
    {
        return $this->imageDir . '/' . md5($this->getSn()) . $this->suffix;
    }

    /**
     * 画图片表格
     * @param      $img
     * @param      $startX
     * @param      $startY
     * @param      $width
     * @param      $height
     * @param      $data
     * @param int  $fontColor
     * @param int  $fontSize
     * @param null $borderColor
     * @param null $bgColor
     * @param null $borderWidth
     */
    private function imageGrid(
        $img, $startX, $startY, $width, $height, $data, $fontColor = 0, $fontSize = 14,
        $borderColor = null, $bgColor = null, $borderWidth = null
    )
    {
        if ($borderColor && $bgColor && $borderWidth) {
            $call = function (
                $x1, $y1, $x2, $y2, $text
            ) use ($img, $borderColor, $bgColor, $borderWidth, $width, $height, $fontSize, $fontColor)
            {
                imagefilledrectangle(
                    $img, $x1, $y1, $x2 + $borderWidth, $y2 + $borderWidth, $borderColor
                );
                imagefilledrectangle(
                    $img, $x1 + $borderWidth, $y1 + $borderWidth, $x2, $y2, $bgColor
                );

                imagettftext(
                    $img, $fontSize, 0, $x1 + $width / 2 - (strlen($text) * $fontSize) / 3,
                    $y1 + $fontSize / 2 + $height / 2, $fontColor, $this->font, $text
                );
            };
        } else {
            $call = function (
                $x1, $y1, $x2, $y2, $text
            ) use ($img, $width, $height, $fontSize, $fontColor)
            {
                imagettftext(
                    $img, $fontSize, 0, $x1 + $width / 2 - (strlen($text) * $fontSize) / 3,
                    $y1 + $fontSize / 2 + $height / 2, $fontColor, $this->font, $text
                );
            };
        }

        $rows = count($data);
        for ($y = 0; $y < $rows; $y++) {
            $cols = count($data[$y]);
            for ($x = 0; $x < $cols; $x++) {
                $x1   = $startX + ($width * $x);
                $x2   = $startX + ($width * ($x + 1));
                $y1   = $startY + ($height * $y);
                $y2   = $startY + ($height * ($y + 1));
                $text = $data[$y][$x];
                $call($x1, $y1, $x2, $y2, $text);
            }
        }
    }

    /**
     * 获取密保卡数据
     * @return mixed
     */
    public function getData()
    {
        return $this->getSession()->data;
    }

    /**
     * 获取密保卡序号
     * @return mixed
     */
    public function getSn()
    {
        return $this->getSession()->sn;
    }

    /**
     * 创建密保卡随机位码
     * @return array
     */
    public function createRandomCode()
    {
        $x    = '123456';
        $y    = 'ABCDEF';
        $code = [];

        while (count($code) != 3) {
            $code[] = $y[mt_rand(0, 5)] . $x[mt_rand(0, 5)];
            $code   = array_values(array_unique($code));
        }

        $this->getSession()->code = $code;

        return $code;
    }

    /**
     * 密保卡解绑
     * @param $account
     * @param $password
     * @param $sn
     *
     * @return bool|void
     */
    public function unbind($account, $password, $sn)
    {
        return $this->isValid(
            $account, $password, function () use ($account)
            {
                $this->getTable()->remove($account);
                $this->setMessage($this->messages['unbind']['success']);
                return true;
            }, $sn
        );
    }

    /**
     * 强制删除账号密保
     * @param        $account
     * @param string $sn
     *
     * @return bool
     */
    public function delete($account, $sn = '')
    {
        $this->getTable()->remove($account);
        return true;
    }

    public function replace($account, $sn, $password, $newSn, $newPassword)
    {
        //
    }

    /**
     * 查询账号绑定数据
     * @param $account
     * @return \Zend_Db_Table_Row_Abstract
     */
    public function queryByAccount($account)
    {
        return $this->getTable()->getByAccount($account);
    }

    /**
     * 清除创建
     */
    public function clearCreate()
    {
        $this->getSession()->unsetAll();
    }

    /**
     * 设置密保卡字体
     * @param $font
     *
     * @return $this
     */
    public function setFont($font)
    {
        $this->font = $font;
        return $this;
    }

    /**
     * 设置用自定义坐标密码验证
     * @param bool $customCode
     *
     * @return Matrix
     */
    public function setCustomCode($customCode)
    {
        $this->customCode = (bool)$customCode;
        return $this;
    }
}