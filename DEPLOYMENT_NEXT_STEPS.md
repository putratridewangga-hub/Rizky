# ✅ DEPLOYMENT CHECKLIST - POST GIT PUSH

## 🎯 LANGKAH-LANGKAH SELANJUTNYA

### **PHASE 1: VERIFY GIT PUSH** ✅
- [x] Push ke GitHub berhasil
- [ ] Verify api.env NOT ada di GitHub (cek di repo)
  - Buka: https://github.com/YOUR_USERNAME/Rizky
  - Search file: api.env (seharusnya NOT ada)
  - Jika ada, bersihkan dengan git rm --cached api.env

---

### **PHASE 2: SETUP RAILWAY** (5-10 menit)

#### **Step 1: Connect Railway ke GitHub**
1. Login ke: https://railway.app
2. Create New Project → Import from GitHub
3. Select repository: YOUR_GITHUB_REPO/Rizky
4. Select branch: main (atau branch yang Anda gunakan)
5. Klik "Deploy"
6. Tunggu deployment selesai (~2-5 menit)

#### **Step 2: Set Environment Variables di Railway**
1. Di Railway Dashboard, select your app
2. Click **Settings** tab
3. Ke **Variable Reference** atau **Environment**
4. Tambahkan variables:

```
# Database (jika belum ada dari Railway MySQL)
DB_HOST=your-railway-mysql-host
DB_PORT=3306
DB_NAME=db_booking_foto
DB_USER=root
DB_PASSWORD=your_password

# Gemini API
GEMINI_API_KEY=AIzaSyATrKU27lWm-4BbWy0wJURkIGWrBmxPH5A

# Optional Railway Settings
ENABLE_OUTGOING_NETWORK=true
GEMINI_DISABLE_SSL_VERIFY=false
```

5. Click **Save** atau **Deploy**

#### **Step 3: Wait for Deployment**
- Railway akan auto-redeploy dengan environment variables baru
- Tunggu sampai status: "✅ Online"
- Lihat deployment logs

---

### **PHASE 3: VERIFY PRODUCTION** (5 menit)

#### **Test 1: Check API Diagnostic**
```
https://your-railway-app.railway.app/test_gemini_connection.php
```

Expected result:
- ✅ Test 1: API Key Configuration PASS
- ✅ Test 2: DNS Resolution PASS
- ✅ Test 3: Network Connectivity PASS
- ✅ Test 4: API Key Config PASS
- ✅ Test 5: Actual Gemini API Call PASS

**If any test FAILS:**
- Check Environment Variables di Railway
- Verify API_KEY sudah di-set
- Check server logs di Railway dashboard

#### **Test 2: Test AI Recommendation Feature**
1. Buka: `https://your-railway-app.railway.app/booking.php`
2. Login sebagai customer
3. Klik "Rekomendasi AI"
4. Isi form:
   - Jenis Acara: Prewedding
   - Jumlah Orang: 2
   - Lokasi: Outdoor
   - Gaya: Romantic
5. Klik "Dapatkan Rekomendasi AI"
6. **Expected:** Recommendation muncul dengan paket terbaik

**If Error:**
- Check browser console (F12)
- Check Railway logs
- Run test_gemini_connection.php untuk diagnostik

#### **Test 3: Check Logs**
Di Railway Dashboard:
1. Select your app
2. Click **Logs**
3. Lihat untuk errors atau warnings
4. Search: "Gemini API Error" untuk diagnostik

---

### **PHASE 4: MONITORING & MAINTENANCE**

#### **Setup Alerts (Recommended)**
1. Railway Dashboard → Alerts
2. Enable notifications untuk:
   - Deployment failures
   - High memory usage
   - High CPU usage

#### **Monitor Gemini API Usage**
1. Go to: https://console.cloud.google.com/
2. Select project
3. APIs & Services → Quotas
4. Monitor usage untuk Gemini API

#### **Check Monthly Costs**
1. Railway: $5/month (Starter tier)
2. Google Gemini: Free tier atau pay-as-you-go
   - Usual: $0-5/month untuk low traffic

---

### **PHASE 5: GIT WORKFLOW GOING FORWARD**

#### **Local Workflow:**
```bash
# 1. Make changes
nano functions/ai_helper.php

# 2. Test locally
php -S localhost:8000

# 3. Commit (jangan commit api.env!)
git add .
git commit -m "Fix: Improve AI recommendation"
git push origin main

# Railway auto-redeploy ✅
```

#### **If Need to Change API Key:**
```bash
# 1. Update api.env locally
echo "GEMINI_API_KEY=NEW_KEY_HERE" > api.env

# 2. Test locally
http://localhost:8000/test_gemini_connection.php

# 3. DO NOT COMMIT api.env
git push  # api.env already in .gitignore

# 4. Update Railway environment variable manually
# Railway Dashboard → Environment Variables → Edit GEMINI_API_KEY
```

---

## 🔍 TROUBLESHOOTING COMMON ISSUES

### **Issue 1: "API Key Google Gemini belum dikonfigurasi"**
**Solusi:**
1. Verify Environment Variable di Railway di-set
2. Railway → Variables → Check GEMINI_API_KEY exists
3. Re-deploy untuk apply changes: Railway → Deployments → Trigger Deploy

### **Issue 2: "Tidak bisa terhubung ke Gemini API"**
**Solusi:**
1. Railway free tier mungkin block outgoing connections
2. Upgrade ke Starter tier ($5/month)
3. Set `ENABLE_OUTGOING_NETWORK=true` di variables

### **Issue 3: "SSL certificate error"**
**Solusi:**
1. Set `GEMINI_DISABLE_SSL_VERIFY=true` di Railway variables (temporary)
2. Test jika API call works
3. Once working, remove atau set false

### **Issue 4: "502 Bad Gateway" atau app crash**
**Solusi:**
1. Check memory usage: Railway Dashboard → Metrics
2. Check error logs: Railway Dashboard → Logs
3. Upgrade plan jika out of memory

---

## 📊 YOUR PRODUCTION SETUP

```
┌─────────────────────────────────────────┐
│   GitHub Repository                      │
│   ├─ config/db.php (auto-load .env)     │
│   ├─ .gitignore (protect api.env)       │
│   ├─ functions/ai_helper.php             │
│   └─ ... (code files)                    │
│                                          │
│   ❌ api.env (NOT committed)             │
└─────────────────────────────────────────┘
                    ↓ git push
┌─────────────────────────────────────────┐
│   Railway Platform (Production)          │
│   ├─ Auto-deploy dari GitHub             │
│   ├─ Environment Variables:              │
│   │  ├─ GEMINI_API_KEY=***               │
│   │  ├─ DB_HOST, DB_USER, DB_PASS       │
│   │  └─ ENABLE_OUTGOING_NETWORK=true    │
│   ├─ MySQL Database                      │
│   └─ URL: your-app.railway.app          │
└─────────────────────────────────────────┘
                    ↓ HTTPS
┌─────────────────────────────────────────┐
│   Users Access                           │
│   https://your-app.railway.app/booking  │
│   → Click Rekomendasi AI                 │
│   → AI calls Gemini API ✅               │
└─────────────────────────────────────────┘
```

---

## ✅ FINAL CHECKLIST BEFORE GOING LIVE

- [ ] Git push successful
- [ ] api.env NOT visible di GitHub
- [ ] Railway connected to GitHub
- [ ] Deployment completed (✅ Online)
- [ ] Environment variables set di Railway
- [ ] test_gemini_connection.php shows all ✅
- [ ] AI recommendation feature tested
- [ ] Booking workflow tested end-to-end
- [ ] Server logs checked (no errors)
- [ ] Database queries working
- [ ] Payment flow tested
- [ ] Admin dashboard working
- [ ] Alerts/monitoring setup (optional)

---

## 🎉 YOU'RE LIVE!

Setelah semua checklist selesai, aplikasi Anda sudah live di production dengan:
✅ Secure API key management
✅ AI Gemini integration working
✅ Auto-deployment dari GitHub
✅ Auto-scaling dengan Railway
✅ 99.9% uptime guarantee

**Next features bisa dicoba:**
- More AI features (deskripsi paket auto-generate)
- WhatsApp notifications integration
- Email notifications
- Advanced analytics

---

**Questions?** Baca file dokumentasi:
- RAIL_FIREWALL_GUIDE.md - Network troubleshooting
- QUICK_START_AI.md - AI setup guide
- AI_REKOMENDASI_FIX.md - Common fixes
