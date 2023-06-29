<?php defined('BASEPATH') or exit('No direct script access allowed');
class Geografi_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getProvinsi(){
		$sql = "SELECT kodepos_provinsi FROM geografi GROUP BY kodepos_provinsi ORDER BY kodepos_provinsi ASC";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getKotaKabByProvinsi($provinsi){
		$sql = "SELECT kodepos_kabupaten FROM geografi WHERE kodepos_provinsi = ? GROUP BY kodepos_kabupaten ORDER BY kodepos_kabupaten ASC";
		$query = $this->db->query($sql, array($provinsi));
		return $query->result();
	}

	function getKecamatanByKotaKab($kota_kab){
		$sql = "SELECT kodepos_kecamatan, kodepos_jenis FROM geografi WHERE kodepos_kabupaten = ? GROUP BY kodepos_kecamatan ORDER BY kodepos_jenis, kodepos_kecamatan ASC";
		$query = $this->db->query($sql, array($kota_kab));
		return $query->result();
	}

	function getKelurahanByKecamatan($kecamatan){
		$sql = "SELECT kodepos_kelurahan, kodepos_kode FROM geografi WHERE kodepos_kecamatan = ? ORDER BY kodepos_kode ASC";
		$query = $this->db->query($sql, array($kecamatan));
		return $query->result();	
	}

	function getKodeposByKelurahan($kelurahan, $kecamatan){
		$sql = "SELECT kodepos_kelurahan, kodepos_kode FROM geografi WHERE kodepos_kelurahan = ? AND kodepos_kecamatan = ? ORDER BY kodepos_kode ASC";
		$query = $this->db->query($sql, array($kelurahan, $kecamatan));
		return $query->result();	
	}

}