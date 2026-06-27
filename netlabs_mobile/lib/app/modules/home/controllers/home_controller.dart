import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../../routes/app_pages.dart';

class HomeController extends GetxController {
  final storage = GetStorage();

  // Data Simulasi Profil Siswa (Nanti diambil dari API Laravel)
  var studentName = "Moch Iqbal Firmansyah".obs; 
  var studentClass = "XI TKJ 1".obs;

  // Data Statistik Belajar (Sesuai Requirement PRD)
  var totalPertemuan = "4/12".obs;
  var rataRataNilai = "85.5".obs;
  var streakBelajar = "5 Hari".obs;

  // Data Simulasi Pertemuan yang Sedang Aktif (Horizontal Scroll)
  var pertemuanAktif = [
    {
      "nomor": 5,
      "judul": "Konfigurasi Routing Statis pada Router Cisco",
      "topik": "4 Topik",
      "progress": 0.5, // 50%
    },
    {
      "nomor": 6,
      "judul": "Setup DHCP Server dan Client di MikroTik",
      "topik": "3 Topik",
      "progress": 0.2, // 20%
    },
  ].obs;

  // Pertanyaan terakhir ke AI Tutor untuk ringkasan di beranda
  var terakhirTanyaAI = "Bagaimana cara mengatasi RTO saat ping gateway?".obs;
  var waktuTanyaAI = "10 menit yang lalu".obs;

  // Logika Logout
  void logout() {
    storage.remove('token'); // Hapus token dari HP
    Get.offAllNamed(Routes.LOGIN); // Kembalikan ke halaman login
  }
}