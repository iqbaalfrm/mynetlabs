import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../../data/providers/api_provider.dart';

class ProfileController extends GetxController {
  final storage = GetStorage();
  final ApiProvider _api = Get.find<ApiProvider>();

  var nis = ''.obs;
  var nama = ''.obs;
  var kelas = ''.obs;
  var sekolah = 'SMK Bhakti Praja Dukuhwaru'.obs;

  var totalChatKeAI = 0.obs;
  var rataRataNilai = 0.0.obs;
  var totalPertemuanSelesai = 0.obs;
  var totalPertemuan = 0.obs;
  var isLoading = false.obs;

  @override
  void onInit() {
    super.onInit();
    nis.value = storage.read('nis') ?? '-';
    nama.value = storage.read('nama') ?? 'Siswa';
    kelas.value = storage.read('kelas') ?? '-';
    loadStatistik();
  }

  void loadStatistik() async {
    isLoading.value = true;
    try {
      final response = await _api.getStatistikSiswa();
      final data = response.data['data'];
      final profil = data['profil'];
      final stat = data['statistik'];

      nis.value = profil['nis'];
      nama.value = profil['nama'];
      kelas.value = profil['kelas'] ?? '-';

      totalChatKeAI.value = stat['total_chat_ai'];
      rataRataNilai.value = (stat['rata_rata_nilai'] as num).toDouble();
      totalPertemuanSelesai.value = stat['total_pertemuan_selesai'];
      totalPertemuan.value = stat['total_pertemuan'];
    } catch (e) {
      print('Gagal memuat statistik: $e');
    } finally {
      isLoading.value = false;
    }
  }

  void logout() {
    _api.logout().catchError((_) {});
    storage.remove('token');
    storage.remove('nama');
    storage.remove('kelas');
    storage.remove('role');
    Get.offAllNamed('/login');
  }
}
