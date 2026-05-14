# DailyCart Notifications, Support Tickets, Reviews, and Ratings

## Artisan Commands Used

```bash
php artisan make:migration add_support_review_notification_fields
php artisan make:model SupportTicketReply
php artisan make:controller NotificationController
php artisan make:controller SupportTicketController
php artisan make:controller SupportTicketReplyController
php artisan make:controller Customer/ReviewController
php artisan make:controller Vendor/VendorReviewController
php artisan make:controller Admin/AdminNotificationController
php artisan make:controller Admin/AdminSupportTicketController
php artisan make:controller Admin/AdminReviewController
php artisan make:request StoreSupportTicketRequest
php artisan make:request StoreSupportTicketReplyRequest
php artisan make:request AdminSupportTicketRequest
php artisan make:request StoreReviewRequest
php artisan make:request AdminReviewRequest
php artisan make:policy SupportTicketPolicy --model=SupportTicket
php artisan make:policy ReviewPolicy --model=Review
php artisan make:policy NotificationPolicy --model=Notification
php artisan make:mail GenericNotificationMail
php artisan make:notification UserRegistrationSuccessNotification
php artisan make:notification VendorApprovedNotification
php artisan make:notification VendorRejectedNotification
php artisan make:notification RiderApprovedNotification
php artisan make:notification RiderRejectedNotification
php artisan make:notification OrderPlacedNotification
php artisan make:notification SupportTicketReplyNotification
php artisan make:notification LowStockAlertNotification
```

## Notification System

- Notifications are stored in the existing `notifications` table.
- Users can view their own notifications and mark them read or unread.
- Admin can view all notifications and mark any notification as read.
- Email notification support is available through `GenericNotificationMail`.
- SMS, WhatsApp, and push notification methods are placeholders inside `NotificationService`.
- Low stock alerts are sent to vendors when product stock is at or below the threshold.

## Support Tickets

- Any authenticated user can create support tickets.
- Customers can link tickets only to their own orders.
- Users can view and reply only to their own tickets.
- Admin can view all tickets, assign support admins, update status, and reply.
- Ticket statuses: `open`, `in_progress`, `resolved`, `closed`.
- Priority levels: `low`, `medium`, `high`, `urgent`.

## Reviews and Ratings

- Customers can review only products purchased in their own delivered orders.
- Each customer can review a purchased product only once per order.
- Rating must be between 1 and 5.
- Optional comment and image uploads are supported.
- Vendors can view reviews only for their own products.
- Admin can hide inappropriate reviews or delete fake reviews.
- Product average rating and review count are available on the `Product` model.

## Testing Steps

1. Run `php artisan migrate`.
2. Log in as a customer and open a delivered order.
3. Click Write review for a purchased product and submit a 1-5 rating.
4. Try reviewing the same product for the same order again; it should be blocked.
5. Log in as the vendor and open Vendor > Reviews; only own product reviews should appear.
6. Log in as admin and open Admin > Reviews; hide or delete a review.
7. Create a support ticket as a customer, vendor, or rider.
8. Confirm the user can view only their own support tickets.
9. Log in as admin, assign/reply/update the ticket.
10. Confirm notification records are created and can be marked read/unread.
