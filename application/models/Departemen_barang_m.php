<?php defined('BASEPATH') or exit('No direct script access allowed');

class Departemen_barang_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getDepartemen($page, $limit, $where)
	{
		$sql = "SELECT * FROM adm_departemen_barang WHERE $where LIMIT $page, $limit";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getDepartemenById($id)
	{
		$sql = "SELECT * FROM adm_departemen_barang WHERE ID = ?";
		$query = $this->db->query($sql, array($id));
		return $query->row_array();
	}

	function getDepartemenByCode($kode)
	{
		$sql = "SELECT * FROM adm_departemen_barang WHERE KODE = ?";
		$query = $this->db->query($sql, array($kode));
		return $query->row_array();
	}

	function getDepartemenAfterInsert()
	{
		$sql = "SELECT * FROM adm_departemen_barang ORDER BY ID DESC LIMIT 1";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function postDepartemen($value)
	{
		$result = $this->db->insert('adm_departemen_barang', $value);
		return $result;
	}

	function editDepartemen($id,$value)
	{
		$this->db->where('ID', $id);
		$result = $this->db->update('adm_departemen_barang', $value);
		return $result;
	}

	
}
?>