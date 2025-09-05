# 🔐 GitHub Secrets Setup Guide

## **Why Use Secrets?**
- **Security**: FTP credentials are hidden from public view
- **Safety**: No risk of accidentally committing passwords
- **Professional**: Industry standard for CI/CD

## **How to Set Up GitHub Secrets**

### **Step 1: Go to Your GitHub Repository**
1. **Navigate** to your RapidIndexer repository on GitHub
2. **Click** on "Settings" tab (top right)
3. **Click** on "Secrets and variables" → "Actions"

### **Step 2: Add Repository Secrets**
Click "New repository secret" and add these **3 secrets**:

#### **Secret 1: FTP_SERVER**
- **Name**: `FTP_SERVER`
- **Value**: `82.197.89.47`

#### **Secret 2: FTP_USERNAME**
- **Name**: `FTP_USERNAME`
- **Value**: `u906310247.Testing123`

#### **Secret 3: FTP_PASSWORD**
- **Name**: `FTP_PASSWORD`
- **Value**: `/Nz!JdGZufu[dV3b`

### **Step 3: Verify Secrets**
After adding all 3 secrets, you should see:
- ✅ `FTP_SERVER`
- ✅ `FTP_USERNAME`
- ✅ `FTP_PASSWORD`

## **🔧 Alternative: Environment Variables**

If you prefer, you can also set these as environment variables in your workflow:

```yaml
env:
  FTP_SERVER: ${{ secrets.FTP_SERVER }}
  FTP_USERNAME: ${{ secrets.FTP_USERNAME }}
  FTP_PASSWORD: ${{ secrets.FTP_PASSWORD }}
```

## **🚀 Testing the Deployment**

### **After Setting Up Secrets:**
1. **Commit and push** your changes
2. **Check GitHub Actions** tab
3. **Watch the deployment** run
4. **Verify** files are uploaded to your server

### **If Deployment Fails:**
- **Check the logs** in GitHub Actions
- **Verify secrets** are set correctly
- **Test FTP connection** manually if needed

## **🔒 Security Best Practices**

### **Do:**
- ✅ Use GitHub secrets for all sensitive data
- ✅ Rotate passwords regularly
- ✅ Use strong, unique passwords
- ✅ Limit access to repository secrets

### **Don't:**
- ❌ Commit passwords to code
- ❌ Share secrets in chat/email
- ❌ Use weak passwords
- ❌ Leave secrets in public repositories

## **📋 Quick Checklist**

- [ ] **Go to GitHub repository Settings**
- [ ] **Navigate to Secrets and variables → Actions**
- [ ] **Add FTP_SERVER secret**
- [ ] **Add FTP_USERNAME secret**
- [ ] **Add FTP_PASSWORD secret**
- [ ] **Commit and push changes**
- [ ] **Test deployment**

## **🆘 Troubleshooting**

### **Common Issues:**

#### **"530 Login incorrect"**
- **Check username/password** in secrets
- **Verify FTP server** address
- **Test connection** manually

#### **"Failed to connect"**
- **Check server IP** and port
- **Verify protocol** (FTP vs SFTP)
- **Check firewall** settings

#### **"Permission denied"**
- **Verify server directory** path
- **Check user permissions**
- **Ensure directory exists**

---

**Once you've set up the secrets, your deployment will be secure and automated!** 🎉
