import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/detail_materi_controller.dart';

class DetailMateriView extends GetView<DetailMateriController> {
  const DetailMateriView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(
          "Pertemuan ${controller.nomorPertemuan}",
          style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A)),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xFF1E3A8A)),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Judul Utama Bab
            Text(
              controller.judulPertemuan,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A)),
            ),
            const SizedBox(height: 10),
            
            // Deskripsi/Tujuan Pembelajaran Bab
            Text(
              controller.deskripsiPertemuan,
              style: TextStyle(fontSize: 13, color: Colors.grey.shade600, height: 1.5),
            ),
            const SizedBox(height: 24),

            const Text(
              "Daftar Topik Pembelajaran",
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A)),
            ),
            const SizedBox(height: 12),

            // List Komponen Expansion Materi per Topik
            Obx(() => ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: controller.daftarTopik.length,
                  separatorBuilder: (context, index) => const SizedBox(height: 12),
                  itemBuilder: (context, index) {
                    var topik = controller.daftarTopik[index];
                    bool isCompleted = topik['is_completed'] as bool;

                    return Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.grey.withAlpha(30)),
                      ),
                      child: ExpansionTile(
                        leading: Checkbox(
                          value: isCompleted,
                          activeColor: Colors.green,
                          onChanged: (value) {
                            controller.toggleCompleteTopik(index);
                          },
                        ),
                        title: Text(
                          topik['judul'] as String,
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: isCompleted ? Colors.green.shade700 : const Color(0xFF1E3A8A),
                            decoration: isCompleted ? TextDecoration.lineThrough : null,
                          ),
                        ),
                        children: [
                          Padding(
                            padding: const EdgeInsets.only(left: 16, right: 16, bottom: 16),
                            child: Text(
                              topik['isi'] as String,
                              style: const TextStyle(fontSize: 13, color: Colors.black87, height: 1.6),
                              textAlign: TextAlign.left,
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                )),
            const SizedBox(height: 35),

            // Tombol Mulai Kuis Evaluasi
            Obx(() => ElevatedButton(
                  onPressed: controller.isKuisEnabled.value
                      ? () {
                          // Rute menuju halaman Pengerjaan Kuis
                        }
                      : null, // Otomatis disabled jika belum centang semua topik
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF3B82F6),
                    disabledBackgroundColor: Colors.grey.shade300,
                    minimumSize: const Size(double.infinity, 50),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: Text(
                    "Mulai Kuis Evaluasi",
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: controller.isKuisEnabled.value ? Colors.white : Colors.grey.shade500,
                    ),
                  ),
                )),
          ],
        ),
      ),
      
      // Floating Action Button untuk Akses Cepat AI Tutor Kontekstual
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          // Navigasi langsung lompat ke halaman chatbot RAG
        },
        backgroundColor: const Color(0xFF1E3A8A),
        icon: const Icon(Icons.smart_toy_rounded, color: Colors.white),
        label: const Text("Tanya AI Tutor", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
      ),
    );
  }
}