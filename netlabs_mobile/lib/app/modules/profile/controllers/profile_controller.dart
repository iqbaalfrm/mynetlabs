import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';

class ProfileController extends GetxController {
  final storage = GetStorage();

  // Data Simulasi Biodata Siswa (Nanti ditarik dari DB MySQL via Laravel)
  var nis = "22041001".obs;
  var nama = "Moch Iqbal Firmansyah".obs;
  var kelas = "XI TKJ 1".obs;
  var sekolah = "SMK Bhakti Praja Dukuhwaru".obs;

  // Data Statistik Belajar Siswa[cite: 1]
  var totalChatKeAI = 42.obs;
  var rataRataNilai = 85.5.obs;
  var totalPertemuanSelesai = 4.obs;
  var totalPertemuan = 12.obs;

  // Fungsi Logout[cite: 1]
  void logout() {
    storage.remove('token'); // Menghapus token JWT yang tersimpan[cite: 1]
    Get.offAllNamed('/login'); // Tendang kembali ke halaman Login
  }
}