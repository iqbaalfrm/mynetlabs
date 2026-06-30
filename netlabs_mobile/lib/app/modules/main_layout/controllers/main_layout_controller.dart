import 'package:get/get.dart';
import '../../chatbot/controllers/chatbot_controller.dart';
import '../../materi/controllers/materi_controller.dart';
import '../../profile/controllers/profile_controller.dart';

class MainLayoutController extends GetxController {
  var currentIndex = 0.obs;

  @override
  void onInit() {
    super.onInit();
    // Pre-inject controllers karena views dipakai langsung tanpa routing
    Get.put(ChatbotController(), permanent: true);
    Get.put(MateriController(), permanent: true);
    Get.put(ProfileController(), permanent: true);
  }

  void changePage(int index) {
    currentIndex.value = index;
  }
}
