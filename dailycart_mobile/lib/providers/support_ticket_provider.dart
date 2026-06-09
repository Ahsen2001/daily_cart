import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/support_ticket_model.dart';
import '../services/auth_api_service.dart';
import '../services/support_ticket_api_service.dart';

final supportTicketApiServiceProvider =
    Provider<SupportTicketApiService>((ref) {
  return SupportTicketApiService();
});

final supportTicketProvider =
    ChangeNotifierProvider<SupportTicketProvider>((ref) {
  return SupportTicketProvider(ref.watch(supportTicketApiServiceProvider));
});

class SupportTicketProvider extends ChangeNotifier {
  SupportTicketProvider(this._apiService);

  final SupportTicketApiService _apiService;

  List<SupportTicketModel> tickets = const [];
  SupportTicketModel? selectedTicket;
  bool isLoading = false;
  String? errorMessage;

  Future<void> getTickets() async {
    await _run(() async {
      tickets = await _apiService.getTickets();
    });
  }

  Future<bool> createTicket({
    required String subject,
    required String message,
    required String priority,
    int? orderId,
  }) async {
    return _run(() async {
      final ticket = await _apiService.createTicket(
        subject: subject,
        message: message,
        priority: priority,
        orderId: orderId,
      );
      tickets = [ticket, ...tickets];
      selectedTicket = ticket;
    });
  }

  Future<void> getTicketDetails(int ticketId) async {
    await _run(() async {
      selectedTicket = await _apiService.getTicketDetails(ticketId);
    });
  }

  Future<bool> replyToTicket({
    required int ticketId,
    required String message,
  }) async {
    return _run(() async {
      selectedTicket = await _apiService.replyToTicket(
        ticketId: ticketId,
        message: message,
      );
    });
  }

  Future<bool> closeTicket(int ticketId) async {
    return _run(() async {
      selectedTicket = await _apiService.closeTicket(ticketId);
      tickets = tickets
          .map((item) => item.id == ticketId ? selectedTicket! : item)
          .toList(growable: false);
    });
  }

  Future<bool> _run(Future<void> Function() action) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();
    try {
      await action();
      return true;
    } on ApiException catch (error) {
      errorMessage = error.message;
      return false;
    } catch (_) {
      errorMessage = 'Something went wrong. Please try again.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
