<?php
require 'vendor/autoload.php';

use MaxMind\Db\Reader;
class GeoIP {
	
	protected $_config = [
		'path'	=>	[
			'db'	=>	'db/GeoLite2-City.mmdb',
			'cache'	=>	'cache/'
		],
	];
	
	protected $ip;
	
	protected $ip_info;
	
	protected $cached = FALSE;

	protected $_reader;
	
	protected $_query;
	
	protected $_cache_lifetime = 60*30;
	
	public function __construct() {
		
		$this->_query = array_map('trim', $_GET);
		
		$this->_reader = new Reader($this->_config['path']['db']);
		
		$this->ip = $this->validate_ip() ? $this->_query['ip'] : NULL;
		
		$this->get_ip_info();
	}
	
	public function get_ip_info():void {
		if ( ! $this->ip) {
			$this->ip_info = NULL;
			return;
		}
		if ($this->get_cache($this->ip)) {
			return;
		}
		if ( ! $ip_data = $this->_reader->get($this->ip)) {
			$this->ip_info = NULL;
			return;
		}
		$this->ip_info = [
			'lon'		=>	empty($ip_data['location']) ? NULL : $ip_data['location']['longitude'],
			'lat'		=>	empty($ip_data['location']) ? NULL : $ip_data['location']['latitude'],
			'country'	=>	empty($ip_data['country']) ? NULL : $ip_data['country']['names']['en'],
			'city'		=>	empty($ip_data['city']) ? NULL : $ip_data['city']['names']['en'],
		];
		return;
	}
	
	public function validate_ip():bool {
		if (empty($this->_query['ip'])) {
			return FALSE;
		}
		return preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $this->_query['ip']);
	}
	
	public function get_cache() {
		if ($this->ip AND file_exists($this->_config['path']['cache'].$this->ip)) {
			$this->cached = TRUE;
			$data = json_decode(file_get_contents($this->_config['path']['cache'].$this->ip),TRUE);
			if ( ! empty($data['timestamp']) AND $data['timestamp'] < time() - $this->_cache_lifetime)  {
				unlink($this->_config['path']['cache'].$this->ip);
				return FALSE;
			}
			unset($data['timestamp']);
			$this->ip_info = $data;
			return TRUE;
		}
		return FALSE;
	}
	
	public function write_cache():void {
		if (! $this->cached AND $this->ip) {
			file_put_contents(
				$this->_config['path']['cache'].$this->ip,
				json_encode(array_merge(
					$this->ip_info,
					['timestamp'=>time()]
				))
			);
		}
	}
	
	public function __toString() {
		if (empty($this->ip_info)) {
			header("HTTP/1.0 404 Not Found");
			header("Content-Type: text/plain");
			return "";
		}
		header("HTTP/1.1 200 OK");
		header("Content-Type: application/json");
		return json_encode($this->ip_info); 
	}
	
	public function __destruct() {
		$this->write_cache();
	}
}

echo new GeoIP;