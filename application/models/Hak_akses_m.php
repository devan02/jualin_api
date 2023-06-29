<?php defined('BASEPATH') or exit('No direct script access allowed');

class Hak_akses_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getData($id, $status){
		if($id != ""){
			$sql = "SELECT * FROM adm_menu_1 WHERE ID = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}else{
			$where = "";
			if($status != ""){
				$where = "WHERE STATUS = '$status'";
			}

			$sql = "SELECT * FROM adm_menu_1 $where";
			$query = $this->db->query($sql);
			return $query->result();
		}
	}

	function getMenuSatu($id, $status){
		if($id != ""){
			$sql = "SELECT * FROM adm_menu_1 WHERE ID = ?";
			$query = $this->db->query($sql, array($id));
			return $query->result();
		}else{
			$where = "";
			if($status != ""){
				$where = "WHERE STATUS = '$status'";
			}

			$sql = "SELECT * FROM adm_menu_1 $where";
			$query = $this->db->query($sql);
			return $query->result();
		}
	}

	function getDataAfterInsert()
	{
		$sql = "SELECT * FROM adm_menu_1 ORDER BY ID DESC LIMIT 1";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function getDataByFilter($nama, $urut)
	{
		$where = "";
		if($nama != ""){
			$where = "WHERE NAMA_MENU = '$nama'";
		}
		if($urut != ""){
			$where = "WHERE URUT = '$urut'";
		}

		$sql = "SELECT * FROM adm_menu_1 $where";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getMenuDuaByIdMenuSatu($id_menu1)
	{
		$sql = "SELECT * FROM adm_menu_2 WHERE ID_MENU_1 = ?";
		$query = $this->db->query($sql, array($id_menu1));
		return $query->result();
	}

	function postData($value)
	{
		$insert = $this->db->insert('adm_menu_1', $value);
		return $insert;
	}

	// MENU 2
	function getMenuDua($id, $id_menu1, $status)
	{
		if($id != ""){
			$sql = "SELECT * FROM adm_menu_2 WHERE ID = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}else{
			$where = "1 = 1";
			if($status != ""){
				$where = $where." AND STATUS = '$status'";
			}

			if($id_menu1 != ""){
				$where = $where." AND ID_MENU_1 = '$id_menu1'";
			}

			$sql = "SELECT * FROM adm_menu_2 WHERE $where";
			$query = $this->db->query($sql);
			return $query->result();
		}
	}

	function getMenuDuaByFilter($nama, $urut)
	{
		$where = "";
		if($nama != ""){
			$where = "WHERE NAMA_MENU = '$nama'";
		}
		if($urut != ""){
			$where = "WHERE URUT = '$urut'";
		}

		$sql = "SELECT * FROM adm_menu_2 $where";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getMenuDuaAfterInsert()
	{
		$sql = "SELECT * FROM adm_menu_2 ORDER BY ID DESC LIMIT 1";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	function postMenuDua($value)
	{
		$insert = $this->db->insert('adm_menu_2', $value);
		return $insert;
	}

	// HAK AKSES
	function getUser($id)
	{
		$sql = "SELECT * FROM adm_user WHERE ID = ?";
		$query = $this->db->query($sql, array($id));
		return $query->row_array();		
	}

	function getHakAkses()
	{
		$sql = "
			SELECT 
				a.*,
				b.ID_PEGAWAI,
				b.USERNAME,
				b.NAMA_LENGKAP,
				b.`LEVEL`
			FROM adm_hak_akses a
			LEFT JOIN (
				SELECT
					a.ID,
					a.ID_PEGAWAI,
					a.USERNAME,
					a.`LEVEL`,
					a.AKTIF,
					b.NAMA_LENGKAP
				FROM adm_user a
				LEFT JOIN adm_pegawai b ON b.ID = a.ID_PEGAWAI
			) b ON b.ID = a.ID_USER
		";
		$query = $this->db->query($sql);
		return $query->result();	
	}

	function getHakAksesUser($id_user)
	{
		$sql = "
			SELECT 
				a.*,
				b.ID_PEGAWAI,
				b.USERNAME,
				b.NAMA_LENGKAP,
				b.`LEVEL`
			FROM adm_hak_akses a
			LEFT JOIN (
				SELECT
					a.ID,
					a.ID_PEGAWAI,
					a.USERNAME,
					a.`LEVEL`,
					a.AKTIF,
					b.NAMA_LENGKAP
				FROM adm_user a
				LEFT JOIN adm_pegawai b ON b.ID = a.ID_PEGAWAI
			) b ON b.ID = a.ID_USER
			WHERE a.ID_USER = ?
		";
		$query = $this->db->query($sql, array($id_user));
		return $query->result();
	}

	function postHakAkses($value)
	{
		$insert = $this->db->insert('adm_hak_akses', $value);
		return $insert;
	}

}
?>