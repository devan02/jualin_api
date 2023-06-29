<?php defined('BASEPATH') or exit('No direct script access allowed');

class Supplier_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getAllSupplier($page, $limit, $where)
	{
		$sql = "SELECT * FROM adm_supplier WHERE $where LIMIT $page, $limit";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getSupplierAfterPost()
	{
		$sql = "SELECT * FROM adm_supplier ORDER BY ID DESC LIMIT 1";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getSupplierById($id)
	{
		$sql = "SELECT * FROM adm_supplier WHERE ID = ?";
		$query = $this->db->query($sql, array($id));
		return $query->row_array();
	}

	function checkEmailExist($email)
	{
		$sql = "SELECT * FROM adm_supplier WHERE EMAIL = ?";
		$query = $this->db->query($sql, array($email));
		return $query->result();
	}

	function postSuppier($value)
	{
		$insert = $this->db->insert('adm_supplier', $value);
		return $insert;
	}

	function editSupplier($id,$value){
		$this->db->where("ID", $id);
		$update = $this->db->update('adm_supplier', $value);
		return $update;
	}
	
}
?>