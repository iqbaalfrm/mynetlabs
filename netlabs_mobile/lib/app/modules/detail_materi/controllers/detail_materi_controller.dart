import 'package:get/get.dart';

class DetailMateriController extends GetxController {
  // Ambil argumen data pertemuan yang dikirim dari halaman sebelumnya
  late int nomorPertemuan;
  late String judulPertemuan;

  // Data Simulasi Konten Materi sesuai standar TKJ
  var deskripsiPertemuan = "Pertemuan ini membahas tentang dasar-dasar pembagian segmentasi jaringan menggunakan metode Variable Length Subnet Mask (VLSM) dan Classless Inter-Domain Routing (CIDR) untuk efisiensi alokasi IP Address.";
  
  var daftarTopik = [
    {
      "id": "t1",
      "judul": "Konsep Dasar Classless Addressing (CIDR)",
      "isi": "CIDR (Classless Inter-Domain Routing) merupakan sebuah metode pengalamatan IP Address tanpa mengenal kelas (Classless). Metode ini menggunakan netmask atau prefix (misal: /25, /26) untuk menentukan porsi network ID dan host ID secara lebih fleksibel dibandingkan sistem kelas klasik (Classful).",
      "is_completed": true,
    },
    {
      "id": "t2",
      "judul": "Mekanisme Perhitungan Subnetting VLSM",
      "isi": "VLSM (Variable Length Subnet Mask) adalah teknik pemecahan subnet yang disesuaikan dengan kebutuhan jumlah host di setiap jaringan. Alokasi IP dimulai dari kebutuhan host terbesar terlebih dahulu menuju host terkecil untuk menghindari pemborosan alokasi IP Address.",
      "is_completed": false,
    },
    {
      "id": "t3",
      "judul": "Studi Kasus Desain Jaringan Lab Komputer SMK",
      "isi": "Misalkan Lab TKJ membutuhkan 60 host, Lab Akuntansi 30 host, dan Lab Perkantoran 14 host. Dengan VLSM, kita memecah IP Network utama menggunakan prefix /26 (64 IP) untuk Lab TKJ, /27 (32 IP) untuk Lab Akuntansi, dan /28 (16 IP) untuk Lab Perkantoran.",
      "is_completed": false,
    },
  ].obs;

  // State Reaktif untuk mendeteksi apakah tombol kuis boleh aktif
  var isKuisEnabled = false.obs;

  @override
  void onInit() {
    super.onInit();
    // Menangkap data transfer dari halaman MateriView
    nomorPertemuan = Get.arguments['nomor'] ?? 0;
    judulPertemuan = Get.arguments['judul'] ?? "Detail Pertemuan";
    
    checkKuisStatus();
  }

  // Fungsi untuk menandai topik sebagai "Sudah Dibaca"
  void toggleCompleteTopik(int index) {
    var topik = daftarTopik[index];
    // Balikkan status completed
    topik['is_completed'] = !(topik['is_completed'] as bool);
    daftarTopik[index] = topik; // Trigger update RxList

    checkKuisStatus();
  }

  // Validasi apakah semua topik sudah dicentang selesai
  void checkKuisStatus() {
    bool allDone = daftarTopik.every((topik) => topik['is_completed'] == true);
    isKuisEnabled.value = allDone;
  }
}