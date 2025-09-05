# 🚀 RapidIndexer Go-Live Checklist

## ✅ **CRON JOBS SETUP (CRITICAL)**

### **What Cron Jobs Do:**
- **Auto-sync tasks** with SpeedyIndex API
- **Update task statuses** automatically
- **Process completed tasks** without manual intervention

### **Required Cron Jobs:**

#### **1. Checker Tasks Sync (Every 15 seconds)**
```bash
* * * * * /usr/bin/php /home/u906310247/domains/rapid-indexer.com/public_html/auto_checker_sync.php
* * * * * sleep 15; /usr/bin/php /home/u906310247/domains/rapid-indexer.com/public_html/auto_checker_sync.php
* * * * * sleep 30; /usr/bin/php /home/u906310247/domains/rapid-indexer.com/public_html/auto_checker_sync.php
* * * * * sleep 45; /usr/bin/php /home/u906310247/domains/rapid-indexer.com/public_html/auto_checker_sync.php

```

#### **2. Indexer Tasks Sync (Every 2 minutes)**
```bash
*/2 * * * * /usr/bin/php /home/u906310247/domains/rapid-indexer.com/public_html/auto_task_sync.php
```

### **How to Set Up Cron Jobs in Hostinger:**

1. **Log into Hostinger cPanel**
2. **Go to "Cron Jobs" section**
3. **Add each cron job** with the commands above
4. **Set frequency** as specified
5. **Save all cron jobs**


done
---

## 🔧 **CONFIGURATION FIXES**.

### **1. Make Config Private**
- **Move sensitive data** to environment variables
- **Hide API keys** from public access
- **Secure database credentials**

### **2. Remove .php Extensions**
- **Set up URL rewriting** in .htaccess
- **Remove .php** from all URLs
- **Update internal links**

---

## 🌐 **PAYPAL INTEGRATION**

### **Update PayPal Webhook:**
- **Current:** `https://cyan-peafowl-394593.hostingersite.com/paypal_webhook.php`
- **New:** `https://rapid-indexer.com/paypal_webhook.php` 


### **Steps:**
1. **Go to PayPal Developer Dashboard**
2. **Find your webhook configuration**
3. **Update URL** to new domain
4. **Test webhook** to ensure it works

done
---

## 🔒 **SECURITY & PRODUCTION**

### **Environment Variables:**
- **Move API keys** to server environment
- **Hide database credentials**
- **Secure PayPal secrets**

### **SSL Certificate:**
- **Ensure HTTPS** is working
- **Force HTTPS** redirects
- **Update all URLs** to use HTTPS

### **Error Handling:**
- **Hide PHP errors** in production
- **Set up proper logging**
- **Add error pages** (404, 500)

---

## 📊 **MONITORING & MAINTENANCE**

### **Log Files:**
- **Check log files** regularly
- **Monitor cron job execution**
- **Track API usage**

### **Database:**
- **Regular backups**
- **Monitor performance**
- **Check for errors**

---

## 🧪 **TESTING CHECKLIST**

### **Before Going Live:**
- [ ] **Test PayPal payments** (sandbox)
- [ ] **Test task creation** and syncing
- [ ] **Verify cron jobs** are running
- [ ] **Check all pages** load correctly
- [ ] **Test user registration/login**
- [ ] **Verify email display** in navbar
- [ ] **Test task results** viewing

### **After Going Live:**
- [ ] **Test real PayPal payments**
- [ ] **Monitor task processing**
- [ ] **Check webhook delivery**
- [ ] **Verify auto-sync** is working
- [ ] **Test all user flows**

---

## 🚨 **CRITICAL ITEMS**

### **Must Fix Before Launch:**
1. **Set up cron jobs** (tasks won't auto-sync without this) done
2. **Update PayPal webhook** (payments won't work) done
3. **Make config private** (security risk) not done
4. **Remove .php extensions** (professional URLs) not done

### **Nice to Have:**
- **SSL certificate** (already should work) done
- **Error pages** (404, 500) not done
- ** a home page or landing page** not done
- no more test pages
- **Monitoring setup**
- **Backup strategy**

---

## 📞 **SUPPORT**

If you need help with any of these items, let me know! The cron jobs are the most critical - without them, your tasks won't automatically sync with SpeedyIndex.
