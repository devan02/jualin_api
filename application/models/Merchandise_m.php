<?php defined('BASEPATH') or exit('No direct script access allowed');

class Merchandise_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getMerchandiseByIdBarang($id_barang){		
		$sql = "
			SELECT
				a.ID,
				a.PLU,
				a.BARCODE,
				a.NAMA_BARANG,
				a.DEPARTEMEN_BARANG,
				a.KATEGORI,
				a.SUPPLIER,
				b.PPN,
				a.HARGA_BELI,
				a.HARGA_JUAL,
				a.STOK_SAAT_INI
			FROM adm_barang a
			LEFT JOIN adm_receive_barang b ON b.ID_SUPPLIER = a.ID_SUPPLIER
			WHERE a.ID = '$id_barang'
			GROUP BY a.ID
		";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function getAvgFromRcvDetailByIdBarang($id_barang){
		$sql = "SELECT a.ID_BARANG, AVG(a.HARGA_BELI) AS AVG_HARGA_BELI FROM adm_receive_barang_detail a WHERE a.ID_BARANG = '$id_barang'";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function getLastSoldByIdBarang($id_barang){
		$sql = "SELECT ID, ID_BARANG, CREATE_DATE, DATE_FORMAT(CREATE_DATE, '%d-%m-%Y') AS LAST_SOLD FROM kasir_detail_transaksi WHERE ID_BARANG = '$id_barang' ORDER BY ID DESC LIMIT 1;";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function getRcvDetailByIdBarang($id_barang, $tgl_awal, $tgl_akhir, $tanggal_awal, $tanggal_akhir){
		$where = "";

		if($tgl_awal != "" && $tgl_akhir != ""){
			$where = "AND DATE(a.CREATE_DATE) >= '$tanggal_awal' AND DATE(a.CREATE_DATE) <= '$tanggal_akhir'";
		}

		$sql = "
			SELECT 
				a.ID,
				a.ID_BARANG,
				a.HARGA_BELI,
				a.QTY,
				b.SUPPLIER,
				a.CREATE_DATE,
				DATE_FORMAT(a.CREATE_DATE, '%d-%m-%Y') AS TANGGAL
			FROM adm_receive_barang_detail a
			LEFT JOIN adm_barang b ON b.ID = a.ID_BARANG
			WHERE a.ID_BARANG = '$id_barang' $where	
		";
		$query = $this->db->query($sql);
		return $query->result();
	}

}