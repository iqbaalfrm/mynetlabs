import 'package:flutter/material.dart';

import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';

import 'app/data/providers/api_provider.dart';
import 'app/routes/app_pages.dart';

void main() async {
  await GetStorage.init();

  Get.put<ApiProvider>(ApiProvider(), permanent: true);

  final storage = GetStorage();
  String initialRoute;
  if (storage.read('token') != null) {
    initialRoute = Routes.HOME;
  } else if (storage.read('onboarding_done') == true) {
    initialRoute = Routes.LOGIN;
  } else {
    initialRoute = Routes.ONBOARDING;
  }

  runApp(
    GetMaterialApp(
      title: 'Netlabs',
      initialRoute: initialRoute,
      getPages: AppPages.routes,
    ),
  );
}
