<?php defined('BASEPATH') or exit('No direct script access allowed');

class Auth_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getUserByUsername($username)
	{
		$sql = "SELECT * FROM user WHERE USERNAME = ?";
		$query = $this->db->query($sql, array($username));
		return $query->result();
	}

	function getDataUserLimit($username)
	{
		$sql = "SELECT ID, USERNAME, LEVEL, CREATEDATE FROM user WHERE USERNAME = ?";
		$query = $this->db->query($sql, array($username));
		return $query->result();
	}

	function getDataUserRow($username)
	{
		$sql = "SELECT * FROM user WHERE USERNAME = ?";
		$query = $this->db->query($sql, array($username));
		return $query->row_array();
	}

	function getDataUserById($id)
	{
		$sql = "
			SELECT 
				a.ID,
				a.USERNAME,
				a.`LEVEL`
			FROM user a 
			WHERE a.ID = ?
		";
		$query = $this->db->query($sql, array($id));
		return $query->row_array();
	}

	function updateTokenLogin($username, $value){
		$this->db->where('USERNAME', $username);
		$update = $this->db->update('user', $value);
		return $update;
	}

	
}
?>