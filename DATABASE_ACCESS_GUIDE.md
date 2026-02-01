# 🗄️ Database Access Guide for Local Development

## 📊 Current Database Status:
- **Categories**: ✅ 34 rows (hierarchical structure with parent_id)
- **SubCategories**: ❌ 0 rows (empty table)
- **Connection**: Working correctly

## 🛠 Ways to Access Database:

### **Method 1: Command Line (Fastest)**
```bash
# Connect to PostgreSQL container
docker exec -it dream-pack-ecommerce-db-1 psql -U laravel -d laravel

# Basic commands
\dt                    -- List tables
\d categories           -- Describe categories table
\d sub_categories       -- Describe sub_categories table
SELECT * FROM categories LIMIT 10;
SELECT * FROM sub_categories;
```

### **Method 2: GUI Tool (pgAdmin)**
**1. Install pgAdmin** (recommended):
- **Docker**: `docker run -p 5050:80 dpage/pgadmin4`
- **Desktop**: Download from pgadmin.org
- **VS Code**: PostgreSQL extension

**2. Connection Settings:**
```
Host: localhost:5433
Database: laravel
Username: laravel
Password: secret
```

### **Method 3: VS Code Extension**
- Install: "PostgreSQL" by Chris Kolkman
- Extension name: "postgresql-client"
- Use same connection settings

### **Method 4: TablePlus/DBeaver**
- Host: localhost
- Port: 5433
- Database: laravel
- User: laravel
- Password: secret

## 📋 Key Tables Structure:

### **categories** (34 rows)
```sql
- id (bigint, primary key)
- parent_id (bigint, nullable) -- Self-referencing for hierarchy
- name (varchar)
- slug (varchar, unique)
- description (text, nullable)
- sort_order (integer)
- meta_title (varchar, nullable)
- meta_description (text, nullable)
- is_active (boolean, default true)
- timestamps
- softDeletes
```

### **sub_categories** (0 rows)
```sql
- id (bigint, primary key)
- name (varchar)
- slug (varchar, unique)
- description (text, nullable)
- category_id (bigint, foreign key to categories.id)
- sort_order (integer)
- meta_title (varchar, nullable)
- meta_description (text, nullable)
- is_active (boolean, default true)
- timestamps
- softDeletes
```

## 🐛 Issue Found:
**Why SubCategory dropdown appears empty:**
1. `sub_categories` table has **0 records**
2. Some Filament features need existing data to work properly
3. The Parent Category dropdown expects to show **categories** for selection, not sub_categories

## 🔧 Fix Options:

### **Option 1: Add Test SubCategories**
```sql
INSERT INTO sub_categories (name, slug, category_id, sort_order, is_active, created_at, updated_at) VALUES
('Summer Collection', 'summer-collection', 1, 1, true, NOW(), NOW()),
('Winter Collection', 'winter-collection', 6, 2, true, NOW(), NOW()),
('Kids Special', 'kids-special', 10, 3, true, NOW(), NOW());
```

### **Option 2: Use Existing Categories**
Your current setup has:
- **Hierarchical categories** (parent-child relationships)
- **Flat sub_categories** linking to parent categories

**Recommendation**: Use categories table for both - it already has 34 rows with hierarchy!

## 🎯 Next Steps:
1. Try accessing database with any method above
2. Check the Parent Category dropdown again at: http://localhost:10000/admin/sub-categories/create
3. The hardcoded options should show up now