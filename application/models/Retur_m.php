<?php defined('BASEPATH') or exit('No direct script access allowed');
class Retur_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getRetur($id_supplier, $tanggal)
	{
		$where = "1 = 1";

		if($id_supplier != ""){
			$where = $where." AND ID_SUPPLIER = $id_supplier";
		}

		if($tanggal != ""){
			$where = $where." AND TANGGAL_RETUR = '$tanggal'";
		}

		$sql = "SELECT * FROM adm_retur_barang WHERE $where AND STATUS = 'actived'";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getReturDetail($no_retur)
	{
		$sql = "SELECT * FROM adm_retur_barang_detail WHERE NO_RETUR = ?";
		$query = $this->db->query($sql, array($no_retur));
		return $query->result();
	}

	function getReturAfterInsert()
	{
		$sql = "SELECT * FROM adm_retur_barang ORDER BY ID DESC LIMIT 1";
		$query = $this->db->query($sql);
		return $query->row_array();
	}	

	function postRetur($value)
	{
		$insert = $this->db->insert('adm_retur_barang', $value);
		return $insert;
	}

	function postDetailRetur($value)
	{
		$insert = $this->db->insert('adm_retur_barang_detail', $value);
		return $insert;
	}

	function hapusRetur($id, $value)
	{
		$this->db->where('ID', $id);
		$delete = $this->db->update('adm_retur_barang', $value);
		return $delete;
	}

}