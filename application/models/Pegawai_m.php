<?php defined('BASEPATH') or exit('No direct script access allowed');
class Pegawai_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function cekNip($nip)
	{
		$sql = "SELECT * FROM adm_pegawai WHERE NIP = ?";
		$query = $this->db->query($sql, array($nip));
		return $query->row_array();
	}

	function dataPegawai($id,$filter,$keyword)
	{
		$where = "";
		if($id != "" || $id != null){
			$where = "WHERE ID = $id";
		}else{
			if($filter == 'nama'){
				if($keyword != ""){
					$where = "WHERE NAMA_LENGKAP LIKE '%$keyword%'";
				}
			}

			if($filter == 'kota'){
				if($keyword != ""){
					$where = "WHERE KOTA = '$keyword'";
				}
			}
		}

		$sql = "SELECT * FROM adm_pegawai $where";
		$query = $this->db->query($sql);
		return $query->result();
	}	

	function dataPegawaiById($id)
	{
		$sql = "SELECT * FROM adm_pegawai WHERE ID = ?";
		$query = $this->db->query($sql, array($id));
		return $query->row_array();
	}

	function tambahPegawai($value){
		$insert = $this->db->insert('adm_pegawai', $value);
		return $insert;
	}

	function ubahPegawai($id,$value)
	{
		$this->db->where('ID', $id);
		$this->db->update('adm_pegawai',$value);
	}

	function hapusPegawai($id)
	{
		$this->db->where('ID', $id);
      	$this->db->delete('adm_pegawai'); 
	}
}
?>