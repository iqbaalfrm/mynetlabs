import 'package:get/get.dart';

import '../bindings/login_binding.dart';
import '../views/auth/login_view.dart';
import '../bindings/chatbot_binding.dart';
import '../views/chatbot/chatbot_view.dart';
import '../bindings/detail_materi_binding.dart';
import '../views/modul/detail_materi_view.dart';
import '../bindings/home_binding.dart';
import '../bindings/materi_binding.dart';
import '../views/modul/materi_view.dart';
import '../bindings/onboarding_binding.dart';
import '../views/auth/onboarding_view.dart';
import '../bindings/profile_binding.dart';
import '../views/profil/profile_view.dart';
import '../bindings/change_password_binding.dart';
import '../views/profil/change_password_view.dart';
import '../bindings/quiz_binding.dart';
import '../views/kuis/quiz_view.dart';
import '../views/home/main_layout_view.dart';

part 'app_routes.dart';

class AppPages {
  AppPages._();

  static const INITIAL = Routes.ONBOARDING;

  static final routes = [
    GetPage(
      name: _Paths.ONBOARDING,
      page: () => const OnboardingView(),
      binding: OnboardingBinding(),
    ),
    GetPage(
      name: _Paths.LOGIN,
      page: () => const LoginView(),
      binding: LoginBinding(),
    ),
    GetPage(
      name: _Paths.HOME,
      page: () => const MainLayoutView(),
      binding: HomeBinding(),
    ),
    GetPage(
      name: _Paths.MATERI,
      page: () => const MateriView(),
      binding: MateriBinding(),
    ),
    GetPage(
      name: _Paths.CHATBOT,
      page: () => const ChatbotView(),
      binding: ChatbotBinding(),
    ),
    GetPage(
      name: _Paths.PROFILE,
      page: () => const ProfileView(),
      binding: ProfileBinding(),
    ),
    GetPage(
      name: _Paths.DETAIL_MATERI,
      page: () => const DetailMateriView(),
      binding: DetailMateriBinding(),
    ),
    GetPage(
      name: _Paths.CHANGE_PASSWORD,
      page: () => const ChangePasswordView(),
      binding: ChangePasswordBinding(),
    ),
    GetPage(
      name: _Paths.QUIZ,
      page: () => const QuizView(),
      binding: QuizBinding(),
    ),
  ];
}
