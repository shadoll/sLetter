<?php
/**
 *
 * @author sHa <sha@shadoll.com>
 * @package sLetter
 * @version 18.2.13-8
 *
 */

namespace shadoll;

use Mailgun\Mailgun;

class sLetter{

	public $error = false;
	public $status = "";

	private $senderDetect = true;
	private $sender = "mail";
	private $header = "";
	private $message = "";
	private $subject = "";

	private $language = "en";
	private $lang = [];

	private $fromMail = "";
	private $fromName = "";
	private $toMail = "";
	private $senderIP = null;

	public $fields = null;
	private $logoUri = "";

	function __construct(){
		$this->setLang(require_once(__DIR__."/lng/".$this->language.".php"));
		if($this->senderDetect)
			$this->detect();
	}

	function setData($data,$return=false){
		if(empty($data) || !is_array($data))
			return $return?$this->fields:$this;

		if(is_null($this->fields))
			$this->fields = [];

		foreach($data as $key=>$val)
			if(!empty($val))
				$this->fields[$key] = is_string($val)?trim(stripslashes(strip_tags($val))):$val;

		return $return?$this->fields:$this;
	}

	function setLang($data,$return=false){
		if(empty($data) || !is_array($data))
			return $return?$this->lang:$this;

		if(is_null($this->lang))
			$this->lang = [];

		foreach($data as $key=>$val)
			if(!empty($val))
				$this->lang[$key] = is_string($val)?trim(stripslashes($val)):$val;

		return $return?$this->lang:$this;
	}

	function set($data){
		if(!empty($data) && is_array($data))
			foreach($data as $key=>$val)
				if(!empty($key) && !empty($val))
					$this->{$key} = is_string($val)?trim(stripslashes($val)):$val;
		return $this;
	}

	function get($val){
		if(property_exists($this,$val))
            return $this->{$val};
        return null;
	}

	function header($return=false){
		$this->header .= "From:".(!empty($this->fromName)?($this->fromName."<".strip_tags($this->fromMail).">"):strip_tags($this->fromMail))."\r\n";
		$this->header .= "Reply-To: ".strip_tags($this->fromMail)."\r\n";
		$this->header .= "MIME-Version: 1.0\r\n";
		$this->header .= "Content-Type: text/html;charset=utf-8 \r\n";

		return $return?$this->header:$this;
	}

	function message($return=false){
		$this->message .= "<html><body style='font-family:Arial,sans-serif;'>";

		if(!empty($this->logoUri))
			$this->message .= "<img src='".$this->logoUri."' alt='logo' style='max-height:150px;'>";

		$this->message .= "<h2 style='font-weight:bold;border-bottom:1px dotted #ccc;'>".$this->subject."</h2>\r\n";

		foreach($this->fields as $name=>$val){
			if(!empty($val)){
				$this->message .= "<p><strong>".(array_key_exists($name,$this->lang)?$this->lang[$name]:$name).":</strong> ".$val."</p>\r\n";
			}
		}

		$this->message .= "</body></html>";

		return $return?$this->message:$this;
	}

	function detect($return=false){
		if(empty($this->senderIP))
		$this->senderIP = $_SERVER['REMOTE_ADDR'];

		if(!empty($this->senderIP)){
			$query = @unserialize(file_get_contents('http://ip-api.com/php/'.$this->senderIP));
			if($query && $query['status'] == 'success'){


				$this->setData([
					'senderIP' => $this->senderIP,
					'flag' => "<img style='height:14px; width:auto' src='http://www.geognos.com/api/en/countries/flag/".$query['countryCode'].".png'>",
					'country' => $query['country'],
					'region' => $query['regionName'],
					'city' => $query['city'],
					'provider' => $query['isp'],
				]);
			}
		}
		return $return?$this->fields:$this;
	}

	function validate($data,$return=false){
		$is_valid = GUMP::is_valid($this->fields, $data);
		if($is_valid !== true){
			$this->error = true;
			$this->status = $is_valid;
		}

		return $return?$this->error:$this;
	}

	function subject($return=false){
		if(empty($this->subject))
			$this->subject = (array_key_exists("subject",$this->lang)?$this->lang["subject"]:"Message from")." ".$_SERVER['SERVER_NAME'];

		return $return?$this->subject:$this;
	}

	function sendMail($return=false){
		$this->header();

		$status = @mail($this->toMail, $this->subject(true), $this->message, $this->header);


		return $return?$status:$this;
	}

	function sendMailgun($return=false){
		$mg = new Mailgun($this->mailgun_apikey);

		$status = $mg->sendMessage($this->mailgun_domain, array(
			'from'    => !empty($this->fromName)?($this->fromName."<".strip_tags($this->fromMail).">"):strip_tags($this->fromMail),
			'to'      => $this->toMail,
			'subject' => $this->subject(true),
			'html'    => $this->message,
		));

		return $return?$status:$this;
	}

	function send($return=false){
		if($this->error===false){
			if(empty($this->message))
				$this->message();

			if(!empty($this->sender)){
				$method = "send".ucfirst($this->sender);

				if(method_exists($this,$method))
					$status = $this->{$method}(true);

			}
		}
		else
			$status = "";

		return $return?$status:$this;
	}

	function state(){
		echo json_encode(['success'=>!$this->error,'message'=>$this->status,'answer'=>'']);
	}
}
