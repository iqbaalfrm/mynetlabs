import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';

/// Centralized session management.
/// Use this instead of directly calling storage.remove() in controllers.
class AuthService extends GetxService {
  final _storage = GetStorage();

  static const _keys = [
    'token', 'nis', 'nama', 'kelas', 'role',
    'password_is_default', 'must_change_password', 'password_grace_days_remaining'
  ];

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
    _storage.write('password_is_default', data['password_is_default'] ?? false);
    _storage.write('must_change_password', data['must_change_password'] ?? false);
    _storage.write('password_grace_days_remaining', data['password_grace_days_remaining'] ?? 0);
  }
}