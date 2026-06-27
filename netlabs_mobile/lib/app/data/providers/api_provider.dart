import 'package:dio/dio.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';

/// Kelas penyedia HTTP tunggal (Dio) untuk seluruh aplikasi Netlabs.
/// - Base URL diambil dari konstanta [baseUrl].
/// - Token Sanctum disisipkan otomatis via Interceptor.
/// - Response ditangani secara seragam agar mudah dipakai di controller.
class ApiProvider extends GetxController {
  static const String baseUrl = 'http://157.230.93.99/api';

  late Dio _dio;
  final storage = GetStorage();

  @override
  void onInit() {
    super.onInit();
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 20),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) {
        final token = storage.read('token');
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (e, handler) {
        // Logging sederhana untuk debugging selama pengembangan
        print('API ERROR [${e.response?.statusCode}]: ${e.message}');
        return handler.next(e);
      },
    ));
  }

  Dio get dio => _dio;

  // ============ AUTH ============

  Future<Response> login(String username, String password) async {
    return await _dio.post('/login', data: {
      'username': username,
      'password': password,
    });
  }

  Future<Response> register(Map<String, dynamic> data) async {
    return await _dio.post('/register', data: data);
  }

  Future<Response> logout() async {
    return await _dio.post('/logout');
  }

  Future<Response> getUserProfile() async {
    return await _dio.get('/user-profile');
  }

  // ============ MATERI ============

  Future<Response> getPertemuan() async {
    return await _dio.get('/pertemuan');
  }

  Future<Response> getDetailPertemuan(int id) async {
    return await _dio.get('/pertemuan/$id');
  }

  Future<Response> tandaiTopikSelesai(int pertemuanId, int topikId) async {
    return await _dio.post('/pertemuan/$pertemuanId/topik/$topikId/selesai');
  }

  // ============ KUIS ============

  Future<Response> getSoalKuis(int pertemuanId) async {
    return await _dio.get('/pertemuan/$pertemuanId/kuis');
  }

  Future<Response> submitKuis(int pertemuanId, List<Map<String, dynamic>> jawaban) async {
    return await _dio.post('/kuis/submit', data: {
      'pertemuan_id': pertemuanId,
      'jawaban': jawaban,
    });
  }

  Future<Response> getRiwayatKuis() async {
    return await _dio.get('/kuis/riwayat');
  }

  // ============ CHAT AI ============

  Future<Response> getRiwayatChat() async {
    return await _dio.get('/chat/riwayat');
  }

  Future<Response> kirimChat(String pesan, {int? pertemuanId}) async {
    return await _dio.post('/chat', data: {
      'pertemuan_id': pertemuanId,
      'pesan': pesan,
    });
  }

  // ============ STATISTIK SISWA ============

  Future<Response> getStatistikSiswa() async {
    return await _dio.get('/siswa/statistik');
  }

  Future<Response> getPertemuanAktif() async {
    return await _dio.get('/siswa/pertemuan-aktif');
  }
}
