<x-filament-panels::page>
    <form wire:submit="save">
        <x-filament::section>
            <div class="space-y-6">
                <div class="grid gap-6 md:grid-cols-1 lg:grid-cols-2">
                    <div>
                        <x-filament::card>
                            <x-filament::header>
                                <x-filament::heading>Role Management</x-filament::heading>
                                <x-filament::heading.subtitle>Manage roles and assign permissions to admin users</x-filament::heading.subtitle>
                            </x-filament::header>
                            
                            <div class="space-y-4">
                                @foreach ($this->getTableRecords() as $role)
                                    <div class="border rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h3 class="font-semibold text-lg">{{ $role->name }}</h3>
                                                <p class="text-sm text-gray-600">{{ $role->permissions_count }} permissions</p>
                                            </div>
                                            <div>
                                                {{ $this->getTableActions()->setRecord($role) }}
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($role->permissions as $permission)
                                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                        {{ $permission->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-filament::card>
                    </div>
                    
                    <div>
                        <x-filament::card>
                            <x-filament::header>
                                <x-filament::heading>Permission Groups</x-filament::heading>
                                <x-filament::heading.subtitle>Available permission categories</x-filament::heading.subtitle>
                            </x-filament::header>
                            
                            <div class="space-y-3">
                                @foreach($this->getPermissionGroups() as $group => $permissions)
                                    <div class="border rounded-lg p-3">
                                        <h4 class="font-medium capitalize">{{ str_replace('_', ' ', $group) }}</h4>
                                        <div class="mt-2 grid grid-cols-2 gap-1">
                                            @foreach($permissions as $permission)
                                                <span class="text-xs text-gray-600">{{ $permission }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-filament::card>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </form>
</x-filament-panels::page>