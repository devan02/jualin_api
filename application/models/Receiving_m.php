<?php defined('BASEPATH') or exit('No direct script access allowed');

class Receiving_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getReceive($keyword, $penanda, $tglAwal, $tglAkhir)
	{
		$where = "1 = 1";

		if($keyword != ""){
			$where = $where." AND (CODE_RECEIVING LIKE '%$keyword%' OR NAMA_SUPPLIER LIKE '%$keyword%')";
		}

		if($penanda === "tanggal_datang"){
			$where = $where." AND TANGGAL_DATANG > DATE_FORMAT('$tglAwal','%Y-%m-%d') 
							  AND TANGGAL_DATANG < DATE_FORMAT('$tglAkhir','%Y-%m-%d')";
		}

		if($penanda === "tanggal_diterima"){
			$where = $where." AND TANGGAL_TERIMA > DATE_FORMAT('$tglAwal','%Y-%m-%d')
							  AND TANGGAL_TERIMA < DATE_FORMAT('$tglAkhir','%Y-%m-%d')";
		}

		$sql = "SELECT * FROM tb_receiving WHERE $where";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getReceiveAfterInsert()
	{
		$sql = "SELECT * FROM adm_receive_barang ORDER BY ID DESC LIMIT 1";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getDetailReceiveAfterInsert($no_lpb)
	{
		$sql = "SELECT * FROM adm_receive_barang_detail WHERE NO_LPB = ?";
		$query = $this->db->query($sql, array($no_lpb));
		return $query->result();
	}
	
	function postReceiving($value)
	{
		$insert = $this->db->insert('adm_receive_barang', $value);
		return $insert;
	}

	function postDetailReceiving($value)
	{
		$insert = $this->db->insert('adm_receive_barang_detail', $value);
		return $insert;
	}

	function updatePpnSupplier($id_sup,$value){
		$this->db->where('ID', $id_sup);
		$update = $this->db->update('adm_supplier', $value);
		return $update;
	}
	
}
?>