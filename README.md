# Dream Pack Ecommerce - Laravel + Filament 3

A modern e-commerce platform built with Laravel 11 and Filament 3 admin panel, optimized for Render deployment.

## 🚀 Quick Start

### Local Development with Docker

1. **Clone and setup:**
```bash
git clone <repository-url>
cd dream-pack-ecommerce
```

2. **Start containers:**
```bash
docker-compose up -d
```

3. **Access your application:**
- 🌐 Frontend: http://localhost:10000
- 🔧 Admin Panel: http://localhost:10000/admin
- 🗄️ Database: localhost:5433

### Default Admin Credentials
- **Email:** test@example.com
- **Password:** password

## 📦 Render Deployment

### Automatic Deployment
1. **Push to GitHub** - Connect your repository to Render
2. **Create Web Service** - Use the provided `render.yaml`
3. **Deploy** - Automatic deployment on push to main branch

### Manual Deployment
```bash
./deploy.sh
```

### Environment Variables (Render)
The `render.yaml` includes all necessary environment variables:
- Database connection (PostgreSQL)
- Redis for caching/sessions
- Production optimizations
- Security settings

## 🏗️ Architecture

### Docker Components
- **Dockerfile** - Production-ready PHP 8.4 + FPM + Nginx
- **docker-compose.yml** - Local development setup
- **entrypoint.prod.sh** - Production deployment script
- **.dockerignore** - Optimized build context

### Key Features
- ✅ **Filament 3 Admin Panel** - Modern admin interface
- ✅ **PostgreSQL 16** - Robust database
- ✅ **Redis Caching** - Performance optimization
- ✅ **File Uploads** - Product images, banners
- ✅ **User Management** - Authentication and authorization
- ✅ **Responsive Design** - Mobile-friendly interface

## 🛠️ Development Commands

### Docker Management
```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f app

# Access container
docker-compose exec app sh
```

### Laravel Commands
```bash
# Run migrations
php artisan migrate

# Clear caches
php artisan cache:clear

# Create admin user
php artisan make:filament-user

# Optimize for production
php artisan optimize
```

## 📁 Project Structure

```
dream-pack-ecommerce/
├── app/                    # Laravel application code
├── database/               # Migrations and seeders
├── filament/               # Filament admin panels
├── public/                 # Public assets
├── resources/              # Views and frontend assets
├── routes/                 # Application routes
├── storage/                # File storage
├── docker/                 # Docker configuration
│   ├── entrypoint.sh       # Development entrypoint
│   └── entrypoint.prod.sh  # Production entrypoint
├── docker-compose.yml      # Local development
├── Dockerfile              # Production image
├── render.yaml             # Render deployment config
└── deploy.sh              # Quick deployment script
```

## 🔧 Configuration

### Environment Files
- `.env` - Local development
- `.env.example` - Template
- Render uses environment variables from `render.yaml`

### Database Configuration
- **Development**: PostgreSQL via Docker Compose
- **Production**: Render PostgreSQL service

### Caching Strategy
- **Development**: File cache
- **Production**: Redis for sessions and cache

## 🚨 Troubleshooting

### Common Issues

1. **Composer timeout during build**
   - Increase memory limit in Dockerfile
   - Use `COMPOSER_MEMORY_LIMIT=-1`

2. **Database connection errors**
   - Verify database service is running
   - Check connection string in environment

3. **Filament assets not loading**
   - Run `php artisan filament:assets`
   - Clear browser cache

4. **Permission issues**
   - Ensure storage directories are writable
   - Check file ownership

### Logs and Monitoring

```bash
# Application logs
docker-compose logs app

# Nginx logs (production)
docker exec <container> tail -f /var/log/nginx/error.log

# PHP-FPM logs
docker exec <container> tail -f /var/log/php8.4-fpm.log
```

## 🔐 Security

- Environment variables for secrets
- Production optimizations enabled
- File upload restrictions
- CSRF protection enabled
- SQL injection prevention via Eloquent

## 📈 Performance Optimizations

- OPcache enabled
- Route and view caching
- Redis for sessions and cache
- Gzip compression (Nginx)
- File minification and concatenation

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the MIT license.
