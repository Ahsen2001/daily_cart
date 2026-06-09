import 'package:go_router/go_router.dart';

import '../screens/auth/login_screen.dart';
import '../screens/auth/forgot_password_screen.dart';
import '../screens/auth/otp_verification_screen.dart';
import '../screens/auth/pending_approval_screen.dart';
import '../screens/auth/register_screen.dart';
import '../models/address_model.dart';
import '../models/checkout_response_model.dart';
import '../screens/customer/add_edit_address_screen.dart';
import '../screens/customer/add_review_screen.dart';
import '../screens/customer/address_list_screen.dart';
import '../screens/customer/available_coupons_screen.dart';
import '../screens/customer/category_screen.dart';
import '../screens/customer/cart_screen.dart';
import '../screens/customer/checkout_preparation_screen.dart';
import '../screens/customer/checkout_screen.dart';
import '../screens/customer/create_support_ticket_screen.dart';
import '../screens/customer/customer_home_screen.dart';
import '../screens/customer/delivery_schedule_screen.dart';
import '../screens/customer/change_password_screen.dart';
import '../screens/customer/edit_profile_screen.dart';
import '../screens/customer/loyalty_history_screen.dart';
import '../screens/customer/loyalty_points_screen.dart';
import '../screens/customer/my_orders_screen.dart';
import '../screens/customer/my_reviews_screen.dart';
import '../screens/customer/notifications_screen.dart';
import '../screens/customer/order_details_screen.dart';
import '../screens/customer/order_success_screen.dart';
import '../screens/customer/order_tracking_screen.dart';
import '../screens/customer/payhere_webview_screen.dart';
import '../screens/customer/payment_failed_screen.dart';
import '../screens/customer/payment_method_screen.dart';
import '../screens/customer/payment_success_screen.dart';
import '../screens/customer/profile_screen.dart';
import '../screens/customer/product_details_screen.dart';
import '../screens/customer/product_list_screen.dart';
import '../screens/customer/product_reviews_screen.dart';
import '../screens/customer/promotion_details_screen.dart';
import '../screens/customer/promotions_screen.dart';
import '../screens/customer/search_screen.dart';
import '../screens/customer/support_ticket_details_screen.dart';
import '../screens/customer/support_tickets_screen.dart';
import '../screens/customer/wishlist_screen.dart';
import '../screens/home/role_home_screen.dart';
import '../screens/onboarding/onboarding_screen.dart';
import '../screens/splash/splash_screen.dart';
import '../screens/vendor/add_product_screen.dart';
import '../screens/vendor/edit_product_screen.dart';
import '../screens/vendor/edit_vendor_profile_screen.dart';
import '../screens/vendor/inventory_screen.dart';
import '../screens/vendor/product_images_screen.dart';
import '../screens/vendor/product_variants_screen.dart';
import '../screens/vendor/vendor_dashboard_screen.dart';
import '../screens/vendor/vendor_earning_details_screen.dart';
import '../screens/vendor/vendor_earnings_screen.dart';
import '../screens/vendor/vendor_order_details_screen.dart';
import '../screens/vendor/vendor_order_screen.dart';
import '../screens/vendor/vendor_product_details_screen.dart';
import '../screens/vendor/vendor_product_list_screen.dart';
import '../screens/vendor/vendor_profile_screen.dart';
import '../screens/vendor/vendor_reviews_screen.dart';
import 'app_routes.dart';

final appRouter = GoRouter(
  initialLocation: AppRoutes.splash,
  routes: [
    GoRoute(
      path: AppRoutes.splash,
      name: 'splash',
      builder: (context, state) => const SplashScreen(),
    ),
    GoRoute(
      path: AppRoutes.onboarding,
      name: 'onboarding',
      builder: (context, state) => const OnboardingScreen(),
    ),
    GoRoute(
      path: AppRoutes.login,
      name: 'login',
      builder: (context, state) => const LoginScreen(),
    ),
    GoRoute(
      path: AppRoutes.register,
      name: 'register',
      builder: (context, state) => const RegisterScreen(),
    ),
    GoRoute(
      path: AppRoutes.forgotPassword,
      name: 'forgot-password',
      builder: (context, state) => const ForgotPasswordScreen(),
    ),
    GoRoute(
      path: AppRoutes.otpVerification,
      name: 'otp-verification',
      builder: (context, state) => const OtpVerificationScreen(),
    ),
    GoRoute(
      path: AppRoutes.pendingApproval,
      name: 'pending-approval',
      builder: (context, state) {
        final extra = state.extra;
        final message = extra is String
            ? extra
            : 'Your account is waiting for admin approval.';

        return PendingApprovalScreen(message: message);
      },
    ),
    GoRoute(
      path: AppRoutes.customerHome,
      name: 'customer-home',
      builder: (context, state) => const CustomerHomeScreen(),
    ),
    GoRoute(
      path: AppRoutes.vendorDashboard,
      name: 'vendor-dashboard',
      builder: (context, state) => const VendorDashboardScreen(),
    ),
    GoRoute(
      path: AppRoutes.vendorProducts,
      name: 'vendor-products',
      builder: (context, state) => const VendorProductListScreen(),
    ),
    GoRoute(
      path: AppRoutes.vendorAddProduct,
      name: 'vendor-add-product',
      builder: (context, state) => const AddProductScreen(),
    ),
    GoRoute(
      path: '${AppRoutes.vendorEditProduct}/:id',
      name: 'vendor-edit-product',
      builder: (context, state) {
        final productId = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
        return EditProductScreen(productId: productId);
      },
    ),
    GoRoute(
      path: '${AppRoutes.vendorProductDetails}/:id',
      name: 'vendor-product-details',
      builder: (context, state) {
        final productId = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
        return VendorProductDetailsScreen(productId: productId);
      },
    ),
    GoRoute(
      path: '${AppRoutes.vendorProductImages}/:id',
      name: 'vendor-product-images',
      builder: (context, state) {
        final productId = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
        return ProductImagesScreen(productId: productId);
      },
    ),
    GoRoute(
      path: '${AppRoutes.vendorProductVariants}/:id',
      name: 'vendor-product-variants',
      builder: (context, state) {
        final productId = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
        return ProductVariantsScreen(productId: productId);
      },
    ),
    GoRoute(
      path: '${AppRoutes.vendorInventory}/:id',
      name: 'vendor-inventory',
      builder: (context, state) {
        final productId = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
        return InventoryScreen(productId: productId);
      },
    ),
    GoRoute(
      path: AppRoutes.vendorOrders,
      name: 'vendor-orders',
      builder: (context, state) => const VendorOrdersScreen(),
    ),
    GoRoute(
      path: '${AppRoutes.vendorOrderDetails}/:id',
      name: 'vendor-order-details',
      builder: (context, state) {
        final orderId = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
        return VendorOrderDetailsScreen(orderId: orderId);
      },
    ),
    GoRoute(
      path: AppRoutes.vendorEarnings,
      name: 'vendor-earnings',
      builder: (context, state) => const VendorEarningsScreen(),
    ),
    GoRoute(
      path: AppRoutes.vendorEarningDetails,
      name: 'vendor-earning-details',
      builder: (context, state) => const VendorEarningDetailsScreen(),
    ),
    GoRoute(
      path: AppRoutes.vendorReviews,
      name: 'vendor-reviews',
      builder: (context, state) => const VendorReviewsScreen(),
    ),
    GoRoute(
      path: AppRoutes.vendorProfile,
      name: 'vendor-profile',
      builder: (context, state) => const VendorProfileScreen(),
    ),
    GoRoute(
      path: AppRoutes.vendorEditProfile,
      name: 'vendor-edit-profile',
      builder: (context, state) => const EditVendorProfileScreen(),
    ),
    GoRoute(
      path: AppRoutes.riderDashboard,
      name: 'rider-dashboard',
      builder: (context, state) => const RoleHomeScreen(roleName: 'Rider'),
    ),
    GoRoute(
      path: AppRoutes.categories,
      name: 'categories',
      builder: (context, state) => const CategoryScreen(),
    ),
    GoRoute(
      path: AppRoutes.products,
      name: 'products',
      builder: (context, state) {
        final categoryId = int.tryParse(
          state.uri.queryParameters['categoryId'] ?? '',
        );
        final categoryName = state.uri.queryParameters['categoryName'];

        return ProductListScreen(
          categoryId: categoryId,
          categoryName: categoryName,
        );
      },
    ),
    GoRoute(
      path: '${AppRoutes.productDetails}/:id',
      name: 'product-details',
      builder: (context, state) {
        final productId = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
        return ProductDetailsScreen(productId: productId);
      },
    ),
    GoRoute(
      path: AppRoutes.search,
      name: 'search',
      builder: (context, state) => const SearchScreen(),
    ),
    GoRoute(
      path: AppRoutes.wishlist,
      name: 'wishlist',
      builder: (context, state) => const WishlistScreen(),
    ),
    GoRoute(
      path: AppRoutes.cart,
      name: 'cart',
      builder: (context, state) => const CartScreen(),
    ),
    GoRoute(
      path: AppRoutes.checkoutPreparation,
      name: 'checkout-preparation',
      builder: (context, state) => const CheckoutPreparationScreen(),
    ),
    GoRoute(
      path: AppRoutes.checkout,
      name: 'checkout',
      builder: (context, state) => const CheckoutScreen(),
    ),
    GoRoute(
      path: AppRoutes.addresses,
      name: 'addresses',
      builder: (context, state) => const AddressListScreen(),
    ),
    GoRoute(
      path: AppRoutes.addressForm,
      name: 'address-form',
      builder: (context, state) {
        final extra = state.extra;
        return AddEditAddressScreen(
          address: extra is AddressModel ? extra : null,
        );
      },
    ),
    GoRoute(
      path: AppRoutes.deliverySchedule,
      name: 'delivery-schedule',
      builder: (context, state) => const DeliveryScheduleScreen(),
    ),
    GoRoute(
      path: AppRoutes.paymentMethod,
      name: 'payment-method',
      builder: (context, state) => const PaymentMethodScreen(),
    ),
    GoRoute(
      path: AppRoutes.payHereWebView,
      name: 'payhere-webview',
      builder: (context, state) {
        final extra = state.extra;
        if (extra is Map) {
          return PayHereWebViewScreen(
            orderId: extra['orderId'] as int,
            paymentUrl: extra['paymentUrl'] as String,
          );
        }
        return const PaymentFailedScreen();
      },
    ),
    GoRoute(
      path: AppRoutes.paymentSuccess,
      name: 'payment-success',
      builder: (context, state) {
        final extra = state.extra;
        return PaymentSuccessScreen(
          order: extra is OrderModel ? extra : null,
        );
      },
    ),
    GoRoute(
      path: AppRoutes.paymentFailed,
      name: 'payment-failed',
      builder: (context, state) {
        final extra = state.extra;
        return PaymentFailedScreen(
          order: extra is OrderModel ? extra : null,
        );
      },
    ),
    GoRoute(
      path: AppRoutes.orderSuccess,
      name: 'order-success',
      builder: (context, state) {
        final extra = state.extra;
        return OrderSuccessScreen(
          order: extra is OrderModel
              ? extra
              : const OrderModel(
                  id: 0,
                  orderNumber: '',
                  status: 'pending',
                  paymentStatus: 'pending',
                  grandTotal: 0,
                ),
        );
      },
    ),
    GoRoute(
      path: AppRoutes.myOrders,
      name: 'my-orders',
      builder: (context, state) => const MyOrdersScreen(),
    ),
    GoRoute(
      path: '${AppRoutes.orderDetails}/:id',
      name: 'order-details',
      builder: (context, state) {
        final orderId = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
        return OrderDetailsScreen(orderId: orderId);
      },
    ),
    GoRoute(
      path: '${AppRoutes.orderTracking}/:id',
      name: 'order-tracking',
      builder: (context, state) {
        final orderId = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
        return OrderTrackingScreen(orderId: orderId);
      },
    ),
    GoRoute(
      path: AppRoutes.notifications,
      name: 'notifications',
      builder: (context, state) => const NotificationsScreen(),
    ),
    GoRoute(
      path: AppRoutes.profile,
      name: 'profile',
      builder: (context, state) => const ProfileScreen(),
    ),
    GoRoute(
      path: AppRoutes.editProfile,
      name: 'edit-profile',
      builder: (context, state) => const EditProfileScreen(),
    ),
    GoRoute(
      path: AppRoutes.changePassword,
      name: 'change-password',
      builder: (context, state) => const ChangePasswordScreen(),
    ),
    GoRoute(
      path: '${AppRoutes.addReview}/:orderId/:productId',
      name: 'add-review',
      builder: (context, state) {
        final orderId =
            int.tryParse(state.pathParameters['orderId'] ?? '') ?? 0;
        final productId =
            int.tryParse(state.pathParameters['productId'] ?? '') ?? 0;
        return AddReviewScreen(orderId: orderId, productId: productId);
      },
    ),
    GoRoute(
      path: AppRoutes.myReviews,
      name: 'my-reviews',
      builder: (context, state) => const MyReviewsScreen(),
    ),
    GoRoute(
      path: '${AppRoutes.productReviews}/:productId',
      name: 'product-reviews',
      builder: (context, state) {
        final productId =
            int.tryParse(state.pathParameters['productId'] ?? '') ?? 0;
        return ProductReviewsScreen(productId: productId);
      },
    ),
    GoRoute(
      path: AppRoutes.supportTickets,
      name: 'support-tickets',
      builder: (context, state) => const SupportTicketsScreen(),
    ),
    GoRoute(
      path: AppRoutes.createSupportTicket,
      name: 'create-support-ticket',
      builder: (context, state) => const CreateSupportTicketScreen(),
    ),
    GoRoute(
      path: '${AppRoutes.supportTicketDetails}/:id',
      name: 'support-ticket-details',
      builder: (context, state) {
        final ticketId = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
        return SupportTicketDetailsScreen(ticketId: ticketId);
      },
    ),
    GoRoute(
      path: AppRoutes.loyaltyPoints,
      name: 'loyalty-points',
      builder: (context, state) => const LoyaltyPointsScreen(),
    ),
    GoRoute(
      path: AppRoutes.loyaltyHistory,
      name: 'loyalty-history',
      builder: (context, state) => const LoyaltyHistoryScreen(),
    ),
    GoRoute(
      path: AppRoutes.availableCoupons,
      name: 'available-coupons',
      builder: (context, state) => const AvailableCouponsScreen(),
    ),
    GoRoute(
      path: AppRoutes.promotions,
      name: 'promotions',
      builder: (context, state) => const PromotionsScreen(),
    ),
    GoRoute(
      path: '${AppRoutes.promotionDetails}/:id',
      name: 'promotion-details',
      builder: (context, state) {
        final promotionId =
            int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
        return PromotionDetailsScreen(promotionId: promotionId);
      },
    ),
  ],
);
