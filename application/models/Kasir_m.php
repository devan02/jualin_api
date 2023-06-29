<?php defined('BASEPATH') or exit('No direct script access allowed');

class Kasir_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getBarangKasir($keyword)
	{
		$where = "1 = 1";
		if($keyword != ""){
			$where = $where." AND (BARCODE LIKE '%$keyword%' OR PLU LIKE '%$keyword%' OR NAMA_BARANG LIKE '%$keyword%')";
		}

		$sql = "SELECT * FROM adm_barang WHERE $where";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function getBarangById($id)
	{		
		$sql = "SELECT * FROM adm_barang WHERE ID = $id";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function getTransaksi($tanggal,$status)
	{
		$where = "";
		if($status != ""){
			$where = "AND STATUS = '$status'";
		}
		$sql = "SELECT * FROM kasir_transaksi WHERE TANGGAL = ? $where";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getLastTransaksi($tanggal,$status)
	{
		$where = "";
		if($status != ""){
			$where = "AND STATUS = '$status'";
		}
		$sql = "SELECT * FROM kasir_transaksi WHERE TANGGAL = '$tanggal' $where ORDER BY ID DESC LIMIT 1";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function getTransaksiById($id)
	{
		$sql = "SELECT * FROM kasir_transaksi WHERE ID = ?";
		$query = $this->db->query($sql, array($id));
		return $query->row_array();
	}

	function getTransaksiByNoTrx($no_trx, $tanggal)
	{
		$sql = "SELECT * FROM kasir_transaksi WHERE NO_TRX = ? AND TANGGAL = ? AND STATUS = 'finish'";
		$query = $this->db->query($sql, array($no_trx, $tanggal));
		return $query->row_array();
	}

	function getDetailTransaksiByNoTrxToday($no_trx, $tanggal){
		$sql = "SELECT * FROM kasir_detail_transaksi WHERE NO_TRX = ? AND DATE(CREATE_DATE) = ? AND STATUS_RETUR IS NULL";
		$query = $this->db->query($sql, array($no_trx, $tanggal));
		return $query->result();
	}

	function getDetailTransaksiByNoTrxTodayFinish($no_trx, $tanggal){
		$sql = "SELECT * FROM kasir_detail_transaksi WHERE NO_TRX = ? AND DATE(CREATE_DATE) = ? AND STATUS_RETUR = 'true'";
		$query = $this->db->query($sql, array($no_trx, $tanggal));
		return $query->result();
	}

	function getDetailTransaksiByNoTrxTodayStruk($no_trx, $tanggal){
		$sql = "SELECT * FROM kasir_detail_transaksi WHERE NO_TRX = ? AND DATE(CREATE_DATE) = ? AND STATUS = 'finish'";
		$query = $this->db->query($sql, array($no_trx, $tanggal));
		return $query->result();
	}

	function postTransaksi($value)
	{
		$insert = $this->db->insert('kasir_transaksi', $value);
		return $insert;
	}

	function postDetailTransaksi($value)
	{
		$insert = $this->db->insert('kasir_detail_transaksi', $value);
		return $insert;
	}

	//PENDING
	function getTrxPending($tanggal, $no_trx, $page, $limit)
	{
		$where = "";
		if($no_trx != ""){
			$where = "AND NO_TRX = '$no_trx'";
		}

		$sql = "SELECT * FROM kasir_transaksi WHERE STATUS = 'pending' AND TANGGAL = ? $where LIMIT $page, $limit";
		$query = $this->db->query($sql,array($tanggal));
		return $query->result();
	}

	function updateStatusTransaksi($id, $value)
	{
		$this->db->where('ID', $id);
		$update = $this->db->update('kasir_transaksi', $value);
		return $update;
	}

	// BARANG DISKON
	function getBarangDiskon($id_barang, $qty, $tanggal)
	{
		$sql = "SELECT * FROM adm_diskon WHERE DATE(TANGGAL_AKHIR) >= DATE($tanggal) AND ID_BARANG = $id_barang AND QTY_TRIGGER_DISKON <= $qty";
		$query = $this->db->query($sql);
		return $query->row_array();
	}	

	function getBarangNotDiskon($id_barang)
	{
		$sql = "SELECT * FROM adm_barang WHERE ID = ?";
		$query = $this->db->query($sql, array($id_barang));
		return $query->row_array();
	}

	//RETUR
	function getTransaksiReturToday($no_trx_retur, $tanggal, $id_barang)
	{
		$sql = "SELECT * FROM kasir_detail_transaksi WHERE NO_TRX = '$no_trx_retur' AND DATE(CREATE_DATE) = '$tanggal' AND ID_BARANG = '$id_barang' AND STATUS_RETUR = 'true'";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function updateStatusRetur($no_trx, $id_barang, $qty, $tanggal)
	{
		$sql = "UPDATE kasir_detail_transaksi SET STATUS_RETUR = 'true', QTY_RETUR = '$qty' WHERE NO_TRX = '$no_trx' AND ID_BARANG = '$id_barang' AND DATE(CREATE_DATE) = '$tanggal'";
		$query = $this->db->query($sql);
		return $query;
	}

	function updateStatusReturFinish($no_trx_retur, $id_barang, $tanggal)
	{
		$sql = "UPDATE kasir_detail_transaksi SET STATUS_RETUR = 'finish' WHERE NO_TRX = '$no_trx_retur' AND ID_BARANG = '$id_barang' AND DATE(CREATE_DATE) = '$tanggal' AND STATUS_RETUR = 'true'";
		$query = $this->db->query($sql);
		return $query;
	}

	function updateStokBarangFromRetur($id_barang, $qty_retur)
	{
		$sql = "UPDATE adm_barang SET STOK_LAMA = STOK_SAAT_INI, STOK_SAAT_INI = STOK_SAAT_INI + $qty_retur WHERE ID = '$id_barang' AND STATUS = 'actived'";
		$query = $this->db->query($sql);
		return $query;
	}

	//CLOSING
	function cekTrxFinishToday($tanggal){
		$sql = "SELECT * FROM kasir_transaksi WHERE TANGGAL = '$tanggal' AND STATUS = 'finish'";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getTotalSistemToday($tanggal){
		$sql = "SELECT SUM(TOTAL) AS TOTAL FROM kasir_transaksi WHERE TANGGAL = '$tanggal' AND STATUS = 'finish'";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function getStrukClosing($tanggal)
	{
		$sql = "SELECT * FROM kasir_closing WHERE TANGGAL = '$tanggal'";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function updateStatusClosing($tanggal){
		$sql = "UPDATE kasir_transaksi SET STATUS = 'closing' WHERE TANGGAL = '$tanggal' AND STATUS = 'finish'";
		$query = $this->db->query($sql);
		return $query;
	}

	function updateStatusClosingDetailTrx($tanggal){
		$sql = "UPDATE kasir_detail_transaksi SET STATUS = 'closing' WHERE DATE(CREATE_DATE) = '$tanggal' AND STATUS = 'finish' AND STATUS_RETUR IS NULL";
		$query = $this->db->query($sql);
		return $query;
	}

	function insertClosing($value){
		$this->db->insert('kasir_closing', $value);
	}

}