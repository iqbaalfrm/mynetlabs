import 'package:flutter/material.dart';

import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';

import 'app/data/providers/api_provider.dart';
import 'app/routes/app_pages.dart';

void main() async {
  // Inisialisasi penyimpanan lokal sebelum menjalankan aplikasi
  await GetStorage.init();

  // Daftarkan ApiProvider sebagai singleton global agar bisa diakses di semua controller
  Get.put<ApiProvider>(ApiProvider(), permanent: true);

  runApp(
    GetMaterialApp(
      title: "Netlabs",
      initialRoute: AppPages.INITIAL,
      getPages: AppPages.routes,
    ),
  );
}
