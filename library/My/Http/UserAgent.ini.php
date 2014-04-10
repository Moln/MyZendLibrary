<?php
/*
| -------------------------------------------------------------------
| USER AGENT TYPES
| -------------------------------------------------------------------
| This file contains four arrays of user agent data.  It is used by the
| User Agent Class to help identify browser, platform, robot, and
| mobile device data.  The array keys are used to identify the device
| and the array values are used to set the actual name of the item.
|
*/

$platforms = array(
    'windows nt 6.1'          => 'Windows 7',
    'windows nt 6.0'          => 'Windows Vista',
    'windows nt 5.2'          => 'Windows Server 2003',
    'windows nt 5.1'          => 'Windows XP',
    'windows nt 5.01'         => 'Windows 2000 SP1',
    'windows nt 5.0'          => 'Windows 2000',
    'windows nt 4.0'          => 'Windows NT 4.0',
    'winnt'                   => 'Windows NT 4.0',
    'windows 98; win 9x 4.90' => 'Windows Me',
    'windows 98'              => 'Windows 98',
    'win98'                   => 'Windows 98',
    'windows 95'              => 'Windows 95',
    'win95'                   => 'Windows 95',
    'windows ce'              => 'Windows CE',
    'win'                     => 'Windows',
    'android'                 => 'Android',
    'iphone'                  => 'iPhone',
    'ipad'                    => 'iPad',
    'os x'                    => 'Mac OS X',
    'ppc mac'                 => 'Power PC Mac',
    'freebsd'                 => 'FreeBSD',
    'ppc'                     => 'Macintosh',
    'linux'                   => 'Linux',
    'debian'                  => 'Debian',
    'sunos'                   => 'Sun Solaris',
    'beos'                    => 'BeOS',
    'apachebench'             => 'ApacheBench',
    'aix'                     => 'AIX',
    'irix'                    => 'Irix',
    'osf'                     => 'DEC OSF',
    'hp-ux'                   => 'HP-UX',
    'netbsd'                  => 'NetBSD',
    'bsdi'                    => 'BSDi',
    'openbsd'                 => 'OpenBSD',
    'gnu'                     => 'GNU/Linux',
    'unix'                    => 'Unix OS'
);


// The order of this array should NOT be changed. Many browsers return
// multiple browser types so we want to identify the sub-type first.

$browsers = array(
    //Extras
    'Maxthon'           => '遨游(Maxthon)',
    'TencentTraveler'   => '腾讯TT',
    'TheWorld'          => '世界之窗(The World)',
    '360SE'             => '360浏览器',

    //Defaults
    'Flock'             => 'Flock',
    'Chrome'            => 'Chrome',
    'Opera'             => 'Opera',
    'MSIE'              => 'IE',
    'Internet Explorer' => 'IE',
    'Shiira'            => 'Shiira',
    'Firefox'           => 'Firefox',
    'Chimera'           => 'Chimera',
    'Phoenix'           => 'Phoenix',
    'Firebird'          => 'Firebird',
    'Camino'            => 'Camino',
    'Netscape'          => 'Netscape',
    'OmniWeb'           => 'OmniWeb',
    'Safari'            => 'Safari',
//    'Mozilla'           => 'Mozilla',
    'Konqueror'         => 'Konqueror',
    'icab'              => 'iCab',
    'Lynx'              => 'Lynx',
    'Links'             => 'Links',
    'hotjava'           => 'HotJava',
    'amaya'             => 'Amaya',
    'IBrowse'           => 'IBrowse',
    'Trident'           => 'IE',
);

$mobiles = array(
    // legacy array, old values commented out
    'mobileexplorer'       => 'Mobile Explorer',
    'palmsource'           => 'Palm',
    'palmscape'            => 'Palmscape',

    // Phones and Manufacturers
    'motorola'             => "Motorola",
    'nokia'                => "Nokia",
    'palm'                 => "Palm",
    'iphone'               => "Apple iPhone",
    'ipad'                 => "iPad",
    'ipod'                 => "Apple iPod Touch",
    'sony'                 => "Sony Ericsson",
    'ericsson'             => "Sony Ericsson",
    'blackberry'           => "BlackBerry",
    'cocoon'               => "O2 Cocoon",
    'blazer'               => "Treo",
    'lg'                   => "LG",
    'amoi'                 => "Amoi",
    'xda'                  => "XDA",
    'mda'                  => "MDA",
    'vario'                => "Vario",
    'htc'                  => "HTC",
    'samsung'              => "Samsung",
    'sharp'                => "Sharp",
    'sie-'                 => "Siemens",
    'alcatel'              => "Alcatel",
    'benq'                 => "BenQ",
    'ipaq'                 => "HP iPaq",
    'mot-'                 => "Motorola",
    'playstation portable' => "PlayStation Portable",
    'hiptop'               => "Danger Hiptop",
    'nec-'                 => "NEC",
    'panasonic'            => "Panasonic",
    'philips'              => "Philips",
    'sagem'                => "Sagem",
    'sanyo'                => "Sanyo",
    'spv'                  => "SPV",
    'zte'                  => "ZTE",
    'sendo'                => "Sendo",

    // Operating Systems
    'symbian'              => "Symbian",
    'SymbianOS'            => "SymbianOS",
    'elaine'               => "Palm",
    'series60'             => "Symbian S60",
    'windows ce'           => "Windows CE",

    // Browsers
    'obigo'                => "Obigo",
    'netfront'             => "Netfront Browser",
    'openwave'             => "Openwave Browser",
    'mobilexplorer'        => "Mobile Explorer",
    'operamini'            => "Opera Mini",
    'opera mini'           => "Opera Mini",

    // Other
    'digital paths'        => "Digital Paths",
    'avantgo'              => "AvantGo",
    'xiino'                => "Xiino",
    'novarra'              => "Novarra Transcoder",
    'vodafone'             => "Vodafone",
    'docomo'               => "NTT DoCoMo",
    'o2'                   => "O2",
    'mi'                   => 'XiaoMi',

    // Fallback
    'mobile'               => "Generic Mobile",
    'wireless'             => "Generic Mobile",
    'j2me'                 => "Generic Mobile",
    'midp'                 => "Generic Mobile",
    'cldc'                 => "Generic Mobile",
    'up.link'              => "Generic Mobile",
    'up.browser'           => "Generic Mobile",
    'smartphone'           => "Generic Mobile",
    'cellphone'            => "Generic Mobile"
);

// There are hundreds of bots but these are the most common.
$robots = array(
    'googlebot'   => 'Googlebot',
    'msnbot'      => 'MSNBot',
    'slurp'       => 'Inktomi Slurp',
    'yahoo'       => 'Yahoo',
    'askjeeves'   => 'AskJeeves',
    'fastcrawler' => 'FastCrawler',
    'infoseek'    => 'InfoSeek Robot 1.0',
    'lycos'       => 'Lycos',
    'baidu'       => 'Baidu'
);
