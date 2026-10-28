# Android setup (android/app/src/main/AndroidManifest.xml, inside <manifest>)
<uses-permission android:name="android.permission.CAMERA"/>
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION"/>
<uses-permission android:name="android.permission.POST_NOTIFICATIONS"/>
<uses-permission android:name="android.permission.USE_FULL_SCREEN_INTENT"/>
<queries>
  <intent><action android:name="android.intent.action.VIEW"/><data android:scheme="https"/></intent>
  <intent><action android:name="android.intent.action.DIAL"/><data android:scheme="tel"/></intent>
  <intent><action android:name="android.intent.action.SENDTO"/><data android:scheme="sms"/></intent>
</queries>
# mobile_scanner needs minSdkVersion 23 in android/app/build.gradle
# iOS Info.plist: NSCameraUsageDescription, NSLocationWhenInUseUsageDescription, LSApplicationQueriesSchemes (comgooglemaps, waze)
