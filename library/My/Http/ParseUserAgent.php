<?php

namespace My\Http;


/**
 * Class ParseUserAgent
 * @package My\Http
 * @author Xiemaomao
 * @version $Id$
 */
class ParseUserAgent 
{

    private $agent		= null;

    private $isBrowser	= false;
    private $isRobot	= false;
    private $isMobile	= false;

    private $platforms	= array();
    private $browsers	= array();
    private $mobiles	= array();
    private $robots		= array();

    private $platform	= '';
    private $browser	= '';
    private $version	= '';
    private $mobile		= '';
    private $robot		= '';

    /**
     * Constructor
     *
     * Sets the User Agent && runs the compilation routine
     *
     * @access    public
     * @param null $ua
     * @return \My\Http\ParseUserAgent
     */
    public function __construct($ua = null)
    {
        $ua = $ua ? : $_SERVER['HTTP_USER_AGENT'];
        $this->agent = $ua;
        if ($this->loadAgentFile()) {
            $this->parseData();
        }
    }

    /**
     * Compile the User Agent Data
     *
     * @access	private
     * @return	bool
     */
    private function loadAgentFile()
    {
        include 'UserAgent.ini.php';

        $return = false;

        if (isset($platforms)) {
            $this->platforms = $platforms;
            unset($platforms);
            $return = true;
        }

        if (isset($browsers)) {
            $this->browsers = $browsers;
            unset($browsers);
            $return = true;
        }

        if (isset($mobiles)) {
            $this->mobiles = $mobiles;
            unset($mobiles);
            $return = true;
        }

        if (isset($robots)) {
            $this->robots = $robots;
            unset($robots);
            $return = true;
        }

        return $return;
    }

    // --------------------------------------------------------------------

    /**
     * Compile the User Agent Data
     *
     * @access	private
     * @return	bool
     */
    private function parseData()
    {
        $this->setPlatform();

        foreach (array('setRobot', 'setBrowser', 'setMobile') as $function) {
            if ($this->$function() === true) {
                break;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set the Platform
     *
     * @access	private
     * @return	mixed
     */
    private function setPlatform()
    {
        if (is_array($this->platforms) && count($this->platforms) > 0)
        {
            foreach ($this->platforms as $key => $val)
            {
                if (preg_match("|".preg_quote($key)."|i", $this->agent))
                {
                    $this->platform = $val;
                    return true;
                }
            }
        }
        $this->platform = 'Unknown';
        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Set the Browser
     *
     * @access	private
     * @return	bool
     */
    private function setBrowser()
    {
        if (is_array($this->browsers) && count($this->browsers) > 0) {
            foreach ($this->browsers as $key => $val) {
                if (preg_match("|".preg_quote($key).".*?([0-9\\.]+)|i", $this->agent, $match)) {
                    $this->isBrowser = true;
                    $this->version = $match[1];
                    $this->browser = $val;
                    $this->setMobile();

                    if (preg_match("| Version/([0-9\\.]+)|i", $this->agent, $match)) {
                        $this->version = $match[1];
                    }
                    if (preg_match("| rv:([0-9\\.]+)|i", $this->agent, $match)) {
                        $this->version = $match[1];
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set the Robot
     *
     * @access	private
     * @return	bool
     */
    private function setRobot()
    {
        if (is_array($this->robots) && count($this->robots) > 0)
        {
            foreach ($this->robots as $key => $val)
            {
                if (preg_match("|".preg_quote($key)."|i", $this->agent))
                {
                    $this->isRobot = true;
                    $this->robot = $val;
                    return true;
                }
            }
        }
        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Set the Mobile Device
     *
     * @access	private
     * @return	bool
     */
    private function setMobile()
    {
        if (is_array($this->mobiles) && count($this->mobiles) > 0)
        {
            foreach ($this->mobiles as $key => $val)
            {
                if (false !== (strpos(strtolower($this->agent), $key)))
                {
                    $this->isMobile = true;
                    $this->mobile = $val;
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Is Browser
     *
     * @access    public
     * @param string $key
     * @return    bool
     */
    public function isBrowser($key = null)
    {
        if (!$this->isBrowser) {
            return false;
        }

        // No need to be specific, it's a browser
        if ($key === null) {
            return true;
        }

        // Check for a specific browser
        return array_key_exists($key, $this->browsers) && $this->browser === $this->browsers[$key];
    }

    // --------------------------------------------------------------------

    /**
     * Is Robot
     *
     * @access    public
     * @param null $key
     * @return    bool
     */
    public function isRobot($key = null)
    {
        if (!$this->isRobot)
        {
            return false;
        }

        // No need to be specific, it's a robot
        if ($key === null)
        {
            return true;
        }

        // Check for a specific robot
        return array_key_exists($key, $this->robots) && $this->robot === $this->robots[$key];
    }

    /**
     * Is Mobile
     *
     * @access    public
     * @param null $key
     * @return    bool
     */
    public function isMobile($key = null)
    {
        if (!$this->isMobile)
        {
            return false;
        }

        // No need to be specific, it's a mobile
        if ($key === null)
        {
            return true;
        }

        // Check for a specific robot
        return array_key_exists($key, $this->mobiles) && $this->mobile === $this->mobiles[$key];
    }


    // --------------------------------------------------------------------

    /**
     * Agent String
     *
     * @access	public
     * @return	string
     */
    public function getAgent()
    {
        return $this->agent;
    }

    // --------------------------------------------------------------------

    /**
     * Get Platform
     *
     * @access	public
     * @return	string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    // --------------------------------------------------------------------

    /**
     * Get Browser Name
     *
     * @access	public
     * @return	string
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    // --------------------------------------------------------------------

    /**
     * Get the Browser Version
     *
     * @access	public
     * @return	string
     */
    public function getVersion()
    {
        return $this->version;
    }

    // --------------------------------------------------------------------

    /**
     * Get The Robot Name
     *
     * @access	public
     * @return	string
     */
    public function getRobot()
    {
        return $this->robot;
    }
    // --------------------------------------------------------------------

    /**
     * Get the Mobile Device
     *
     * @access	public
     * @return	string
     */
    public function getMobile()
    {
        return $this->mobile;
    }
}