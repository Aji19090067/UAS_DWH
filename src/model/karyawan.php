<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
require_once 'database.php';

function add_karyawan($post){
	$db = new Database();
	$q = "INSERT INTO calon_karyawan(nama_karyawan, umur, ttl) VALUES ('$post[nama]','$post[umur]','$post[ttl]')";
	if($db->query($q)){
		header('Location: ../karyawan-list.php?status=calon');

		$db = new Database();
		$q = "INSERT INTO log(aksi) VALUES ('Admin Menambahkan Data Karyawan')";
		$db->query($q);
		header('Location: ../karyawan-list.php?status=calon');
	}

}
function edit_karyawan($post){
	$db = new Database();
	$q = "UPDATE calon_karyawan SET nama_karyawan='$post[nama]',umur='$post[umur]',ttl='$post[ttl]' WHERE id = '$post[id]'";
	$q = "INSERT INTO log(aksi) VALUES ('Admin Merubah Data Karyawan')";
	if($db->query($q)){
		header('Location: ../karyawan-list.php?status=calon');

		$db = new Database();
		$q = "INSERT INTO log(aksi) VALUES ('Admin Merubah Data Karyawan')";
		$db->query($q);
		header('Location: ../karyawan-list.php?status=calon');
	}
}
function delete_karyawan($id){
	$db = new Database();
	$q = "DELETE FROM calon_karyawan WHERE id='$id'";
	if($db->query($q)){
		header('Location: ../karyawan-list.php?status=calon');

		$db = new Database();
		$q = "INSERT INTO log(aksi) VALUES ('Admin Menghapus Data Karyawan')";
		$db->query($q);
		header('Location: ../karyawan-list.php?status=calon');
	}
}
function show_karyawan_tetap(){
	$db = new Database();
	$q = "SELECT * FROM karyawan_tetap";
	if($db->query($q)){
		return $db->fetch();

		$db = new Database();
		$q = "INSERT INTO log(aksi) VALUES ('Admin Melihat Data Karyawan Tetap')";
		$db->query($q);
	}
}
function show_history_karyawan_tetap(){
	$db = new Database();
	$q = "SELECT * FROM history_pemilihan_karyawan";
	if($db->query($q)){
		return $db->fetch();

		$db = new Database();
		$q = "INSERT INTO log(aksi) VALUES ('Admin Melihat History Pemilihan Karyawan')";
		$db->query($q);
	}
}
function show_karyawan($id = null,$join = [],$where =[]){
	$db = new Database();
	$q = "SELECT * FROM calon_karyawan";
	if($id != null){
		$q .= " WHERE id = '$id'";
	}
	if(count($where) > 0){
		$q .= " WHERE $where[0] = $where[1]";
	}
	if($db->query($q)){
		return $db->fetch();
	}

}
function set_karyawan_tetap(){
	$no_batch = 1;
	$db = new Database();
	$q = "UPDATE calon_karyawan INNER JOIN (SELECT * FROM hasil_akhir ORDER BY total DESC limit 5) hasil_akhir ON calon_karyawan.id = hasil_akhir.karyawan_id SET calon_karyawan.status = 1 WHERE calon_karyawan.status = 0";
	if($db->query($q)){
		$q = "SELECT batch_pengambilan FROM history_pemilihan_karyawan ORDER BY batch_pengambilan DESC LIMIT 1 ";
		if($db->query($q)){
			$batch_pengambilan_terakhir = $db->fetch();
			if(count( $batch_pengambilan_terakhir)>0){
				$no_batch = $batch_pengambilan_terakhir[0]->batch_pengambilan+1;
			}
		}
		$q = "INSERT INTO history_pemilihan_karyawan(nama_karyawan, umur, ttl,status,waktu_pemilihan,batch_pengambilan) SELECT nama_karyawan,umur,ttl,status,NOW() AS waktu_pemilihan,$no_batch AS batch_pengambilan FROM calon_karyawan";
		$db->query($q);
		$q = "INSERT INTO karyawan_tetap(nama_karyawan, umur, ttl) SELECT nama_karyawan,umur,ttl FROM calon_karyawan WHERE status = 1";
		$db->query($q);
		$q = "DELETE FROM calon_karyawan WHERE status = 1";
		$db->query($q);
		$q = "TRUNCATE hasil_akhir";
		$db->query($q);
		$q = "TRUNCATE data_kriteria";
		$db->query($q);
		$_SESSION['status'] = (object) ['status'=>'success','message'=>'5 Karyawan berhasil dijadikan karyawan tetap'];
		header('Location: ../karyawan-list.php?status=tetap');
	}
}
if(isset($_GET['f'])){
	switch ($_GET['f']){
		// case 'edit':
		// 	edit_karyawan($_GET['id']);
		// 	break;
		case 'delete':
			delete_karyawan($_GET['id']);
			break;
		default:return;
	}
}

if(isset($_POST['button'])){
	switch ($_POST['button']){
		case 'simpan':
			add_karyawan($_POST);
			break;
		case 'edit':
			edit_karyawan($_POST);
			break;
		case 'set':
			set_karyawan_tetap();
			break;
		default:echo 'No route available';
	}
}