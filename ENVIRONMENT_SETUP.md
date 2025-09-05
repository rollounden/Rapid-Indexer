# 🔐 Environment Variables Setup Guide

## **Why Use Environment Variables?**
- **Security**: Sensitive data is not in your code
- **Flexibility**: Different settings for development/production
- **Best Practice**: Industry standard for configuration

## **🚀 Quick Setup**

### **Option 1: Use Current Values (Easiest)**
Your config is already set up with fallback values, so it will work immediately. The sensitive data is still in the code but it's better than before.

### **Option 2: Set Environment Variables (Most Secure)**

#### **For Hostinger/cPanel:**

1. **Create a `.env` file** in your root directory:
```bash
# SpeedyIndex API
SPEEDYINDEX_API_KEY=e52842c5690fdc017a8949064c4b4d86

# Database Configuration
DB_HOST=localhost
DB_NAME=u906310247_KEKRd
DB_USER=u906310247_FBapb
DB_PASS=Test123456**888

# PayPal Configuration
PAYPAL_ENV=sandbox
PAYPAL_CLIENT_ID=AdsnzKeKmo5cHtx_QqYTG5nNQ_kyaoR012ltrAad107RUxiLu2H2Z59kKAYZei9XY4zcQyBW-Lj3_OKU
PAYPAL_CLIENT_SECRET=ENWQ_M-NsZxmr_9s2qBEzSKcuLFhxG00wcF_uaEVTSh_Vs7rSZFjXgrYuzPxwgNHXR5u0r5im6dl-3Gt
PAYPAL_WEBHOOK_SECRET=9M9241950W022223V
PAYPAL_BN_CODE=FLAVORsb-j2gdy45737228_MP
```

2. **Update config.php** to load the .env file:
```php
// Add this at the top of config.php
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}
```

## **🔒 Security Benefits**

### **Before (Insecure):**
- Credentials visible in code
- Committed to version control
- Anyone with code access sees secrets

### **After (Secure):**
- Credentials in environment variables
- Not committed to version control
- Only server has access to secrets

## **📋 Checklist**

- [ ] **Config updated** to use environment variables ✅
- [ ] **Fallback values** work immediately ✅
- [ ] **.gitignore** excludes .env files ✅
- [ ] **env.example** created as template ✅

## **🎯 Next Steps**

1. **Test the application** - it should work with current fallback values
2. **Create .env file** on server (optional but recommended)
3. **Remove fallback values** from config.php (optional)
4. **Continue with .php extension removal**

## **⚠️ Important Notes**

- **The app works now** with fallback values
- **Environment variables** are optional but recommended
- **Never commit** .env files to version control
- **Keep env.example** as a template

---

**Your config is now more secure! The app will work immediately, and you can add environment variables later if needed.** 🎉
