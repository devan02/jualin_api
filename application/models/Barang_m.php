<?php defined('BASEPATH') or exit('No direct script access allowed');

class Barang_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getAllBarang($page, $limit, $keyword, $status)
	{
		$where = "1 = 1";
		if($keyword != ""){
			$where = $where." AND (NAMA_BARANG LIKE '%$keyword%') OR (PLU LIKE '%$keyword%') OR (BARCODE LIKE '%$keyword%')";
		}

		if($status != ""){
			$where = $where." AND STATUS = '$status'";
		}

		$page = $page ? $page : 0;
		$limit = $limit ? $limit : 10000;

		$sql = "SELECT * FROM adm_barang WHERE $where LIMIT $page, $limit";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getDetailBarang($id)
	{
		$sql = "SELECT * FROM adm_barang WHERE ID = ?";
		$query = $this->db->query($sql, array($id));
		return $query->row_array();
	}

	function getBarangByPlu($plu)
	{
		$sql = "SELECT * FROM adm_barang WHERE PLU = ?";
		$query = $this->db->query($sql, array($plu));
		return $query->row_array();
	}

	function getBarangAfterInsert()
	{
		$sql = "SELECT * FROM adm_barang ORDER BY ID DESC LIMIT 1";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function postBarang($value)
	{
		$insert = $this->db->insert('adm_barang', $value);
		return $insert;
	}
	
}
?>