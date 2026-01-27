@extends('filament-panels::layout')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold">Manage Admin Roles</h2>
            <p class="text-gray-600">Assign roles to admin users and manage permissions.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Admin Users List -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Admin Users</h3>
                <div class="space-y-4">
                    @foreach($admins as $admin)
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium">{{ $admin->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $admin->email }}</p>
                                    <div class="flex gap-2 mt-2">
                                        @foreach($admin->roles as $role)
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($admin->is_active)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Role Assignment -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Available Roles</h3>
                <div class="space-y-3">
                    @foreach($roles as $role)
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <h4 class="font-medium">{{ $role->name }}</h4>
                            <p class="text-sm text-gray-600 mb-3">{{ $role->permissions->pluck('name')->implode(', ') }}</p>
                            
                            @if($currentUser && $currentUser->hasRole('super-admin'))
                                <form wire:submit="assignRole" class="space-y-3">
                                    <input type="hidden" name="role_id" value="{{ $role->id }}">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Assign to Admin:</label>
                                        <select name="admin_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select admin...</option>
                                            @foreach($admins->where('id', '!=', $currentUser->id ?? 0)->get() as $admin)
                                                <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Assign Role
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection