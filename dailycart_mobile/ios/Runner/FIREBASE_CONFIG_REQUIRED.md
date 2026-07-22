# Firebase role files

Download one iOS configuration for each final Firebase app and place it at:

- `Firebase/customer/GoogleService-Info.plist` for `com.dailycart.customer`
- `Firebase/vendor/GoogleService-Info.plist` for `com.dailycart.vendor`
- `Firebase/rider/GoogleService-Info.plist` for `com.dailycart.rider`

The Xcode `Copy Role Firebase Configuration` build phase selects the file using
`DAILYCART_FLAVOR`. Never copy one role's configuration into another role.
