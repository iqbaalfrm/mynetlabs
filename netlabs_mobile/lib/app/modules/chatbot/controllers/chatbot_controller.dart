import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../data/providers/api_provider.dart';

class ChatbotController extends GetxController {
  final ApiProvider _api = Get.find<ApiProvider>();
  late final TextEditingController msgCtrl;
  late final ScrollController scrollCtrl;

  var messages = <Map<String, dynamic>>[
    {'sender': 'ai', 'pesan': 'Halo! Aku NetLabs AI Tutor. Tanyakan apa saja tentang praktikum Jaringan Komputer yang sedang kamu pelajari.', 'sumber': null}
  ].obs;

  final chips = ['Cara hitung subnetting VLSM?', 'Apa beda CIDR dan Classful?', 'Kenapa ping gateway bisa RTO?'];
  var isAiTyping = false.obs;

  @override
  void onInit() {
    super.onInit();
    msgCtrl = TextEditingController();
    scrollCtrl = ScrollController();
    loadRiwayat();
  }

  @override
  void onClose() {
    msgCtrl.dispose();
    scrollCtrl.dispose();
    super.onClose();
  }

  Future<void> loadRiwayat() async {
    try {
      final r = await _api.getRiwayatChat();
      final list = r.data['data'] as List;
      if (list.isNotEmpty) {
        messages.value = list.map((e) => {
          'sender': e['sender'], 'pesan': e['pesan'], 'sumber': e['sumber'],
        }).toList();
        _scrollToBottom();
      }
    } catch (_) {}
  }

  Future<void> sendMessage(String text, {int? pertemuanId}) async {
    if (text.trim().isEmpty) return;
    messages.add({'sender': 'siswa', 'pesan': text, 'sumber': null});
    msgCtrl.clear();
    _scrollToBottom();
    isAiTyping.value = true;

    try {
      final r = await _api.kirimChat(text, pertemuanId: pertemuanId);
      final d = r.data['data'];
      messages.add({'sender': d['sender'] ?? 'ai', 'pesan': d['pesan'] ?? '', 'sumber': d['sumber']});
    } catch (_) {
      messages.add({'sender': 'ai', 'pesan': 'Maaf, terjadi kesalahan. Coba lagi ya.', 'sumber': null});
    } finally {
      isAiTyping.value = false;
      _scrollToBottom();
    }
  }

  void _scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 120), () {
      if (scrollCtrl.hasClients) {
        scrollCtrl.animateTo(scrollCtrl.position.maxScrollExtent, duration: const Duration(milliseconds: 300), curve: Curves.easeOut);
      }
    });
  }
}
