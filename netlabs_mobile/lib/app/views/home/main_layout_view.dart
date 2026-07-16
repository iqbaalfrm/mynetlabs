import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:netlabs_mobile/app/views/chatbot/chatbot_view.dart';
import 'package:netlabs_mobile/app/views/modul/materi_view.dart';
import 'package:netlabs_mobile/app/views/profil/profile_view.dart';
import '../../../core/theme/app_theme.dart';
import '../../controllers/main_layout_controller.dart';
import 'home_view.dart';

// Catatan: Sementara kita panggil Container kosong untuk Chatbot dan Profile 
// sebelum modul tersebut kita koding penuh.
class MainLayoutView extends GetView<MainLayoutController> {
  const MainLayoutView({super.key});

  @override
  Widget build(BuildContext context) {
    final List<Widget> pages = [
      HomeView(),
      MateriView(),
      ChatbotView(), // Sambungkan ke sini
      ProfileView(),
    ];

    return Scaffold(
      body: Obx(() => pages[controller.currentIndex.value]),
      bottomNavigationBar: Obx(
        () => BottomNavigationBar(
          currentIndex: controller.currentIndex.value,
          onTap: controller.changePage,
          type: BottomNavigationBarType.fixed,
          selectedItemColor: NetlabsTheme.primary,
          unselectedItemColor: Colors.grey,
          showUnselectedLabels: true,
          backgroundColor: Colors.white,
          elevation: 8,
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.dashboard_rounded),
              label: 'Home',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.book_rounded),
              label: 'Materi',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.blur_on_rounded),
              label: 'Chatbot',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.person_rounded),
              label: 'Profil',
            ),
          ],
        ),
      ),
    );
  }
}