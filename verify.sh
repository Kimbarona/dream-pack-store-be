#!/bin/bash

echo "🔍 Dream Pack Ecommerce - Deployment Verification"
echo "=================================================="

# Check Docker containers
echo "📦 Checking Docker containers..."
docker-compose ps

echo ""
echo "🌐 Testing Application Endpoints..."

# Test main application
MAIN_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:10000 2>/dev/null)
if [ "$MAIN_STATUS" = "302" ]; then
    echo "✅ Main application: $MAIN_STATUS (Redirecting as expected)"
else
    echo "❌ Main application: $MAIN_STATUS"
fi

# Test admin login
ADMIN_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:10000/admin/login 2>/dev/null)
if [ "$ADMIN_STATUS" = "200" ]; then
    echo "✅ Admin login: $ADMIN_STATUS (Working)"
else
    echo "❌ Admin login: $ADMIN_STATUS"
fi

echo ""
echo "🔧 Configuration Check..."

# Check environment files
if [ -f ".env" ]; then
    echo "✅ .env file exists"
else
    echo "❌ .env file missing"
fi

if [ -f "render.yaml" ]; then
    echo "✅ render.yaml exists"
else
    echo "❌ render.yaml missing"
fi

if [ -f "Dockerfile" ]; then
    echo "✅ Dockerfile exists"
else
    echo "❌ Dockerfile missing"
fi

if [ -f "docker-compose.yml" ]; then
    echo "✅ docker-compose.yml exists"
else
    echo "❌ docker-compose.yml missing"
fi

echo ""
echo "📊 Container Resources..."
docker stats --no-stream dream-pack-ecommerce-app-1 dream-pack-ecommerce-db-1 2>/dev/null || echo "Containers not running"

echo ""
echo "🎯 Next Steps:"
echo "1. Test locally: http://localhost:10000/admin/login"
echo "2. Deploy to Render: ./deploy.sh"
echo "3. Access production: https://dream-pack-ecommerce.onrender.com/admin"
echo ""
echo "Default credentials: test@example.com / password"