import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../data/providers/api_provider.dart';

class ChatbotController extends GetxController {
  final ApiProvider _api = Get.find<ApiProvider>();

  late TextEditingController messageController;
  late ScrollController scrollController;

  var chatMessages = <Map<String, dynamic>>[
    {
      'sender': 'ai',
      'pesan': 'Halo! Aku Netlabs AI Tutor. Ada yang bisa aku bantu seputar praktikum Jaringan Komputer Dasar hari ini?',
      'sumber': null,
      'waktu': 'Sekarang'
    }
  ].obs;

  var suggestionChips = [
    'Cara hitung subnetting VLSM?',
    'Apa beda CIDR dan Classful?',
    'Kenapa ping gateway bisa RTO?',
  ];

  var isAiTyping = false.obs;

  @override
  void onInit() {
    super.onInit();
    messageController = TextEditingController();
    scrollController = ScrollController();
    loadRiwayatChat();
  }

  @override
  void onClose() {
    messageController.dispose();
    scrollController.dispose();
    super.onClose();
  }

  void loadRiwayatChat() async {
    try {
      final response = await _api.getRiwayatChat();
      final list = response.data['data'] as List;
      if (list.isNotEmpty) {
        chatMessages.value = list
            .map((e) => Map<String, dynamic>.from(e))
            .map((e) => {
                  'sender': e['sender'],
                  'pesan': e['pesan'],
                  'sumber': e['sumber'],
                  'waktu': e['waktu'],
                })
            .toList();
        scrollToBottom();
      }
    } catch (e) {
      print('Gagal memuat riwayat chat: $e');
    }
  }

  void sendMessage(String text) async {
    if (text.trim().isEmpty) return;

    chatMessages.add({
      'sender': 'siswa',
      'pesan': text,
      'sumber': null,
      'waktu': 'Sekarang'
    });
    messageController.clear();
    scrollToBottom();

    isAiTyping.value = true;

    try {
      final response = await _api.kirimChat(text);
      final data = response.data['data'];

      chatMessages.add({
        'sender': data['sender'],
        'pesan': data['pesan'],
        'sumber': data['sumber'],
        'waktu': data['waktu'] ?? 'Sekarang',
      });
    } catch (e) {
      chatMessages.add({
        'sender': 'ai',
        'pesan': 'Maaf, terjadi kesalahan saat memproses pesan Anda. Coba lagi ya.',
        'sumber': null,
        'waktu': 'Sekarang'
      });
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
