import 'package:get/get.dart';

class MateriController extends GetxController {
  // Simulasi data praktikum Semester 1
  var materiSemester1 = [
    {
      "id": "m1",
      "nomor": 1,
      "judul": "Pengenalan Perangkat Keras Jaringan & Pembuatan Kabel UTP",
      "topik_count": 3,
      "waktu": "120 Menit",
      "progress": 1.0, // 100% (Selesai)
      "is_locked": false
    },
    {
      "id": "m2",
      "nomor": 2,
      "judul": "Konfigurasi IP Address Dasar & Peer-to-Peer Jaringan Windows",
      "topik_count": 4,
      "waktu": "90 Menit",
      "progress": 1.0,
      "is_locked": false
    },
    {
      "id": "m3",
      "nomor": 3,
      "judul": "Subnetting Jaringan Menggunakan Metode VLSM & CIDR",
      "topik_count": 5,
      "waktu": "150 Menit",
      "progress": 0.4, // 40% (Sedang jalan)
      "is_locked": false
    },
  ].obs;

  // Simulasi data praktikum Semester 2
  var materiSemester2 = [
    {
      "id": "m4",
      "nomor": 4,
      "judul": "Konfigurasi Routing Statis dan Default Route",
      "topik_count": 4,
      "waktu": "120 Menit",
      "progress": 0.0,
      "is_locked": true // Masih terkunci
    },
    {
      "id": "m5",
      "nomor": 5,
      "judul": "Setup Pengamanan Jaringan Menggunakan Access Control List (ACL)",
      "topik_count": 3,
      "waktu": "120 Menit",
      "progress": 0.0,
      "is_locked": true
    },
  ].obs;
}