import 'package:flutter/foundation.dart';
import '../models/plan.dart';
import '../services/api_service.dart';

class PlanProvider extends ChangeNotifier {
  final ApiService _api;

  List<Plan> _plans = [];
  bool _loading = false;
  String? _error;

  PlanProvider(this._api);

  List<Plan> get plans => _plans;
  bool get loading => _loading;
  String? get error => _error;

  Future<void> loadPlans() async {
    _loading = true;
    _error = null;
    notifyListeners();
    try {
      _plans = await _api.getPlans();
    } catch (e) {
      _error = e.toString();
    } finally {
      _loading = false;
      notifyListeners();
    }
  }
}
