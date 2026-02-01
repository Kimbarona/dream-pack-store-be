# 🖼️ Product API Image Paths & Storage Guide

## ✅ **API Response Analysis**

Your `/api/products` endpoint **DOES include image paths**:

### **Image Fields in API:**

#### **1. Product List (`/api/products`):**
```json
{
  "featured_image": "/storage/products/01KGBWW7K0AN0XHW2BZXVS0RZC.avif",
  "in_stock": true,
  "on_sale": true,
  // ... other fields
}
```

#### **2. Product Detail (`/api/products/{slug}`):**
```json
{
  "featured_image": "/storage/products/01KGBWW7K0AN0XHW2BZXVS0RZC.avif",
  "images": [
    {
      "id": 37,
      "url": "/storage/products/01KGBWW7K0AN0XHW2BZXVS0RZC.avif",
      "path": "products/01KGBWW7K0AN0XHW2BZXVS0RZC.avif",
      "alt_text": "bag",
      "sort_order": 1,
      "is_featured": true
    }
  ],
  // ... other fields
}
```

---

## 🗄️ **Image Storage Locations**

### **📂 Directory Structure:**
```
storage/app/public/products/
├── 1/
├── 2/
├── 3/
├── 4/
├── 5/
│   └── gallery/
│       ├── 697a06d15e86f_product_1.jpg
│       ├── 697a03b2dc67f_product_2.jpg
│       ├── 697a06d15b901_product_3.jpg
│       └── ... more images
├── 6/
├── 7/
├── 8/
├── 9/
├── 10/
└── 01KGBWW7K0AN0XHW2BZXVS0RZC.avif  (Featured image)
```

### **📁 Key Paths:**
- **Storage Root**: `storage/app/public/products/`
- **Web Access**: `http://localhost:10000/storage/products/`
- **Full URL**: `http://localhost:10000/storage/products/01KGBWW7K0AN0XHW2BZXVS0RZC.avif`

---

## 🔧 **Image Path Generation**

### **How URLs are Created:**

#### **1. ProductImage Model (`app/Models/ProductImage.php`):**
```php
public function getUrlAttribute(): string
{
    return Storage::url($this->path);
}
```

#### **2. Storage Configuration (`config/filesystems.php`):**
```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
    'visibility' => 'public',
],
```

#### **3. Featured Image Access (`app/Models/Product.php`):**
```php
public function getFeaturedImageUrlAttribute(): ?string
{
    $featuredImage = $this->images()->where('is_featured', true)->first();
    if (!$featuredImage) {
        $featuredImage = $this->images()->first();
    }
    return $featuredImage ? $featuredImage->url : null;
}
```

---

## 🎯 **API Testing Commands**

### **List All Products:**
```bash
curl http://localhost:10000/api/products
```

### **Product Detail with Images:**
```bash
curl http://localhost:10000/api/products/leather-bag
```

### **Filter by Category:**
```bash
curl "http://localhost:10000/api/products?category=clothing"
```

### **Search Products:**
```bash
curl "http://localhost:10000/api/products?q=bag"
```

---

## 📂 **Finding Images on Your System**

### **In Docker Container:**
```bash
docker exec dream-pack-ecommerce-app-1 ls -la storage/app/public/products/
```

### **On Your Local Machine:**
**If using Docker volumes:**
- Images are stored inside the Docker container
- Access via: `docker exec dream-pack-ecommerce-app-1`

**If you mounted volume locally:**
- Check your project folder: `./storage/app/public/products/`

---

## 🌐 **Web Access to Images**

### **Full URL Format:**
```
http://localhost:10000/storage/products/{filename}
```

### **Example:**
```
http://localhost:10000/storage/products/01KGBWW7K0AN0XHW2BZXVS0RZC.avif
```

### **Gallery Images:**
```
http://localhost:10000/storage/products/5/gallery/697a06d15e86f_product_1.jpg
```

---

## 🔧 **File Types Supported**

From analysis, your system stores:
- **AVIF** (modern format, primary)
- **JPG** (fallback/legacy format)
- **WEBP** (optimized format)
- **PNG** (transparent backgrounds)

### **Current Example:**
- **Featured**: `01KGBWW7K0AN0XHW2BZXVS0RZC.avif`
- **Gallery**: `697a06d15e86f_product_1.jpg` (multiple variants)

---

## 📊 **Summary**

✅ **YES**, the API includes image paths:
- **featured_image**: Main product image URL
- **images[]**: Array of all product images with full details
- **Path included**: Both full URL and relative path available
- **Web accessible**: Images available via `/storage/` URL prefix

**Images are stored in**: `storage/app/public/products/`
**Web accessible via**: `http://localhost:10000/storage/products/`