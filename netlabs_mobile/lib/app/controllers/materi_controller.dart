import 'package:get/get.dart';
import '../data/providers/api_provider.dart';

class MateriController extends GetxController {
  final ApiProvider _api = Get.find<ApiProvider>();

  var materiSemester1 = <Map<String, dynamic>>[].obs;
  var materiSemester2 = <Map<String, dynamic>>[].obs;
  var isLoading = false.obs;

  @override
  void onInit() {
    super.onInit();
    loadMateri();
  }

  /// Reload data materi (dipanggil saat balik dari detail_materi)
  void refreshMateri() {
    loadMateri();
  }

  void loadMateri() async {
    isLoading.value = true;
    try {
      final response = await _api.getPertemuan();
      final data = response.data['data'];

      List<Map<String, dynamic>> allMateri = [];
      if (data is List) {
        allMateri = data.map((e) => Map<String, dynamic>.from(e)).toList();
        materiSemester1.value = allMateri
            .where((e) => e['semester'].toString() == '1')
            .toList();
        materiSemester2.value = allMateri
            .where((e) => e['semester'].toString() == '2')
            .toList();
      } else if (data is Map) {
        final s1Raw = data['1'] ?? data[1] ?? [];
        final s2Raw = data['2'] ?? data[2] ?? [];
        materiSemester1.value =
            (s1Raw as List).map((e) => Map<String, dynamic>.from(e)).toList();
        materiSemester2.value =
            (s2Raw as List).map((e) => Map<String, dynamic>.from(e)).toList();
      }
    } catch (e) {
      print('Gagal memuat materi: $e');
    } finally {
      isLoading.value = false;
    }
  }
}
