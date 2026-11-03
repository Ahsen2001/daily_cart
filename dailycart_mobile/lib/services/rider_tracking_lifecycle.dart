class RiderTrackingLifecycle {
  static const trackableStatuses = {'accepted', 'picked_up', 'on_the_way'};

  static const terminalStatuses = {
    'delivered',
    'failed',
    'cancelled',
    'reassigned',
  };

  static bool shouldTrack(String status) {
    return trackableStatuses.contains(status.toLowerCase());
  }

  static bool shouldStopForStatus(String status) {
    return terminalStatuses.contains(status.toLowerCase());
  }

  static bool shouldStopForLogout() => true;
}
