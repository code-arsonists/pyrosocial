<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Oembed {
	
    public $type = 'json';
	public $_oEmbedProvider;
	public $_oEmbedParams = array();
	
	function __construct($params = array())
	{
		$this->ci = & get_instance();
		$this->ci->load->library('curl');

		$this->_initialize($params);

        log_message('debug', "oEmbed Lib Initialized");
	}
	

    function call($provider, $url)
    {
		$url = rawurlencode($url);
		$provider = strtolower($provider);
		
		if ( array_key_exists($provider ,$this->_oEmbedProvider) )
		{
			$extra = '&'.http_build_query($this->_oEmbedParams);			
			$request_url = str_replace( array('{url}', '{format}'), array($url, $this->_oEmbedParams['format']), $this->_oEmbedProvider[$provider]) . $extra;

			return $this->_fetch($request_url);
		}
		return show_error('Oembed - The '.$provider.' API is unsupported!');
	}

	function _fetch($url)
    {

		$return = $this->ci->curl->simple_get($url);
		if ($return)
        {
            return $this->_parse_returned($return);
		}
		return FALSE;
	}

	function _parse_returned($data)
    {
		if(empty($data)) return FALSE;

		switch ($this->type)
        {
			case 'xml':
                return $this->_build_xml($data);
				break;
			case 'json':
				$resp = json_decode($data);
				
				$resizeWidth = $resp->thumbnail_width;
				$resizeHeight = $resp->thumbnail_height;

				$aspect = $resizeWidth / $resizeHeight;

				if ($resizeWidth > $this->getParam('maxwidth'))
				{
					$resizeWidth = $this->getParam('maxwidth');
					$resizeHeight = $resizeWidth / $aspect;
				}
				if ($resizeHeight > $this->getParam('maxheight'))
				{
					$aspect = $resizeWidth / $resizeHeight;
					$resizeHeight = $this->getParam('maxheight');
					$resizeWidth = $resizeHeight * $aspect;
				}
				$resp->thumbnail_width = $resizeWidth;
				$resp->thumbnail_height = $resizeHeight;
				return $resp;
			break;
		}
	}

	function _build_xml($data)
    {
        if ($this->type == 'xml')
        {
            $data = simplexml_load_string($data);

            $keys = array();

            foreach($data as $key => $value)
            {
                if ($key !== '@attributes')
                {
                    $keys[] = $key;
                }
            }
            if (count($keys) == 1)
            {
                return $data->$keys[0];
            }
        }
        return $data;
	}
	
    function _initialize($params = array())
    {
		$this->_oEmbedParams = array(
			'format'=>'json',
			'maxwidth'=>300,
			'maxheight'=>250
		);
		
        if (count($params) > 0)
        {
            foreach ($params as $key => $val)
            {
				$this->_oEmbedParams[$key] = strtolower($val);
            }
        }
/*
			'#http://(www\.)?youtube.com/watch.*#i'         => array( 'http://www.youtube.com/oembed',            true  ),
			'http://youtu.be/*'                             => array( 'http://www.youtube.com/oembed',            false ),
			'http://blip.tv/*'                              => array( 'http://blip.tv/oembed/',                   false ),
			'#http://(www\.)?vimeo\.com/.*#i'               => array( 'http://www.vimeo.com/api/oembed.{format}', true  ),
			'#http://(www\.)?dailymotion\.com/.*#i'         => array( 'http://www.dailymotion.com/api/oembed',    true  ),
			'#http://(www\.)?flickr\.com/.*#i'              => array( 'http://www.flickr.com/services/oembed/',   true  ),
			'#http://(.+)?smugmug\.com/.*#i'                => array( 'http://api.smugmug.com/services/oembed/',  true  ),
			'#http://(www\.)?hulu\.com/watch/.*#i'          => array( 'http://www.hulu.com/api/oembed.{format}',  true  ),
			'#http://(www\.)?viddler\.com/.*#i'             => array( 'http://lab.viddler.com/services/oembed/',  true  ),
			'http://qik.com/*'                              => array( 'http://qik.com/api/oembed.{format}',       false ),
			'http://revision3.com/*'                        => array( 'http://revision3.com/api/oembed/',         false ),
			'http://i*.photobucket.com/albums/*'            => array( 'http://photobucket.com/oembed',            false ),
			'http://gi*.photobucket.com/groups/*'           => array( 'http://photobucket.com/oembed',            false ),
			'#http://(www\.)?scribd\.com/.*#i'              => array( 'http://www.scribd.com/services/oembed',    true  ),
			'http://wordpress.tv/*'                         => array( 'http://wordpress.tv/oembed/',              false ),
			'#http://(answers|surveys)\.polldaddy.com/.*#i' => array( 'http://polldaddy.com/oembed/',             true  ),
			'#http://(www\.)?funnyordie\.com/videos/.*#i'   => array( 'http://www.funnyordie.com/oembed',         true  ),

*/
		$this->_oEmbedProvider = array(
			'youtube' => 'http://www.youtube.com/oembed?url={url}&format={format}',
			'viddler'=> 'http://lab.viddler.com/services/oembed/?url={url}&format={format}',
			'qik' => 'http://qik.com/api/oembed.{format}?url={url}',
			'revision3' => 'http://revision3.com/api/oembed/?url={url}&format={format}',
			'hulu'=> 'http://www.hulu.com/api/oembed.{format}?url={url}',
			'vimeo' => 'http://www.vimeo.com/api/oembed.{format}?url={url}',
			'oohembed' => 'http://oohembed.com/oohembed/?url={url}&format={format}'
		);
    }
	
	/*
		http://www.youtube.com/oembed?url={url}&format={format}
		http://www.hulu.com/api/oembed.{format}?url={url}
	*/
	function addProvider($name, $url)
	{
		$this->_oEmbedProvider[$name] = $url;
	}
	
	public function getParam($name)
	{
		if (array_key_exists($name, $this->_oEmbedParams))
		{
			return $this->_oEmbedParams[$name];
		}
		return null;
	}

	public function issetParam($name) {
		return isset($this->_oEmbedParams[$name]);
	}
	
}
