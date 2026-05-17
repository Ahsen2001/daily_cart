# DailyCart External API Integration

DailyCart supports English-only content and LKR-only payments. Delivery scheduling remains validated in Laravel with the minimum time of order time plus 30 minutes.

## Required Accounts

1. Google Cloud account with Maps JavaScript API, Places API, Geocoding API, and Distance Matrix API enabled.
2. SMTP provider account such as Gmail App Password, Mailtrap, Brevo, Mailgun, or any university SMTP server.
3. PayHere merchant account with sandbox enabled first, then live merchant credentials.

## Required Keys

1. `GOOGLE_MAPS_BROWSER_KEY`: restricted to your website domain, used by the checkout and tracking maps.
2. `GOOGLE_MAPS_SERVER_KEY`: restricted to server IP where possible, used by backend geocode and distance endpoints.
3. SMTP username/password and sender address.
4. `PAYHERE_MERCHANT_ID` and `PAYHERE_MERCHANT_SECRET`.

## Environment Variables

```env
APP_NAME=DailyCart
APP_URL=http://127.0.0.1:8001

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="uahsens1@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

GOOGLE_MAPS_BROWSER_KEY=your_browser_key
GOOGLE_MAPS_SERVER_KEY=your_server_key

PAYHERE_SANDBOX=true
PAYHERE_MERCHANT_ID=your_merchant_id
PAYHERE_MERCHANT_SECRET=your_merchant_secret

OTP_LOGIN_ENABLED=true
OTP_EXPIRES_MINUTES=10
```

## Implemented Laravel Files

Services:
- `App\Services\ExternalEmailService`
- `App\Services\OtpService`
- `App\Services\GoogleMapsService`
- `App\Services\PayHereService`

Controllers:
- `App\Http\Controllers\Auth\OtpController`
- `App\Http\Controllers\Integrations\GoogleMapsController`
- `App\Http\Controllers\Integrations\PayHereController`

Database:
- `email_otps`
- `api_integration_logs`
- `payment_gateway_transactions`
- Location columns on `customers`, `vendors`, and `orders`

## Routes

Google Maps:
- `POST /integrations/maps/geocode`
- `POST /integrations/maps/reverse-geocode`
- `POST /integrations/maps/distance`

OTP:
- `GET /login/otp`
- `POST /login/otp`
- `POST /login/otp/resend`
- `POST /email/verification-otp`
- `POST /email/verification-otp/verify`

PayHere:
- `GET /customer/payments/{payment}/payhere`
- `POST /payments/payhere/notify`
- `GET /customer/payments/{payment}/payhere/return`
- `GET /customer/payments/{payment}/payhere/cancel`

## Testing Steps

1. Run `php artisan migrate`.
2. Set SMTP values in `.env`, then run `php artisan config:clear`.
3. Register a customer and confirm that the welcome email is sent.
4. Set `OTP_LOGIN_ENABLED=true`, log out, log in again, and verify the OTP challenge.
5. Add Google Maps keys and open `/customer/checkout`; the address picker map should appear.
6. Use the Maps distance endpoint with two coordinates and confirm a JSON response.
7. Configure PayHere sandbox credentials.
8. Place an order using Card Payment.
9. Open the payment page and click Pay with PayHere.
10. Confirm the PayHere sandbox notification updates the payment status to paid or failed.
