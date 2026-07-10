import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import 'app/data/providers/api_provider.dart';
import 'app/data/services/auth_service.dart';
import 'core/theme/app_theme.dart';
import 'app/routes/app_pages.dart';

void main() async {
  await GetStorage.init();
  Get.put<ApiProvider>(ApiProvider(), permanent: true);
  Get.put<AuthService>(AuthService(), permanent: true);

  final auth = Get.find<AuthService>();
  String initialRoute;
  if (auth.isLoggedIn) {
    initialRoute = Routes.HOME;
  } else if (GetStorage().read('onboarding_done') == true) {
    initialRoute = Routes.LOGIN;
  } else {
    initialRoute = Routes.ONBOARDING;
  }

  runApp(
    GetMaterialApp(
      title: 'Netlabs',
      theme: NetlabsTheme.light,
      debugShowCheckedModeBanner: false,
      initialRoute: initialRoute,
      getPages: AppPages.routes,
    ),
  );
}
