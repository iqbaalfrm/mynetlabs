import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../core/theme/app_theme.dart';
import '../../controllers/detail_materi_controller.dart';
import '../../../routes/app_pages.dart';

class DetailMateriView extends GetView<DetailMateriController> {
  const DetailMateriView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: NetlabsTheme.surface,
      appBar: AppBar(
        title: Text(
          "Bab ${controller.nomorPertemuan}",
          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: NetlabsTheme.dark),
        ),
        centerTitle: true,
        backgroundColor: NetlabsTheme.surface,
        elevation: 0,
        iconTheme: const IconThemeData(color: NetlabsTheme.dark),
        actions: [
          Obx(() => controller.pdfUrl.value.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.download_for_offline_rounded, color: NetlabsTheme.primary, size: 24),
                  tooltip: 'Unduh Modul PDF',
                  onPressed: () => controller.unduhPdfMateri(),
                )
              : const SizedBox.shrink()),
        ],
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 20.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Elegant Header
            Text(
              controller.judulPertemuan,
              style: const TextStyle(
                fontSize: 28,
                fontWeight: FontWeight.w900,
                color: NetlabsTheme.dark,
                height: 1.2,
                letterSpacing: -0.5,
              ),
            ),
            const SizedBox(height: 16),
            Obx(() => Text(
                  controller.deskripsiPertemuan.value,
                  style: const TextStyle(
                    fontSize: 15,
                    color: NetlabsTheme.textSecondary,
                    height: 1.5,
                  ),
                )),
            const SizedBox(height: 40),
            
            // Materi Content Card
            Obx(() {
              if (controller.isLoading.value) {
                return const Center(
                  child: Padding(
                    padding: EdgeInsets.all(40),
                    child: CircularProgressIndicator(color: NetlabsTheme.primary),
                  ),
                );
              }
              
              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: double.infinity,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF0F172A).withOpacity(0.02),
                          blurRadius: 16,
                          offset: const Offset(0, 8),
                        ),
                      ],
                    ),
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: controller.isiMateri.value.trim().isEmpty
                          ? const Center(
                              child: Padding(
                                padding: EdgeInsets.all(24.0),
                                child: Text(
                                  "Materi belum diunggah oleh guru.",
                                  style: TextStyle(
                                    color: NetlabsTheme.textSecondary,
                                    fontSize: 14.5,
                                  ),
                                ),
                              ),
                            )
                          : _buildFormattedText(controller.isiMateri.value),
                    ),
                  ),
                  const SizedBox(height: 24),
                  
                  // Status Selesai Dibaca Banner / Button
                  if (controller.isKuisEnabled.value)
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: NetlabsTheme.success.withAlpha(20),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: NetlabsTheme.success.withAlpha(50), width: 0.5),
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.check_circle_rounded, color: NetlabsTheme.success, size: 20),
                          SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              "Anda telah selesai membaca materi ini. Kuis evaluasi sekarang aktif!",
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: NetlabsTheme.success,
                              ),
                            ),
                          ),
                        ],
                      ),
                    )
                  else
                    ElevatedButton.icon(
                      onPressed: () => controller.tandaiSelesai(),
                      icon: const Icon(Icons.check_circle_outline_rounded, size: 20, color: Colors.white),
                      label: const Text(
                        "Tandai Selesai Dibaca",
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: Colors.white),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: NetlabsTheme.success,
                        minimumSize: const Size(double.infinity, 52),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                        elevation: 0,
                      ),
                    ),
                  const SizedBox(height: 20),
                  
                  // Evaluation Button
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 300),
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: controller.isKuisEnabled.value
                          ? [BoxShadow(color: NetlabsTheme.primary.withAlpha(50), blurRadius: 20, offset: const Offset(0, 8))]
                          : [],
                    ),
                    child: ElevatedButton(
                      onPressed: controller.isKuisEnabled.value
                          ? () {
                              Get.toNamed(Routes.QUIZ, arguments: {
                                'pertemuan_id': controller.pertemuanId,
                              });
                            }
                          : null,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: NetlabsTheme.primary,
                        disabledBackgroundColor: NetlabsTheme.border,
                        minimumSize: const Size(double.infinity, 56),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                        elevation: 0,
                      ),
                      child: Text(
                        "Mulai Kuis Evaluasi",
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w800,
                          letterSpacing: 0.5,
                          color: controller.isKuisEnabled.value ? Colors.white : NetlabsTheme.textMuted,
                        ),
                      ),
                    ),
                  ),
                ],
              );
            }),
            const SizedBox(height: 100), // Space for FAB
          ],
        ),
      ),
      
      // Floating Action Button with Micro-animation
      floatingActionButton: TweenAnimationBuilder<double>(
        tween: Tween<double>(begin: 0.0, end: 1.0),
        duration: const Duration(milliseconds: 600),
        curve: Curves.elasticOut,
        builder: (context, value, child) {
          return Transform.scale(
            scale: value,
            child: child,
          );
        },
        child: FloatingActionButton.extended(
          onPressed: () {
            Get.toNamed(Routes.CHATBOT, arguments: {
              'pertemuan_id': controller.pertemuanId,
            });
          },
          backgroundColor: NetlabsTheme.primary,
          elevation: 4,
          highlightElevation: 8,
          shape: const StadiumBorder(),
          icon: const Icon(Icons.blur_on_rounded, color: Colors.white, size: 22),
          label: const Text(
            "Tanya AI Tutor",
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
          ),
        ),
      ),
    );
  }

  // Refactor Text Format (Anti-Koran)
  Widget _buildFormattedText(String text) {
    // Bersihkan karakter newline kasar agar bisa diparsing
    String cleanText = text.replaceAll(RegExp(r'\n(\d+\.)'), '\n\n\$1');
    cleanText = cleanText.replaceAll(RegExp(r'\n(-)'), '\n\n\$1');
    
    // Pecah menjadi paragraf-paragraf
    final paragraphs = cleanText.split('\n\n');
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: paragraphs.map((p) {
        if (p.trim().isEmpty) return const SizedBox.shrink();
        
        bool isList = p.trim().startsWith(RegExp(r'\d+\.|-'));
        
        return Padding(
          padding: EdgeInsets.only(
            bottom: isList ? 8.0 : 16.0,
            left: isList ? 12.0 : 0.0, // Indentasi jika berupa list
          ),
          child: Text(
            p.trim(),
            style: const TextStyle(
              color: Color(0xFF334155),
              fontSize: 14.5,
              height: 1.7,
              letterSpacing: 0.1,
            ),
          ),
        );
      }).toList(),
    );
  }
}
