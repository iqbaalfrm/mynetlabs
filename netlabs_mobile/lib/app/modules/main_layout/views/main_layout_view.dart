import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:netlabs_mobile/app/modules/chatbot/views/chatbot_view.dart';
import 'package:netlabs_mobile/app/modules/materi/views/materi_view.dart';
import 'package:netlabs_mobile/app/modules/profile/views/profile_view.dart';
import '../controllers/main_layout_controller.dart';
import '../../home/views/home_view.dart';

// Catatan: Sementara kita panggil Container kosong untuk Chatbot dan Profile 
// sebelum modul tersebut kita koding penuh.
class MainLayoutView extends GetView<MainLayoutController> {
  const MainLayoutView({super.key});

  @override
  Widget build(BuildContext context) {
    final List<Widget> pages = [
  const HomeView(),
  const MateriView(),
  const ChatbotView(), // Sambungkan ke sini[cite: 1]
  const ProfileView(),

    ];

    return Scaffold(
      body: Obx(() => pages[controller.currentIndex.value]),
      bottomNavigationBar: Obx(
        () => BottomNavigationBar(
          currentIndex: controller.currentIndex.value,
          onTap: controller.changePage,
          type: BottomNavigationBarType.fixed,
          selectedItemColor: const Color(0xFF0D9488),
          unselectedItemColor: Colors.grey,
          showUnselectedLabels: true,
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