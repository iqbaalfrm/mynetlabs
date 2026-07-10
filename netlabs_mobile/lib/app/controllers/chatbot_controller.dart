import 'dart:io' show Directory;
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:image_picker/image_picker.dart';
import 'package:file_picker/file_picker.dart';
import 'package:flutter_tts/flutter_tts.dart';
import 'package:record/record.dart';
import '../../data/providers/api_provider.dart';

class ChatbotController extends GetxController {
  final ApiProvider _api = Get.find<ApiProvider>();
  late final TextEditingController msgCtrl;
  late final ScrollController scrollCtrl;
  final FlutterTts _tts = FlutterTts();
  final AudioRecorder _recorder = AudioRecorder();

  int? get pertemuanId => _pertemuanId;
  int? _pertemuanId;

  var messages = <Map<String, dynamic>>[].obs;
  final chips = <String>[].obs;
  var isAiTyping = false.obs;
  var _isSending = false;

  final Rx<Map<String, dynamic>?> pendingAttachment = Rx<Map<String, dynamic>?>(null);
  final RxBool isSpeaking = false.obs;
  final RxString speakingMessageId = ''.obs;

  // Recording state
  final RxBool isRecording = false.obs;
  final RxInt recordDuration = 0.obs;
  String? _recordPath;

  @override
  void onInit() {
    super.onInit();
    msgCtrl = TextEditingController();
    scrollCtrl = ScrollController();

    if (Get.arguments != null) {
      _pertemuanId = Get.arguments['pertemuan_id'] as int?;
    }

    _initContextual();
    loadRiwayat();
    initTts();
  }

  void _initContextual() {
    if (_pertemuanId != null) {
      messages.add({
        'id': 'init_${DateTime.now().millisecondsSinceEpoch}',
        'sender': 'ai',
        'pesan': 'Halo! Aku NetLabs AI Tutor. Tanyakan apa saja tentang materi modul #$_pertemuanId yang sedang kamu pelajari.',
        'sumber': null,
      });
      chips.value = ['Apa saja yang dibahas di modul ini?', 'Beri rangkuman singkat', 'Contoh soal latihan'];
    } else {
      messages.add({
        'id': 'init_${DateTime.now().millisecondsSinceEpoch}',
        'sender': 'ai',
        'pesan': 'Halo! Aku NetLabs AI Tutor. Tanyakan apa saja tentang praktikum Jaringan Komputer yang sedang kamu pelajari.',
        'sumber': null,
      });
      chips.value = ['Cara hitung subnetting VLSM?', 'Apa beda CIDR dan Classful?', 'Kenapa ping gateway bisa RTO?'];
    }
  }

  @override
  void onClose() {
    msgCtrl.dispose();
    scrollCtrl.dispose();
    _tts.stop();
    _recorder.dispose();
    super.onClose();
  }

  // ---- TTS ----

  Future<void> initTts() async {
    await _tts.setLanguage('id-ID');
    await _tts.setSpeechRate(0.35);
    await _tts.setVolume(1.0);
    await _tts.setPitch(0.85);
    await _tts.setVoice({'name': 'id-id-x-sfg#male_1-local', 'locale': 'id-ID'});
    _tts.setCompletionHandler(() {
      isSpeaking.value = false;
      speakingMessageId.value = '';
    });
  }

  Future<void> speakMessage(String messageId, String text) async {
    if (isSpeaking.value && speakingMessageId.value == messageId) {
      await _tts.stop();
      isSpeaking.value = false;
      speakingMessageId.value = '';
    } else {
      await _tts.stop();
      speakingMessageId.value = messageId;
      isSpeaking.value = true;
      await _tts.speak(text);
    }
  }

  // ---- Image / File Picker ----

  Future<void> pickImage(ImageSource source) async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: source, imageQuality: 80);
    if (picked != null) {
      pendingAttachment.value = {
        'type': 'image',
        'path': picked.path,
        'name': picked.name,
      };
    }
  }

  Future<void> pickFile() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf', 'docx', 'txt'],
    );
    if (result != null && result.files.isNotEmpty) {
      final f = result.files.first;
      pendingAttachment.value = {
        'type': 'file',
        'path': f.path,
        'name': f.name,
        'size': f.size,
      };
    }
  }

  void clearAttachment() {
    pendingAttachment.value = null;
  }

  // ---- Voice Note Recording ----

  Future<void> startRecording() async {
    final hasPermission = await _recorder.hasPermission();
    if (!hasPermission) {
      Get.snackbar('Izin Ditolak', 'Microphone permission diperlukan untuk merekam suara.');
      return;
    }
    try {
      _recordPath = null;
      recordDuration.value = 0;
      isRecording.value = true;

      await _recorder.start(
        const RecordConfig(
          encoder: AudioEncoder.aacLc,
          bitRate: 128000,
          sampleRate: 44100,
        ),
        path: '${Directory.systemTemp.path}/vn_${DateTime.now().millisecondsSinceEpoch}.m4a',
      );

      // Update duration setiap detik
      _recordTick();
    } catch (e) {
      isRecording.value = false;
      Get.snackbar('Error', 'Gagal memulai rekaman.');
    }
  }

  void _recordTick() async {
    while (isRecording.value) {
      await Future.delayed(const Duration(seconds: 1));
      if (isRecording.value) recordDuration.value++;
    }
  }

  Future<void> stopRecording() async {
    if (!isRecording.value) return;
    try {
      _recordPath = await _recorder.stop();
      isRecording.value = false;
      if (_recordPath != null) {
        await _sendVoiceNote(_recordPath!);
      }
    } catch (e) {
      isRecording.value = false;
      Get.snackbar('Error', 'Gagal menghentikan rekaman.');
    }
  }

  Future<void> cancelRecording() async {
    if (!isRecording.value) return;
    try {
      await _recorder.stop();
    } catch (_) {}
    isRecording.value = false;
    recordDuration.value = 0;
  }

  Future<void> _sendVoiceNote(String filePath) async {
    _isSending = true;

    final msgId = 'msg_${DateTime.now().millisecondsSinceEpoch}';
    messages.add({
      'id': msgId,
      'sender': 'siswa',
      'pesan': '🎤 Voice Note (${_formatDuration(recordDuration.value)})',
      'sumber': null,
      'attachment': {
        'type': 'audio',
        'path': filePath,
        'name': 'Voice Note',
        'duration': recordDuration.value,
      },
    });

    _scrollToBottom();
    isAiTyping.value = true;

    try {
      final r = await _api.kirimChatAudio(filePath, pertemuanId: _pertemuanId);
      final d = r.data['data'];
      final aiId = 'msg_${DateTime.now().millisecondsSinceEpoch}';
      messages.add({
        'id': aiId,
        'sender': d['sender'] ?? 'ai',
        'pesan': d['pesan'] ?? '',
        'sumber': d['sumber'],
      });
    } catch (_) {
      final errId = 'msg_${DateTime.now().millisecondsSinceEpoch}';
      messages.add({
        'id': errId,
        'sender': 'ai',
        'pesan': 'Maaf, terjadi kesalahan. Coba lagi ya.',
        'sumber': null,
      });
    } finally {
      isAiTyping.value = false;
      _isSending = false;
      recordDuration.value = 0;
      _scrollToBottom();
    }
  }

  // ---- Send ----

  Future<void> sendMessage(String text) async {
    if ((text.trim().isEmpty && pendingAttachment.value == null) || _isSending) return;
    _isSending = true;

    final att = pendingAttachment.value;
    final hasAtt = att != null;

    final msgId = 'msg_${DateTime.now().millisecondsSinceEpoch}';
    messages.add({
      'id': msgId,
      'sender': 'siswa',
      'pesan': text.trim().isEmpty ? (att?['type'] == 'image' ? '📷 Gambar' : '📎 ${att?['name'] ?? 'File'}') : text,
      'sumber': null,
      'attachment': hasAtt ? Map<String, dynamic>.from(att) : null,
    });

    msgCtrl.clear();
    clearAttachment();
    _scrollToBottom();
    isAiTyping.value = true;

    try {
      final r = await _api.kirimChat(text, pertemuanId: _pertemuanId);
      final d = r.data['data'];
      final aiId = 'msg_${DateTime.now().millisecondsSinceEpoch}';
      messages.add({
        'id': aiId,
        'sender': d['sender'] ?? 'ai',
        'pesan': d['pesan'] ?? '',
        'sumber': d['sumber'],
      });
    } catch (_) {
      final errId = 'msg_${DateTime.now().millisecondsSinceEpoch}';
      messages.add({
        'id': errId,
        'sender': 'ai',
        'pesan': 'Maaf, terjadi kesalahan. Coba lagi ya.',
        'sumber': null,
      });
    } finally {
      isAiTyping.value = false;
      _isSending = false;
      _scrollToBottom();
    }
  }

  // ---- Riwayat ----

  Future<void> loadRiwayat() async {
    try {
      final r = await _api.getRiwayatChat();
      final list = r.data['data'] as List;
      if (list.isNotEmpty) {
        messages.clear();
        for (var i = 0; i < list.length; i++) {
          final item = list[i] as Map<String, dynamic>;
          messages.add({
            'id': 'hist_$i',
            'sender': item['sender'],
            'pesan': item['pesan'],
            'sumber': item['sumber'],
          });
        }
        _scrollToBottom();
      }
    } catch (_) {}
  }

  void _scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 120), () {
      if (scrollCtrl.hasClients) {
        scrollCtrl.animateTo(
          scrollCtrl.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  String _formatDuration(int seconds) {
    final m = seconds ~/ 60;
    final s = seconds % 60;
    return '${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
  }
}