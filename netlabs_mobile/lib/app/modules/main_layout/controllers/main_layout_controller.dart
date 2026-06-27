import 'package:get/get.dart';

class MainLayoutController extends GetxController {
  // State reaktif untuk mencatat indeks menu aktif
  var currentIndex = 0.obs;

  void changePage(int index) {
    currentIndex.value = index;
  }
}