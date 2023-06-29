<?php defined('BASEPATH') or exit('No direct script access allowed');

class WhitelistEmail_m extends CI_Model
{
	public function __construct()
	{
		
	}

	// validasi email dengan domain
	function checkingEmail($email)
	{
		$listEmail = array('gmail.com','yahoo.com','ymail.com','yahoo.jp');
		$pecahEmail = explode('@', $email)[1];
		$list = in_array($pecahEmail, $listEmail);
		return $list;
	}

}
?>