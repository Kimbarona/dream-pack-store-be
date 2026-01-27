{{-- Example Dashboard using new Skydash Layout --}}
@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">Dashboard</h1>
            <p class="text-muted mb-0">Welcome to Skydash Admin</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary btn-sm">
                <i class="bi bi-plus me-1"></i>New Item
            </button>
            <button class="btn btn-outline-secondary btn-sm" data-action="print">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>
@endsection

@section('content')
    <!-- Stats Widgets -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="bg-white rounded-lg shadow-sm p-4 text-center border-0">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" 
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-speedometer2 fs-4"></i>
                    </div>
                </div>
                <h5 class="mb-2 text-primary">Total Users</h5>
                <h3 class="mb-0">1,234</h3>
                <small class="text-muted">+12% from last month</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white rounded-lg shadow-sm p-4 text-center border-0">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success" 
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-cart-check fs-4"></i>
                    </div>
                </div>
                <h5 class="mb-2 text-success">Total Sales</h5>
                <h3 class="mb-0">$12,345</h3>
                <small class="text-muted">+23% from last month</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white rounded-lg shadow-sm p-4 text-center border-0">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 text-warning" 
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-box fs-4"></i>
                    </div>
                </div>
                <h5 class="mb-2 text-warning">Products</h5>
                <h3 class="mb-0">456</h3>
                <small class="text-muted">+5% from last month</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white rounded-lg shadow-sm p-4 text-center border-0">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-info bg-opacity-10 text-info" 
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-bar-chart fs-4"></i>
                    </div>
                </div>
                <h5 class="mb-2 text-info">Orders</h5>
                <h3 class="mb-0">789</h3>
                <small class="text-muted">+18% from last month</small>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="bg-white rounded-lg shadow-sm p-4 border-0">
                <h5 class="mb-4 border-bottom pb-3">Recent Activity</h5>
                
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="bi bi-person-plus text-muted"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium">New user registered</div>
                                <small class="text-muted">2 hours ago</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="bi bi-cash text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium">New order #1234</div>
                                <small class="text-muted">3 hours ago</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="bi bi-box text-warning"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium">Product updated</div>
                                <small class="text-muted">5 hours ago</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-info bg-opacity-10 me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="bi bi-envelope text-info"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium">Support ticket replied</div>
                                <small class="text-muted">6 hours ago</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
    .rounded-circle {
        border-radius: 50%;
    }
    .border-0 {
        border: none !important;
    }
    .shadow-sm {
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    }
    .bg-opacity-10 {
        background-color: rgba(255,255,255,0.1);
    }
    .fs-4 {
        font-size: 1.5rem;
    }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add your page-specific JavaScript here
            console.log('Dashboard loaded with Skydash layout');
        });
    </script>
@endpush