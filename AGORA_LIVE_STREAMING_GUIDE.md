# Agora Live Streaming for Auction System - Complete Guide

## ✅ What Has Been Implemented

Your auction system now includes **Agora real-time live streaming** integration. Here's what's ready:

### 1. Backend Implementation ✓
- **AgoraService** (`app/Services/AgoraService.php`) - Generates tokens and manages streaming
- **Database Migration** - Added Agora fields to `live_videos` table
- **API Endpoints** - Updated to return Agora credentials
- **API Resources** - Return Agora configuration in responses

---

## 📱 How It Works

### **For Auction Creators (Broadcasters)**

1. **Create Auction** → Call `POST /api/user/live/add`
   - Backend generates unique Agora channel
   - Returns `agora_app_id`, `channel_name`, and `token_publisher`

2. **Start Live Stream**
   - Mobile app uses Agora SDK to broadcast
   - Uses the `token_publisher` to start streaming

3. **Viewers Join**
   - Call `POST /api/user/live/add-view/{id}` to track viewers
   - Backend returns `token_subscriber` for watching

---

## 🔧 Setup Instructions

### Step 1: Get Agora Credentials

1. Go to [Agora Console](https://console.agora.io/)
2. Create a new project or use existing one
3. Get your **App ID** and **App Certificate**

### Step 2: Configure Environment

Add to your `.env` file:

```env
AGORA_APP_ID=your_agora_app_id_here
AGORA_APP_CERTIFICATE=your_agora_app_certificate_here
```

### Step 3: Run Migration (if not done)

```bash
php artisan migrate --path="database/migrations/New folder/2025_10_16_000001_add_streaming_fields_to_live_videos_table.php"
```

---

## 📡 API Endpoints

### 1. Create Live Auction
```http
POST /api/user/live/add
Authorization: Bearer {token}

Body:
{
    "title": "Auction Title",
    "title_ar": "عنوان المزاد",
    "information": "Auction info",
    "information_ar": "معلومات المزاد",
    "date_start_at": "2025-01-01",
    "date_end_at": "2025-01-02",
    "time_start_at": "10:00",
    "time_end_at": "12:00",
    "terms_conditions": "Terms",
    "terms_conditions_ar": "الشروط",
    "image[]": [files]
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Auction Title",
        "status": "pending",
        "agora": {
            "app_id": "your_app_id",
            "channel_name": "auction_1234567890",
            "token_publisher": "publisher_token_here",
            "token_subscriber": "subscriber_token_here",
            "uid": 123
        }
    }
}
```

### 2. Start Live Stream
```http
POST /api/user/live/start/{id}
Authorization: Bearer {token}
```

Changes status to `start` and sends notifications to all users.

### 3. Get Live Video Details
```http
GET /api/user/live/video/{id}
Authorization: Bearer {token}
```

Returns full auction details including Agora credentials for viewers.

### 4. Refresh Agora Token
```http
GET /api/user/live/refresh-token/{id}
Authorization: Bearer {token}
```

Refreshes tokens if they expire during long streams (tokens valid for 24 hours).

### 5. End Live Stream
```http
POST /api/user/live/end/{id}
Authorization: Bearer {token}
```

Ends the auction and removes Firebase live data.

---

## 📱 Mobile App Integration

### For Android (Kotlin/Java)

1. **Add Agora SDK to `build.gradle`:**
```gradle
dependencies {
    implementation 'io.agora.rtc:full-sdk:4.2.3'
}
```

2. **Start Broadcasting:**
```kotlin
// Initialize Agora Engine
val rtcEngine = RtcEngine.create(context, agoraAppId, object : IRtcEngineEventHandler() {
    override fun onJoinChannelSuccess(channel: String, uid: Int, elapsed: Int) {
        // Successfully joined
    }
})

// Enable video
rtcEngine.enableVideo()
rtcEngine.setChannelProfile(Constants.CHANNEL_PROFILE_LIVE_BROADCASTING)
rtcEngine.setClientRole(Constants.CLIENT_ROLE_BROADCASTER)

// Join channel
rtcEngine.joinChannel(
    tokenPublisher,
    channelName,
    "",
    uid
)
```

3. **View Stream (Audience):**
```kotlin
rtcEngine.setClientRole(Constants.CLIENT_ROLE_AUDIENCE)
rtcEngine.joinChannel(tokenSubscriber, channelName, "", 0)
```

### For iOS (Swift)

1. **Add Agora SDK via CocoaPods:**
```ruby
pod 'AgoraRtcEngine_iOS'
```

2. **Start Broadcasting:**
```swift
let agoraKit = AgoraRtcEngineKit.sharedEngine(withAppId: agoraAppId, delegate: self)
agoraKit.enableVideo()
agoraKit.setChannelProfile(.liveBroadcasting)
agoraKit.setClientRole(.broadcaster)

agoraKit.joinChannel(byToken: tokenPublisher, 
                     channelId: channelName, 
                     info: nil, 
                     uid: uid) { (channel, uid, elapsed) in
    // Successfully joined
}
```

---

## 🔐 Security Features

1. **Token-Based Authentication** - Each stream requires valid Agora token
2. **24-Hour Token Expiration** - Tokens auto-expire for security
3. **Role-Based Access** - Publisher vs Subscriber tokens
4. **Unique Channels** - Each auction has unique channel name

---

## 🎯 Workflow Example

### Complete Auction Flow

1. **Auctioneer creates auction**
   ```
   Mobile App → POST /api/user/live/add
   Response: Agora credentials (app_id, channel, tokens)
   ```

2. **Auctioneer starts stream**
   ```
   Mobile App → POST /api/user/live/start/{id}
   App initializes Agora SDK with token_publisher
   Goes live on Agora channel
   ```

3. **Viewers join**
   ```
   Mobile App → GET /api/user/live/video/{id}
   Response: Agora credentials with token_subscriber
   App joins Agora channel as audience
   ```

4. **Users place bids**
   ```
   During live stream → POST /api/video/auctions/add
   Bids stored in database
   Real-time bid updates via Firebase
   ```

5. **Auction ends**
   ```
   POST /api/user/live/end/{id}
   Highest bidder wins
   Stream ends
   ```

---

## 🧪 Testing Checklist

- [ ] Configure `AGORA_APP_ID` and `AGORA_APP_CERTIFICATE` in `.env`
- [ ] Run migration to add Agora fields
- [ ] Create test auction via API
- [ ] Verify Agora credentials in response
- [ ] Test mobile app with Agora SDK
- [ ] Test broadcaster can go live
- [ ] Test viewers can watch
- [ ] Test token refresh if needed
- [ ] Test auction bidding during live stream
- [ ] Test ending stream

---

## 📚 Resources

- **Agora Documentation**: https://docs.agora.io/
- **Agora Console**: https://console.agora.io/
- **Android SDK Guide**: https://docs.agora.io/en/voice-calling/get-started/get-started-sdk?platform=android
- **iOS SDK Guide**: https://docs.agora.io/en/voice-calling/get-started/get-started-sdk?platform=ios
- **Token Generator**: https://webdemo.agora.io/token_builder

---

## 🐛 Troubleshooting

### Token Invalid Error
- Check `AGORA_APP_ID` and `AGORA_APP_CERTIFICATE` in `.env`
- Ensure tokens haven't expired (24 hours)
- Call `/refresh-token` endpoint to get new tokens

### Stream Not Starting
- Verify Agora SDK initialization in mobile app
- Check network connectivity
- Ensure correct `channel_name` and `token_publisher`

### Viewers Can't Join
- Verify using `token_subscriber` (not publisher token)
- Check correct `channel_name`
- Ensure auction status is `start`

---

## 💡 Next Steps

1. **Install Agora SDK** in your mobile app (Android/iOS)
2. **Add environment variables** to `.env`
3. **Test with Agora sample app** first
4. **Integrate with your UI** for broadcasting and viewing
5. **Add real-time bid updates** using Firebase
6. **Implement recording** (optional) for playback later

---

**Need Help?**
- Agora Community: https://www.agora.io/en/community/
- Agora Support: https://console.agora.io/support

---

## 📦 What's Included

✅ Agora token generation
✅ Unique channel creation per auction
✅ Publisher and subscriber tokens
✅ Token refresh endpoint
✅ Database fields for Agora credentials
✅ API resources updated with Agora data
✅ 24-hour token expiration
✅ Integration with existing auction system

Your system is now ready for live streaming auctions with Agora! 🎉

