<?php defined('BASEPATH') or exit('No direct script access allowed');

class Inventory_m extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}

	function getReportByDepartement($bulan, $tahun)
	{
		$sql = "
			SELECT
				SUM(a.SUBTOTAL) AS INVENTORY,
				a.ID_DEPARTEMEN_BARANG,
				a.DEPARTEMEN_BARANG,
				a.BULAN,
				a.TAHUN
			FROM(
				SELECT
					a.ID_BARANG,
					a.NAMA_BARANG,
					a.SUBTOTAL,
					b.ID_DEPARTEMEN_BARANG,
					b.DEPARTEMEN_BARANG,
					MONTH(a.CREATE_DATE) AS BULAN,
					YEAR(a.CREATE_DATE) AS TAHUN
				FROM kasir_detail_transaksi a
				JOIN (
					SELECT a.ID, a.ID_DEPARTEMEN_BARANG, b.NAMA AS DEPARTEMEN_BARANG FROM adm_barang a
					JOIN adm_departemen_barang b ON b.ID = a.ID_DEPARTEMEN_BARANG
				) b ON b.ID = a.ID_BARANG
			) a
			WHERE a.BULAN = ? AND a.TAHUN = ?
			GROUP BY a.ID_DEPARTEMEN_BARANG
		";
		$query = $this->db->query($sql, array($bulan,$tahun));
		return $query->result();
	}

}