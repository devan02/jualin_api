<?php defined('BASEPATH') or exit('No direct script access allowed');

class ErrorHandling_m extends CI_Model
{
	public function __construct()
	{
		
	}

	public function only_number($value)
	{
		$numeric = is_numeric($value);
		$status = $numeric ? true : false;
		return $status;
	}

	public function digitPhoneNumber($value)
	{
		$digit = strlen($value);
		$status = '';
		if($digit < 12){
			$status = 'kurang';
		}else if($digit > 13){
			$status = 'lebih';
		}
		return $status;
	}

}