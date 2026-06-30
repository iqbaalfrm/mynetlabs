import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';

/// Centralized session management.
/// Use this instead of directly calling storage.remove() in controllers.
class AuthService extends GetxService {
  final _storage = GetStorage();

  static const _keys = ['token', 'nis', 'nama', 'kelas', 'role'];

  bool get isLoggedIn => _storage.read('token') != null;
  String? get token => _storage.read('token');

  Future<void> clearSession() async {
    for (final key in _keys) {
      await _storage.remove(key);
    }
  }

  void saveLoginData(Map<String, dynamic> data) {
    _storage.write('token', data['token']);
    _storage.write('nis', data['nis']);
    _storage.write('nama', data['nama']);
    _storage.write('kelas', data['kelas']);
    _storage.write('role', data['role']);
  }
}