<?php defined('BASEPATH') or exit('No direct script access allowed');

class Generate_nomor_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function check_nomor($keterangan, $huruf='')
	{
		$where = "1 = 1";
		$whereUpdate = "";
		if($keterangan == 'SUPPLIER'){
			$where = $where." AND KETERANGAN = ? AND HURUF = UPPER('$huruf')";
			$whereUpdate = array('KETERANGAN' => $keterangan, 'HURUF' => $huruf);
		}else{
			$where = $where." AND KETERANGAN = ?";
			$whereUpdate = array('KETERANGAN' => $keterangan);
		}

		$sql = "SELECT * FROM adm_nomor WHERE $where";
		$query = $this->db->query($sql, array($keterangan));
		$result = $query->result();

		if(count($result) > 0){ //jika nomor ada maka +1 dan di edit
			$row = $query->row_array();
			$next = $row['NEXT'] + 1;
			$value = array(
				'KETERANGAN' => $keterangan, 
				'NEXT' => $next,
				'MODIFY_DATE' => date('Y-m-d H:i:s')
			);
			$this->db->where($whereUpdate);
			$this->db->update('adm_nomor', $value);
		}else{ //jika kosong default = 1 dan di insert
			$nomor = 1;
			$value = array(
				'KETERANGAN' => $keterangan, 
				'HURUF' => ($keterangan == 'Supplier') ? strtoupper($huruf) : null, 
				'NEXT' => $nomor,
				'CREATE_DATE' => date('Y-m-d H:i:s')
			);
			$this->db->insert('adm_nomor', $value);
		}
	}

	function get_nomor($keterangan, $huruf='')
	{
		$where = "1 = 1";
		if($keterangan == 'SUPPLIER'){
			$where = $where." AND KETERANGAN = ? AND HURUF = UPPER('$huruf')";
		}else{
			$where = $where." AND KETERANGAN = ?";
		}

		$sql = "SELECT * FROM adm_nomor WHERE $where";
		$query = $this->db->query($sql, array($keterangan));
		$result = $query->result();

		if(count($result) > 0){ //jika nomor ada maka +1
			$row = $query->row_array();
			$next = $row['NEXT'] + 1;
			return $next;
		}else{ //jika kosong default = 1
			$nomor = 1;
			return $nomor;
		}
	}

	//NOMOR LPB
	function get_nomor_lpb()
	{
		$keterangan = 'LPB';
		$bulan = date('n');
		$tahun = date('Y');

		$sql = "SELECT * FROM adm_nomor_receive WHERE KETERANGAN = ? AND BULAN = ? AND TAHUN = ?";
		$query = $this->db->query($sql, array($keterangan, $bulan, $tahun));
		$result = $query->result();

		if(count($result) > 0){ //jika nomor ada maka +1
			$row = $query->row_array();
			$next = $row['NEXT'] + 1;
			return $next;
		}else{ //jika kosong default = 1
			$nomor = 1;
			return $nomor;
		}
	}

	function check_nomor_lpb()
	{
		$keterangan = 'LPB';
		$bulan = date('n');
		$tahun = date('Y');

		$sql = "SELECT * FROM adm_nomor_receive WHERE KETERANGAN = ? AND BULAN = ? AND TAHUN = ?";
		$query = $this->db->query($sql, array($keterangan, $bulan, $tahun));
		$result = $query->result();

		if(count($result) > 0){ //jika nomor ada maka +1 dan di edit
			$row = $query->row_array();
			$next = $row['NEXT'] + 1;
			$value = array(
				'KETERANGAN' => $keterangan, 
				'NEXT' => $next,
				'MODIFY_DATE' => date('Y-m-d H:i:s')
			);
			$this->db->where(array('KETERANGAN' => $keterangan, 'BULAN' => $bulan, 'TAHUN' => $tahun));
			$this->db->update('adm_nomor_receive', $value);
		}else{ //jika kosong default = 1 dan di insert
			$nomor = 1;
			$value = array(
				'KETERANGAN' => $keterangan, 
				'BULAN' => $bulan,
				'TAHUN' => date('Y'), 
				'NEXT' => $nomor,
				'CREATE_DATE' => date('Y-m-d H:i:s')
			);
			$this->db->insert('adm_nomor_receive', $value);
		}
	}

	//NOMOR TRX KASIR
	function get_nomor_trx()
	{
		$keterangan = 'TRX';
		$tanggal = date('Y-m-d');

		$sql = "SELECT * FROM adm_nomor_kasir WHERE KETERANGAN = ? AND TANGGAL = ?";
		$query = $this->db->query($sql, array($keterangan, $tanggal));
		$result = $query->result();

		if(count($result) > 0){ //jika nomor ada maka +1
			$row = $query->row_array();
			$next = $row['NEXT'] + 1;
			return $next;
		}else{ //jika kosong default = 1
			$nomor = 1;
			return $nomor;
		}
	}

	function check_nomor_trx()
	{
		$keterangan = 'TRX';
		$tanggal = date('Y-m-d');

		$sql = "SELECT * FROM adm_nomor_kasir WHERE KETERANGAN = ? AND TANGGAL = ?";
		$query = $this->db->query($sql, array($keterangan, $tanggal));
		$result = $query->result();

		if(count($result) > 0){ //jika nomor ada maka +1 dan di edit
			$row = $query->row_array();
			$next = $row['NEXT'] + 1;
			$value = array(
				'KETERANGAN' => $keterangan, 
				'TANGGAL' => $tanggal, 
				'NEXT' => $next,
				'MODIFY_DATE' => date('Y-m-d H:i:s')
			);
			$this->db->where(array('KETERANGAN' => $keterangan, 'TANGGAL' => $tanggal));
			$this->db->update('adm_nomor_kasir', $value);
		}else{ //jika kosong default = 1 dan di insert
			$nomor = 1;
			$value = array(
				'KETERANGAN' => $keterangan, 
				'TANGGAL' => $tanggal, 
				'NEXT' => $nomor,
				'CREATE_DATE' => date('Y-m-d H:i:s')
			);
			$this->db->insert('adm_nomor_kasir', $value);
		}
	}

	// NOMOR RETUR
	function get_nomor_retur()
	{
		$keterangan = 'RETUR';
		$bulan = date('n');
		$tahun = date('Y');

		$sql = "SELECT * FROM adm_nomor_retur WHERE KETERANGAN = ? AND BULAN = ? AND TAHUN = ?";
		$query = $this->db->query($sql, array($keterangan, $bulan, $tahun));
		$result = $query->result();

		if(count($result) > 0){ //jika nomor ada maka +1
			$row = $query->row_array();
			$next = $row['NEXT'] + 1;
			return $next;
		}else{ //jika kosong default = 1
			$nomor = 1;
			return $nomor;
		}
	}

	function check_nomor_retur()
	{
		$keterangan = 'RETUR';
		$bulan = date('n');
		$tahun = date('Y');

		$sql = "SELECT * FROM adm_nomor_retur WHERE KETERANGAN = ? AND BULAN = ? AND TAHUN = ?";
		$query = $this->db->query($sql, array($keterangan, $bulan, $tahun));
		$result = $query->result();

		if(count($result) > 0){ //jika nomor ada maka +1 dan di edit
			$row = $query->row_array();
			$next = $row['NEXT'] + 1;
			$value = array(
				'KETERANGAN' => $keterangan, 
				'NEXT' => $next,
				'MODIFY_DATE' => date('Y-m-d H:i:s')
			);
			$this->db->where(array('KETERANGAN' => $keterangan, 'BULAN' => $bulan, 'TAHUN' => $tahun));
			$this->db->update('adm_nomor_retur', $value);
		}else{ //jika kosong default = 1 dan di insert
			$nomor = 1;
			$value = array(
				'KETERANGAN' => $keterangan, 
				'BULAN' => $bulan,
				'TAHUN' => date('Y'), 
				'NEXT' => $nomor,
				'CREATE_DATE' => date('Y-m-d H:i:s')
			);
			$this->db->insert('adm_nomor_retur', $value);
		}
	}

	//FAKTUR KIRIM
	function get_nomor_faktur_kirim($keterangan)
	{		
		$bulan = date('n');
		$tahun = date('Y');

		$sql = "SELECT * FROM adm_nomor_faktur WHERE KETERANGAN = ? AND BULAN = ? AND TAHUN = ?";
		$query = $this->db->query($sql, array($keterangan, $bulan, $tahun));
		$result = $query->result();

		if(count($result) > 0){ //jika nomor ada maka +1
			$row = $query->row_array();
			$next = $row['NEXT'] + 1;
			return $next;
		}else{ //jika kosong default = 1
			$nomor = 1;
			return $nomor;
		}
	}

	function check_nomor_faktur_kirim($keterangan)
	{		
		$bulan = date('n');
		$tahun = date('Y');

		$sql = "SELECT * FROM adm_nomor_faktur WHERE KETERANGAN = ? AND BULAN = ? AND TAHUN = ?";
		$query = $this->db->query($sql, array($keterangan, $bulan, $tahun));
		$result = $query->result();

		if(count($result) > 0){ //jika nomor ada maka +1 dan di edit
			$row = $query->row_array();
			$next = $row['NEXT'] + 1;
			$value = array(
				'KETERANGAN' => $keterangan, 
				'NEXT' => $next,
				'MODIFY_DATE' => date('Y-m-d H:i:s')
			);
			$this->db->where(array('KETERANGAN' => $keterangan, 'BULAN' => $bulan, 'TAHUN' => $tahun));
			$this->db->update('adm_nomor_faktur', $value);
		}else{ //jika kosong default = 1 dan di insert
			$nomor = 1;
			$value = array(
				'KETERANGAN' => $keterangan, 
				'BULAN' => $bulan,
				'TAHUN' => date('Y'), 
				'NEXT' => $nomor,
				'CREATE_DATE' => date('Y-m-d H:i:s')
			);
			$this->db->insert('adm_nomor_faktur', $value);
		}
	}
	
}
?>