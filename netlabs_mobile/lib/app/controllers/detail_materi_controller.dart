import 'package:get/get.dart';
import '../../data/providers/api_provider.dart';
import 'home_controller.dart';
import 'materi_controller.dart';

class DetailMateriController extends GetxController {
  final ApiProvider _api = Get.find<ApiProvider>();

  late int nomorPertemuan;
  late String judulPertemuan;
  late int pertemuanId;

  var deskripsiPertemuan = ''.obs;
  var daftarTopik = <Map<String, dynamic>>[].obs;
  var isKuisEnabled = false.obs;
  var isLoading = false.obs;

  @override
  void onInit() {
    super.onInit();
    pertemuanId = Get.arguments['id'] ?? 0;
    nomorPertemuan = Get.arguments['nomor'] ?? 0;
    judulPertemuan = Get.arguments['judul'] ?? 'Detail Pertemuan';
    loadDetail();
  }

  void loadDetail() async {
    isLoading.value = true;
    try {
      final response = await _api.getDetailPertemuan(pertemuanId);
      final d = response.data['data'];
      deskripsiPertemuan.value = d['deskripsi'] ?? '';
      final list = (d['daftar_topik'] as List)
          .map((e) => Map<String, dynamic>.from(e))
          .toList();
      daftarTopik.value = list;
      checkKuisStatus();
    } catch (e) {
      print('Gagal memuat detail: $e');
    } finally {
      isLoading.value = false;
    }
  }

  void toggleCompleteTopik(int index) async {
    var topik = Map<String, dynamic>.from(daftarTopik[index]);
    topik['is_completed'] = !(topik['is_completed'] as bool);

    // Refresh list agar UI (Obx) ikut rebuild
    var newList = List<Map<String, dynamic>>.from(daftarTopik);
    newList[index] = topik;
    daftarTopik.value = newList;

    if (topik['is_completed'] == true) {
      try {
        await _api.tandaiTopikSelesai(pertemuanId, topik['id']);
        // Reload MateriController & HomeController agar progress update di mana-mana
        if (Get.isRegistered<MateriController>()) {
          Get.find<MateriController>().refreshMateri();
        }
        if (Get.isRegistered<HomeController>()) {
          Get.find<HomeController>().loadStatistik();
          Get.find<HomeController>().loadPertemuan();
        }
      } catch (e) {
        print('Gagal menyimpan progress: $e');
      }
    }

    checkKuisStatus();
  }

  void checkKuisStatus() {
    if (daftarTopik.isEmpty) {
      isKuisEnabled.value = false;
      return;
    }
    bool allDone = daftarTopik.every((t) => t['is_completed'] == true);
    isKuisEnabled.value = allDone;
  }
}
