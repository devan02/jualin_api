<?php defined('BASEPATH') or exit('No direct script access allowed');

class Kategori_barang_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getKategori($page, $limit, $where)
	{
		$sql = "SELECT * FROM adm_kategori WHERE $where LIMIT $page, $limit";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getKategoriById($id)
	{
		$sql = "SELECT * FROM adm_kategori WHERE ID = ?";
		$query = $this->db->query($sql, array($id));
		return $query->row_array();
	}

	function getKategoriAfterInsert()
	{
		$sql = "SELECT * FROM adm_kategori ORDER BY ID DESC LIMIT 1";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function getKategoriByCode($kode)
	{
		$sql = "SELECT * FROM adm_kategori WHERE KODE = ?";
		$query = $this->db->query($sql, array($kode));
		return $query->row_array();
	}

	function postKategori($value)
	{
		$insert = $this->db->insert('adm_kategori', $value);
		return $insert;
	}

	function editKategori($id,$value)
	{
		$this->db->where('ID', $id);
		$result = $this->db->update('adm_kategori', $value);
		return $result;
	}

}
?>