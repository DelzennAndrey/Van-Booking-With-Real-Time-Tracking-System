// app.config.js - Dynamic configuration for Expo
// This allows environment-based configuration

export default {
  expo: {
    name: "SMTSC Booking",
    slug: "smtsc-booking",
    scheme: "vanbooking",
    plugins: ["expo-font"],
    android: {
      package: "com.vanbooking.capstone",
      intentFilters: [
        {
          action: "VIEW",
          category: ["BROWSABLE", "DEFAULT"],
          data: [
            {
              scheme: "vanbooking",
              host: "pay",
              pathPrefix: "/success"
            },
            {
              scheme: "vanbooking",
              host: "pay",
              pathPrefix: "/cancel"
            }
          ]
        }
      ]
    },
    ios: {
      bundleIdentifier: "com.vanbooking.capstone",
      infoPlist: {
        CFBundleURLTypes: [
          {
            CFBundleURLSchemes: ["vanbooking"]
          }
        ]
      }
    },
    extra: {
      eas: {
        projectId: "496546c0-243a-4492-98e4-d46f7b965a28"
      },
      // API URL - can be overridden by environment variables
      // For development: use your local IP (e.g., http://10.32.61.168/app-mobile/capstone/backend/api)
      // For production: use your hosted domain
      apiUrl: process.env.EXPO_PUBLIC_API_URL || "https://smtsc-booking.proplocator.online/backend/api"
    },
    owner: "samzkie123"
  }
};

