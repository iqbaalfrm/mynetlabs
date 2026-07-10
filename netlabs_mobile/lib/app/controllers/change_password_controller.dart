import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../data/providers/api_provider.dart';

class ChangePasswordController extends GetxController {
  final ApiProvider _api = Get.find<ApiProvider>();
  final pwLamaCtrl = TextEditingController();
  final pwBaruCtrl = TextEditingController();
  final pwKonfirmCtrl = TextEditingController();
  var isLoading = false.obs;
  var errorMsg = ''.obs;

  @override
  void onClose() {
    pwLamaCtrl.dispose();
    pwBaruCtrl.dispose();
    pwKonfirmCtrl.dispose();
    super.onClose();
  }

  Future<void> submit() async {
    errorMsg.value = '';
    if (pwBaruCtrl.text != pwKonfirmCtrl.text) {
      errorMsg.value = 'Password baru tidak cocok dengan konfirmasi.';
      return;
    }
    if (pwBaruCtrl.text.length < 6) {
      errorMsg.value = 'Password baru minimal 6 karakter.';
      return;
    }
    isLoading.value = true;
    try {
      await _api.changePassword(pwLamaCtrl.text, pwBaruCtrl.text);
      GetStorage().write('password_is_default', false);
      Get.back();
      Get.snackbar('Sukses', 'Password berhasil diubah!',
          snackPosition: SnackPosition.BOTTOM,
          backgroundColor: Colors.green,
          colorText: Colors.white);
    } on DioException catch (e) {
      final msg = e.response?.data['message'] ?? 'Gagal mengubah password.';
      errorMsg.value = msg.toString();
    } catch (_) {
      errorMsg.value = 'Terjadi kesalahan, coba lagi.';
    }
    isLoading.value = false;
  }
}