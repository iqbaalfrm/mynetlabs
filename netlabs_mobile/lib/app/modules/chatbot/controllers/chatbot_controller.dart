import 'package:flutter/material.dart';
import 'package:get/get.dart';

class ChatbotController extends GetxController {
  late TextEditingController messageController;
  late ScrollController scrollController;

  // RxList untuk menampung riwayat chat di UI
  var chatMessages = <Map<String, dynamic>>[
    {
      "sender": "ai",
      "pesan": "Halo! Aku Netlabs AI Tutor. Ada yang bisa aku bantu seputar praktikum Jaringan Komputer Dasar hari ini?",
      "sumber": null,
      "waktu": "Sekarang"
    }
  ].obs;

  // Pilihan pertanyaan otomatis (Suggestion Chips)[cite: 1]
  var suggestionChips = [
    "Cara hitung subnetting VLSM?",
    "Apa beda CIDR dan Classful?",
    "Kenapa ping gateway bisa RTO?",
  ];

  var isAiTyping = false.obs; // Indikator loading "AI sedang mengetik"[cite: 1]

  @override
  void onInit() {
    super.onInit();
    messageController = TextEditingController();
    scrollController = ScrollController();
  }

  @override
  void onClose() {
    messageController.dispose();
    scrollController.dispose();
    super.onClose();
  }

  // Fungsi mengirim pesan[cite: 1]
  void sendMessage(String text) async {
    if (text.trim().isEmpty) return;

    // 1. Tambahkan pesan siswa ke layar[cite: 1]
    chatMessages.add({
      "sender": "siswa",
      "pesan": text,
      "sumber": null,
      "waktu": "Sekarang"
    });
    messageController.clear();
    scrollToBottom();

    // 2. Aktifkan mode loading AI sedang mengetik[cite: 1]
    isAiTyping.value = true;

    try {
      // Simulasi delay respons RAG + Claude API (Target asli < 3 detik)[cite: 1]
      await Future.delayed(const Duration(seconds: 2));

      // 3. Tambahkan jawaban AI beserta label referensi sumber modul[cite: 1]
      chatMessages.add({
        "sender": "ai",
        "pesan": "Untuk menghitung subnetting menggunakan metode VLSM, langkah pertamanya adalah mengurutkan kebutuhan jumlah host dari yang paling besar ke yang paling kecil. Hal ini dilakukan agar alokasi IP Address efisien dan tidak terjadi bentrokan segmentasi.",
        "sumber": "Pertemuan 3 — Modul Subnetting Jaringan[cite: 1]",
        "waktu": "Sekarang"
      });
    } catch (e) {
      // Handle error jika koneksi gagal
    } finally {
      isAiTyping.value = false;
      scrollToBottom();
    }
  }

  void scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 100), () {
      if (scrollController.hasClients) {
        scrollController.animateTo(
          scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }
}