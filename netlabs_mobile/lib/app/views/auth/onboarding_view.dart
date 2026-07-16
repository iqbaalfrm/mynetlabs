import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../routes/app_pages.dart';
import '../../../core/theme/app_theme.dart';

class OnboardingView extends StatefulWidget {
  const OnboardingView({super.key});
  @override
  State<OnboardingView> createState() => _OnboardingViewState();
}

class _OnboardingViewState extends State<OnboardingView> {
  final PageController _pageController = PageController();
  int _currentPage = 0;

  final List<Map<String, dynamic>> _slides = [
    {
      'icon': Icons.router_rounded,
      'title': 'Selamat Datang di Netlabs',
      'desc': 'Platform LMS praktikum Jaringan Komputer. Belajar subnetting, routing, VLAN, dan lainnya jadi lebih mudah & interaktif.',
      'color': NetlabsTheme.primary,
    },
    {
      'icon': Icons.menu_book_rounded,
      'title': 'Materi & Kuis Interaktif',
      'desc': 'Pelajari modul per pertemuan, tandai topik yang selesai, lalu kerjakan kuis. Dapatkan rekomendasi AI otomatis!',
      'color': NetlabsTheme.primaryDark,
    },
    {
      'icon': Icons.auto_awesome_rounded,
      'title': 'AI Tutor Siap Membantu',
      'desc': 'Bingung dengan materi? Tanya langsung ke AI Tutor 24/7. Dapatkan penjelasan tentang VLSM, CIDR, DHCP, dan lainnya.',
      'color': NetlabsTheme.accent,
    },
  ];

  void _nextPage() {
    if (_currentPage < _slides.length - 1) {
      _pageController.nextPage(duration: const Duration(milliseconds: 300), curve: Curves.easeInOut);
    } else {
      _finish();
    }
  }

  void _finish() {
    GetStorage().write('onboarding_done', true);
    Get.offAllNamed(Routes.LOGIN);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            Align(
              alignment: Alignment.topRight,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: TextButton(
                  onPressed: _finish,
                  child: Text(_currentPage == _slides.length - 1 ? '' : 'Lewati', style: const TextStyle(color: NetlabsTheme.textSecondary, fontWeight: FontWeight.w600)),
                ),
              ),
            ),
            Expanded(
              child: PageView.builder(
                controller: _pageController,
                itemCount: _slides.length,
                onPageChanged: (i) => setState(() => _currentPage = i),
                itemBuilder: (context, index) {
                  final s = _slides[index];
                  return Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 40),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          width: 140, height: 140,
                          decoration: BoxDecoration(color: (s['color'] as Color).withAlpha(25), shape: BoxShape.circle),
                          child: Icon(s['icon'] as IconData, size: 70, color: s['color'] as Color),
                        ),
                        const SizedBox(height: 40),
                        Text(s['title'] as String, textAlign: TextAlign.center, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: NetlabsTheme.primaryDark)),
                        const SizedBox(height: 16),
                        Text(s['desc'] as String, textAlign: TextAlign.center, style: TextStyle(fontSize: 15, color: Colors.grey.shade600, height: 1.6)),
                      ],
                    ),
                  );
                },
              ),
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(_slides.length, (i) => AnimatedContainer(
                duration: const Duration(milliseconds: 300),
                margin: const EdgeInsets.only(right: 8),
                height: 8, width: _currentPage == i ? 24 : 8,
                decoration: BoxDecoration(color: _currentPage == i ? NetlabsTheme.primary : Colors.grey.shade300, borderRadius: BorderRadius.circular(4)),
              )),
            ),
            const SizedBox(height: 40),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 24),
              child: SizedBox(
                width: double.infinity, height: 52,
                child: ElevatedButton(
                  onPressed: _nextPage,
                  style: ElevatedButton.styleFrom(backgroundColor: NetlabsTheme.primary, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)), elevation: 0),
                  child: Text(_currentPage == _slides.length - 1 ? 'Mulai Belajar' : 'Lanjut', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
