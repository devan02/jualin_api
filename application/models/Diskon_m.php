<?php defined('BASEPATH') or exit('No direct script access allowed');

class Diskon_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getDataBarangDiskon($id_barang, $tgl_akhir, $tanggal_search)
	{
		$where = "1 = 1";

		if(!empty($id_barang)){
			$where = $where." AND ID_BARANG = $id_barang";
		}

		if($tgl_akhir != ""){
			$where = $where." AND a.TGL_AKHIR_SEARCH >= '$tanggal_search'";
		}

		$sql = "
			SELECT
				a.*
			FROM(
				SELECT 
					a.ID,
					a.ID_BARANG,
					a.PLU,
					a.NAMA_BARANG,
					a.HARGA,
					a.JENIS_DISKON,
					a.KETERANGAN,
					a.QTY_TRIGGER_DISKON,
					a.QTY_BONUS,
					a.DISKON_PERSEN,
					a.DISKON_RUPIAH,
					a.HARGA_DISKON,
					DATE_FORMAT(a.TANGGAL_AWAL, '%d-%m-%Y') AS TANGGAL_AWAL,
					DATE_FORMAT(a.TANGGAL_AKHIR, '%d-%m-%Y') AS TANGGAL_AKHIR,
					a.TANGGAL_AKHIR AS TGL_AKHIR_SEARCH,
					a.`STATUS`,
					a.CREATE_DATE
				FROM adm_diskon a
			) a
			WHERE $where
		";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getBarangDiskonByEndDate($id_barang, $tgl_akhir)
	{		
		$sql = "
			SELECT 
				a.ID,
				a.ID_BARANG,
				a.PLU,
				a.NAMA_BARANG,
				a.HARGA,
				a.JENIS_DISKON,
				a.KETERANGAN,
				a.QTY_TRIGGER_DISKON,
				a.QTY_BONUS,
				a.DISKON_PERSEN,
				a.DISKON_RUPIAH,
				a.HARGA_DISKON,
				a.TANGGAL_AWAL,
				a.TANGGAL_AKHIR,
				a.`STATUS`,
				a.CREATE_DATE
			FROM adm_diskon a
			WHERE a. ID_BARANG = '$id_barang' AND a.TANGGAL_AKHIR >= '$tgl_akhir' AND a.STATUS = 'actived'
		";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getDiskonById($id)
	{
		$sql = "SELECT * FROM adm_diskon WHERE ID = ?";
		$query = $this->db->query($sql, array($id));
		return $query->row_array();
	}

	function insertDiskon($value){
		$insert = $this->db->insert('adm_diskon', $value);
		return $insert;
	}

	function updateDiskon($id, $value){
		$this->db->where('ID', $id);
		$delete = $this->db->update('adm_diskon', $value);
		return $delete;
	}

}