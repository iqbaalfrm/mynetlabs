import 'package:dio/dio.dart';
import 'package:get/get.dart' hide Response, FormData, MultipartFile;
import 'package:get_storage/get_storage.dart';
import '../../routes/app_pages.dart';

/// Kelas penyedia HTTP tunggal (Dio) untuk seluruh aplikasi Netlabs.
/// - Base URL diambil dari konstanta [baseUrl].
/// - Token Sanctum disisipkan otomatis via Interceptor.
/// - Response ditangani secara seragam agar mudah dipakai di controller.
class ApiProvider extends GetxController {
  static const String baseUrl = 'http://157.230.93.99/api';

  late Dio _dio;
  final storage = GetStorage();

  /// Flag mencegah redirect login berulang saat 401.
  bool _isRedirecting = false;

  @override
  void onInit() {
    super.onInit();
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 60),
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
      onError: (e, handler) async {
        if (e.response?.statusCode == 401 && !_isRedirecting) {
          _isRedirecting = true;
          _handleUnauthorized();
        }
        print('API ERROR [${e.response?.statusCode}]: ${e.message}');
        // Retry once for transient network errors
        if (_shouldRetry(e)) {
          try {
            final retryResponse = await _dio.fetch(e.requestOptions);
            return handler.resolve(retryResponse);
          } catch (_) {
            // Retry failed, fall through
          }
        }
        return handler.next(e);
      },
    ));
  }

  bool _shouldRetry(DioException e) {
    return e.type == DioExceptionType.connectionTimeout ||
        e.type == DioExceptionType.receiveTimeout ||
        e.type == DioExceptionType.connectionError ||
        (e.response?.statusCode != null && e.response!.statusCode! >= 500);
  }

  void _handleUnauthorized() {
    storage.remove('token');
    storage.remove('user');
    Get.offAllNamed(Routes.LOGIN);
    _isRedirecting = false;
  }

  Dio get dio => _dio;

  // ============ AUTH ============

  Future<Response> login(String username, String password) async {
    return _dio.post('/api/login', data: {
      'username': username,
      'password': password,
    });
  }

  Future<Response> changePassword(String passwordLama, String passwordBaru) async {
    return _dio.post('/api/change-password', data: {
      'password_lama': passwordLama,
      'password_baru': passwordBaru,
    });
  }

  Future<Response> logout() async {
    return await _dio.post('/logout');
  }

  Future<Response> getUserProfile() async {
    return await _dio.get('/user-profile');
  }

  Future<Response> updateFotoProfil(String filePath) async {
    String fileName = filePath.split('/').last;
    FormData formData = FormData.fromMap({
      "foto": await MultipartFile.fromFile(filePath, filename: fileName),
    });
    return await _dio.post(
      '/siswa/foto-profil',
      data: formData,
      options: Options(headers: {"Content-Type": "multipart/form-data"}),
    );
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

  Future<Response> kirimChatAudio(String filePath, {int? pertemuanId}) async {
    final fileName = filePath.split('/').last;
    final formData = FormData.fromMap({
      'pertemuan_id': pertemuanId,
      'audio': await MultipartFile.fromFile(filePath, filename: fileName),
    });
    return await _dio.post(
      '/chat/audio',
      data: formData,
      options: Options(headers: {'Content-Type': 'multipart/form-data'}),
    );
  }

  // ============ STATISTIK SISWA ============

  Future<Response> getStatistikSiswa() async {
    return await _dio.get('/siswa/statistik');
  }

  Future<Response> getPertemuanAktif() async {
    return await _dio.get('/siswa/pertemuan-aktif');
  }
}
