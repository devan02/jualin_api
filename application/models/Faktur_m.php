<?php defined('BASEPATH') or exit('No direct script access allowed');

class Faktur_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getFakturAfterInsert()
	{
		$sql = "SELECT * FROM adm_faktur ORDER BY ID DESC LIMIT 1";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function getDetailFakturAfterInsert($no)
	{
		$sql = "SELECT a.*, b.NAMA_BARANG FROM adm_faktur_detail a 
		JOIN adm_barang b ON b.ID = a.ID_BARANG
		WHERE a.NO_FAKTUR = ?
		";
		$query = $this->db->query($sql, array($no));
		return $query->result();
	}
	
	function postFakturKirim($value)
	{
		$insert = $this->db->insert('adm_faktur', $value);
		return $insert;
	}

	function postDetailFakturKirim($value)
	{
		$insert = $this->db->insert('adm_faktur_detail', $value);
		return $insert;
	}

}
?>