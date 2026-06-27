import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../../data/providers/api_provider.dart';
import '../../../routes/app_pages.dart';

class LoginController extends GetxController {
  final loginFormKey = GlobalKey<FormState>();
  final storage = GetStorage();
  final ApiProvider _api = Get.find<ApiProvider>();

  late TextEditingController nisController;
  late TextEditingController passwordController;

  var isLoading = false.obs;
  var isPasswordObscured = true.obs;

  @override
  void onInit() {
    super.onInit();
    nisController = TextEditingController();
    passwordController = TextEditingController();

    if (storage.read('token') != null) {
      Future.delayed(Duration.zero, () => Get.offAllNamed(Routes.HOME));
    }
  }

  @override
  void onClose() {
    nisController.dispose();
    passwordController.dispose();
    super.onClose();
  }

  void togglePasswordVisibility() {
    isPasswordObscured.value = !isPasswordObscured.value;
  }

  void login() async {
    if (loginFormKey.currentState!.validate()) {
      isLoading.value = true;

      try {
        final response = await _api.login(
          nisController.text.trim(),
          passwordController.text,
        );

        final data = response.data;
        final token = data['token'] as String;
        final user = data['user'] as Map<String, dynamic>;

        await storage.write('token', token);
        await storage.write('nama', user['nama'] ?? '');
        await storage.write('kelas', user['kelas'] ?? '');
        await storage.write('role', user['role'] ?? 'siswa');

        isLoading.value = false;

        Get.snackbar(
          'Sukses',
          'Selamat datang di Netlabs, ${user['nama']}!',
          snackPosition: SnackPosition.BOTTOM,
          backgroundColor: Colors.green,
          colorText: Colors.white,
        );

        Get.offAllNamed(Routes.HOME);
      } on DioException catch (e) {
        isLoading.value = false;
        String pesanError = 'NIS atau Kata Sandi salah.';
        if (e.response?.data != null && e.response!.data['message'] != null) {
          pesanError = e.response!.data['message'];
        }
        Get.snackbar(
          'Gagal Login',
          pesanError,
          snackPosition: SnackPosition.BOTTOM,
          backgroundColor: Colors.red,
          colorText: Colors.white,
        );
      } catch (e) {
        isLoading.value = false;
        Get.snackbar(
          'Gagal Login',
          'Terjadi kesalahan: ${e.toString()}',
          snackPosition: SnackPosition.BOTTOM,
          backgroundColor: Colors.red,
          colorText: Colors.white,
        );
      }
    }
  }
}
