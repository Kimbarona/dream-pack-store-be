# Skydash Admin Layout

A modern, responsive admin layout template for Laravel applications using Skydash design system.

## Features

- **Modern Design**: Based on Skydash admin theme
- **Responsive**: Mobile-first approach with collapsible sidebar
- **Accessibility**: Semantic HTML5 structure
- **Interactive**: JavaScript-powered navigation and interactions
- **Flexible**: Easy to customize and extend

## Layout Structure

```
resources/views/layouts/admin.blade.php
├── @include('layouts.partials.admin-navbar')    # Top navigation bar
├── @include('layouts.partials.admin-sidebar')   # Left sidebar navigation  
├── @include('layouts.partials.admin-footer')    # Footer section
└── @yield('content')                             # Main content area
```

## Partials

### Navigation (`layouts/partials/admin-navbar.blade.php`)
- Logo/brand display
- Search functionality
- Notifications dropdown
- User menu with logout

### Sidebar (`layouts/partials/admin-sidebar.blade.php`)
- User profile section
- Main navigation menu
- Footer with version info
- Active state management

### Footer (`layouts.partials/admin-footer.blade.php`)
- Copyright information
- Version display
- Quick action buttons

## Usage

### Basic Usage
```blade
@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-12">
            <h1>Dashboard</h1>
            <p>Welcome to the admin panel.</p>
        </div>
    </div>
@endsection
```

### With Page Header
```blade
@extends('layouts.admin')

@section('page-header')
    <h1>Dashboard</h1>
    <p>Manage your application from here.</p>
@ends

@section('page-actions')
    <button class="btn btn-primary">Add New</button>
@endsection

@section('content')
    <!-- Your content here -->
@endsection
```

### Custom Title
```blade
@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <!-- Your content here -->
@endsection
```

## Assets

### CSS Files
- `app-DXBpylaQ.css` - Main compiled styles
- `bootstrap.css` - Bootstrap framework styles  
- `admin-layout.css` - Layout-specific styles

### JavaScript Files
- `app-DRfqliMl.js` - Main compiled scripts
- `bootstrap.js` - Bootstrap initialization
- `admin-layout.js` - Layout interactions

## Features

### Responsive Design
- **Desktop (≥992px)**: Full sidebar, horizontal navigation
- **Tablet (768px-991px)**: Collapsible sidebar, hamburger menu
- **Mobile (<768px)**: Hidden sidebar with overlay, mobile menu

### Navigation States
- Active page highlighting based on current route
- Hover effects and transitions
- Mobile-friendly touch targets

### Interactive Elements
- Sidebar toggle with overlay
- Dropdown menus with animations
- Search functionality
- Notification system
- User menu with logout

### Customization
- CSS custom properties for easy theming
- Component-based structure
- Modular partials for reusability

## Routes Used

The layout automatically detects active routes for:
- `admin.dashboard`
- `admin.categories.*`
- `admin.products.*`
- `admin.orders.*`
- `admin.settings.*`

Additional menu items use placeholder href="#" for safe implementation.

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- iOS Safari 12+
- Android Chrome 60+