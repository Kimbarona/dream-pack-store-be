# 🚀 Render Deployment Seeding Guide

## 📋 Current Behavior Analysis

### **Default Render Deployment**
- ✅ **Migrations**: Always run (`php artisan migrate --force`)
- ❌ **Seeders**: Currently **NEVER** run (no `SEED_ADMIN` env var in render.yaml)

## 🎯 Deployment Options

### **Option 1: Full Seeding (Current + Fix)**
Add to `render.yaml`:
```yaml
envVars:
  - key: SEED_ADMIN
    value: true
```
**Result**: `migrate:fresh` + ALL seeders (Admins, Categories, Products, etc.)

---

### **Option 2: Admin-Only Seeding (New Feature)**
Add to `render.yaml`:
```yaml
envVars:
  - key: SEED_ADMIN_ONLY
    value: true
```
**Result**: `migrate:fresh` + AdminOnlySeeder (Admin accounts only)

---

### **Option 3: Fresh Migration Only**
Add to `render.yaml`:
```yaml
envVars:
  - key: SEED_ADMIN
    value: false
```
**Result**: `migrate` only (no seeders)

---

## 📧 Admin Credentials

When admin seeding runs, these accounts are created:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@dreampack.com | superadmin123 |
| Admin | admin@dreampack.com | admin123 |
| Co-Admin | coadmin@dreampack.com | coadmin123 |

**⚠️ Change passwords in production!**

## 🛠 Local Development

### **Full Seeding (Current)**
```bash
# Already configured in docker-compose.yml
SEED_ADMIN=true docker-compose up -d
```

### **Admin-Only Seeding (New)**
```bash
# Use the new option
SEED_ADMIN_ONLY=true docker-compose up -d
```

### **No Seeding**
```bash
# Skip all seeding
unset SEED_ADMIN SEED_ADMIN_ONLY docker-compose up -d
```

## 🔄 Database Reset Scenarios

| Scenario | Command | Result |
|----------|----------|---------|
| Production deployment | `SEED_ADMIN_ONLY=true` | Fresh DB + Admins only |
| Staging deployment | `SEED_ADMIN=true` | Fresh DB + All seeders |
| Empty production | `SEED_ADMIN=false` | Migrations only |

## 📝 Files Modified

1. `entrypoint.sh` - Added AdminOnlySeeder support
2. `database/seeders/AdminOnlySeeder.php` - Admin accounts only
3. This documentation

Use the appropriate environment variable based on your deployment needs!