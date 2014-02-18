<?php

namespace Bestnetwork\Telnet;

use Radical\Utility\System\Execute;
class TelnetClient {
	private $proc;
	private $timeout;
	private $pager;
	
	function __construct($host, $port, $timeout, $pager = null){
		$cmd = 'telnet '.escapeshellarg($host);
		$execute = new \Radical\Utility\System\Execute($cmd);
		$this->proc = $execute->run();
		$this->timeout = $timeout;
		$this->pager = $pager;
	}
	
	function read(){
		return $this->proc->read();
	}
	
	function write($str){
		$this->proc->write($str);
	}
	
	function expect_str($str, $timeout = null){
		if($timeout === null){
			$timeout = $this->timeout;
		}
		$buf = '';
		$start = time();
		while(strpos($buf, $str) === false){
			$b = $this->read();
			if(!empty($b)){
				$start = time();
				$buf .= $b;
				
				if($this->pager !== null && strpos($b, $this->pager) !== false){
					$this->write("\n");
				}
			}else{
				usleep(10000);
				
				if((time() - $start) >= $timeout){
					break;
				}
			}
		}
		return $buf;
	}
	
	function expect_regex($str, $timeout = null){
		if($timeout === null){
			$timeout = $this->timeout;
		}
		$buf = '';
		$start = time();
		while(!preg_match($str,$buf)){
			$b = $this->read();
			if(!empty($b)){
				$start = time();
				$buf .= $b;
				
				if($this->pager !== null && strpos($b, $this->pager) !== false){
					$this->write("\n");
				}
			}else{
				usleep(10000);
				
				if((time() - $start) >= $timeout){
					break;
				}
			}
		}
		return $buf;
	}
	
	function expect_str_throw($str, $timeout = null){
		$buf = $this->expect_str($str, $timeout);
		if(strpos($buf, $str) === false){
			throw new \Exception('Expected "'.$str.'", not found');
		}
		return $buf;
	}
	
	function expect_regex_throw($str, $timeout = null){
		$buf = $this->expect_regex($str, $timeout);
		if(preg_match($str, $buf) === false){
			throw new \Exception('Expected regex:"'.$str.'", not found');
		}
		return $buf;
	}
}