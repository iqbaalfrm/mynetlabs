import 'package:get/get.dart';
import '../../data/providers/api_provider.dart';

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
      final s1 = (data['1'] as List)
          .map((e) => Map<String, dynamic>.from(e))
          .toList();
      final s2 = (data['2'] as List)
          .map((e) => Map<String, dynamic>.from(e))
          .toList();
      materiSemester1.value = s1;
      materiSemester2.value = s2;
    } catch (e) {
      print('Gagal memuat materi: $e');
    } finally {
      isLoading.value = false;
    }
  }
}
