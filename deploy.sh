# Quick Deploy Script for Render
# Usage: ./deploy.sh

echo "🚀 Deploying Dream Pack Ecommerce to Render..."

# Check if render CLI is installed
if ! command -v render &> /dev/null; then
    echo "❌ Render CLI not found. Installing..."
    curl -L https://render.com/install | sh
    export PATH="$HOME/.render/bin:$PATH"
fi

# Login to Render (if not already logged in)
echo "🔐 Checking Render authentication..."
if ! render whoami &> /dev/null; then
    echo "Please login to Render:"
    render login
fi

# Deploy the application
echo "📦 Deploying application..."
render deploy

echo "✅ Deployment initiated! Check your Render dashboard for progress."
echo "🌐 Your app will be available at: https://dream-pack-ecommerce.onrender.com"
echo "🔧 Admin panel at: https://dream-pack-ecommerce.onrender.com/admin"