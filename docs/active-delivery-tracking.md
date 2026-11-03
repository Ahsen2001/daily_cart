# Active delivery tracking storage and retention

## Storage mapping

- `delivery_tracking_states` owns the short-lived tracking authorization token and lifecycle state.
- `delivery_current_locations` contains exactly one fast current-location row per delivery. A row is updated only when a point has a newer `recorded_at`, or the same timestamp with a higher `sequence_number`.
- The existing `rider_locations` table is the append-only `rider_location_history`. It was extended additively with `point_uuid`, `received_at`, `sequence_number`, and nullable timestamps so existing rows and relationships remain intact. Its existing `accuracy` column represents `accuracy_meters` and is not duplicated.

Coordinates are returned only while both the delivery and tracking session are active. Current and historical records remain private after delivery completion, cancellation, failure, reassignment, or authorization loss.

## Idempotency and reconnects

The Rider app assigns every point a UUID before writing it to its durable local queue. The backend has a unique index on `rider_locations.point_uuid`. Batch retries acknowledge already-existing UUIDs as duplicates and never create a second history row.

The Rider queue sends the newest point first so the live map recovers immediately, then sends older points for support and analytics. The local queue keeps at most 500 valid points and discards only points older than the server's 24-hour reconnect window.

## Retention strategy

Recommended production retention for `rider_locations` is 60 days, within the requested 30–90 day range. Before enabling deletion, confirm legal, dispute, and analytics requirements and archive any records that must be retained longer. No automatic database pruning is enabled by this implementation, so existing data is never deleted without a separate reviewed operational decision.

`delivery_current_locations` should be retained with the delivery record for audit purposes. It is hidden by authorization and lifecycle checks rather than deleted when tracking stops.

## Private real-time broadcasting

Accepted points that advance `delivery_current_locations` dispatch `DeliveryLocationUpdated` after the database transaction commits. The event uses the private channel `delivery.{deliveryId}` and the event name `delivery.location.updated`. Invalid, duplicate, rejected, and older history points do not dispatch an event.

Channel authorization permits only the assigned Rider, owning Customer, order Vendor, Admin, and Super Admin. The `/broadcasting/auth` route uses `auth:sanctum`, which supports both the authenticated web session and mobile Bearer tokens.

Laravel Reverb and the Laravel Echo/Pusher-protocol browser client are installed. Local development uses `BROADCAST_CONNECTION=reverb`, Reverb on port `8080`, and the existing `composer run dev` command starts the Laravel server, queue listener, Vite, logs, and Reverb together. Web pages prefer the private Echo channel and fall back to the authorized SSE stream and 15–30 second polling if WebSockets are unavailable. Mobile continues to use the authorized SSE stream with polling until a provider-specific Flutter WebSocket client is configured.

For production, set `REVERB_ALLOWED_ORIGINS` to the exact DailyCart web origin, expose Reverb through an HTTPS reverse proxy using `wss`, and keep `php artisan reverb:start` under a process manager. Reverb is a long-running process and must be restarted after deployments with `php artisan reverb:restart`.
