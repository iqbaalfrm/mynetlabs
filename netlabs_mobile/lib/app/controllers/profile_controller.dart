import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import 'package:image_picker/image_picker.dart';
import '../data/providers/api_provider.dart';
import '../data/services/auth_service.dart';
import '../routes/app_pages.dart';

class ProfileController extends GetxController {
  final _auth = Get.find<AuthService>();
  final ApiProvider _api = Get.find<ApiProvider>();

  var nis = ''.obs;
  var nama = ''.obs;
  var kelas = ''.obs;
  var sekolah = 'SMK Astrindo Tegal'.obs;
  var fotoProfilUrl = RxnString();
  var isUploading = false.obs;

  var totalChatKeAI = 0.obs;
  var rataRataNilai = 0.0.obs;
  var totalPertemuanSelesai = 0.obs;
  var totalPertemuan = 0.obs;
  var isLoading = false.obs;

  @override
  void onInit() {
    super.onInit();
    nis.value = GetStorage().read('nis') ?? '-';
    nama.value = GetStorage().read('nama') ?? 'Siswa';
    kelas.value = GetStorage().read('kelas') ?? '-';
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
      fotoProfilUrl.value = profil['foto_profil_url'];

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

  void logout() async {
    try {
      await _api.logout();
    } catch (_) {}
    await _auth.clearSession();
    Get.offAllNamed('/login');
  }

  void gantiPassword() => Get.toNamed(Routes.CHANGE_PASSWORD);

  Future<void> gantiFotoProfil() async {
    try {
      // 1. Pilih gambar dari galeri
      final ImagePicker picker = ImagePicker();
      final XFile? image = await picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 800,
        maxHeight: 800,
        imageQuality: 85,
      );

      if (image == null) return; // User membatalkan pemilihan foto

      // 2. Upload gambar
      isUploading.value = true;
      final response = await _api.updateFotoProfil(image.path);

      if (response.statusCode == 200 && response.data['success']) {
        // 3. Perbarui state URL foto secara real-time
        fotoProfilUrl.value = response.data['foto_profil_url'];
        Get.snackbar('Sukses', 'Foto profil berhasil diperbarui',
            snackPosition: SnackPosition.TOP,
            backgroundColor: Get.theme.colorScheme.primary.withAlpha(200),
            colorText: Get.theme.colorScheme.onPrimary);
      } else {
        Get.snackbar('Gagal', 'Gagal mengunggah foto profil');
      }
    } catch (e) {
      print('Gagal update foto: $e');
      Get.snackbar('Error', 'Terjadi kesalahan saat mengunggah foto');
    } finally {
      isUploading.value = false;
    }
  }
}
