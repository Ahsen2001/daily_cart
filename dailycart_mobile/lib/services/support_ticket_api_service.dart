import 'package:dio/dio.dart';

import '../models/support_ticket_model.dart';
import '../networking/api_client.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class SupportTicketApiService with AuthenticatedApiMixin {
  SupportTicketApiService({Dio? dio, SecureStorageHelper? storage})
    : _dio = dio ?? ApiClient.shared.dio,
      _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<List<SupportTicketModel>> getTickets() async {
    try {
      final response = await _dio.get<dynamic>(
        '/support-tickets',
        options: await authOptions(),
      );
      return ApiListParser.extractList(
        response.data,
        key: 'tickets',
      ).map(SupportTicketModel.fromJson).toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<SupportTicketModel> createTicket({
    required String subject,
    required String message,
    required String priority,
    int? orderId,
  }) async {
    try {
      final response = await _dio.post<dynamic>(
        '/support-tickets',
        data: {
          'subject': subject,
          'message': message,
          'priority': priority,
          'order_id': ?orderId,
        },
        options: await authOptions(),
      );
      return SupportTicketModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<SupportTicketModel> getTicketDetails(int ticketId) async {
    try {
      final response = await _dio.get<dynamic>(
        '/support-tickets/$ticketId',
        options: await authOptions(),
      );
      return SupportTicketModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<SupportTicketModel> replyToTicket({
    required int ticketId,
    required String message,
  }) async {
    try {
      final response = await _dio.post<dynamic>(
        '/support-tickets/$ticketId/replies',
        data: {'message': message},
        options: await authOptions(),
      );
      return SupportTicketModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<SupportTicketModel> closeTicket(int ticketId) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/support-tickets/$ticketId/close',
        options: await authOptions(),
      );
      return SupportTicketModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
