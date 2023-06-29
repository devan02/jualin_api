<?php defined('BASEPATH') or exit('No direct script access allowed');
class Userpegawai_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function dataUserPegawai($keyword)
	{
		$where = "";

		if($keyword != ""){
			$where = "WHERE b.NAMA_LENGKAP LIKE '%$keyword%'";
		}

		$sql = "
			SELECT 
				a.*,
				b.NIP,
				b.NAMA_LENGKAP
			FROM adm_user a
			LEFT JOIN adm_pegawai b ON b.ID = a.ID_PEGAWAI
			$where
		";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function cekUserPegawaiById($id)
	{
		$sql = "SELECT * FROM adm_user WHERE ID = ?";
		$query = $this->db->query($sql,array($id));
		return $query->result();
	}

	function cekUserPegawaiByIdAndIdPegawai($id, $id_pegawai)
	{
		$sql = "SELECT * FROM adm_user WHERE ID = ? AND ID_PEGAWAI = ?";
		$query = $this->db->query($sql,array($id, $id_pegawai));
		return $query;
	}

	function cekUserPegawaiByIdPegawai($id_pegawai)
	{
		$sql = "SELECT * FROM adm_user WHERE ID_PEGAWAI = ?";
		$query = $this->db->query($sql,array($id_pegawai));
		return $query->result();
	}

	function cekPegawaiById($id)
	{
		$sql = "SELECT * FROM adm_pegawai WHERE ID = ?";
		$query = $this->db->query($sql,array($id));
		return $query;
	}

	function tambahUserPegawai($value){
		$insert = $this->db->insert('adm_user', $value);
		return $insert;
	}

	function ubahUserPegawai($id,$value)
	{
		$this->db->where('ID', $id);
		$this->db->update('adm_user',$value);
	}

	function hapusUserPegawai($id,$value)
	{
		$this->db->where('ID', $id);
		$this->db->update('adm_user',$value);
	}

}
?>