<div class="agricart-users-list">
    <div class="agricart-users-list__toolbar">
        {{ $this->addRoleAction }}
    </div>

    <div class="agricart-users-list__table-wrap">
        <table class="agricart-users-list__table">
            <thead>
                <tr>
                    <th>Role Name</th>
                    <th>Description</th>
                    <th>Users</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th class="agricart-users-list__actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->roles as $role)
                    <tr wire:key="role-{{ $role->id }}">
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->description ?: '—' }}</td>
                        <td>{{ $role->users_count }}</td>
                        <td>{{ $role->isProtected() ? 'System' : 'Custom' }}</td>
                        <td>
                            <span @class([
                                'agricart-users-list__badge',
                                'agricart-users-list__badge--active' => $role->is_active,
                                'agricart-users-list__badge--inactive' => ! $role->is_active,
                            ])>
                                {{ $role->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="agricart-users-list__actions-col">
                            <button
                                type="button"
                                class="agricart-users-list__row-btn"
                                wire:click="openEditRole({{ $role->id }})"
                            >
                                Edit
                            </button>
                            @unless ($role->isProtected())
                                <button
                                    type="button"
                                    class="agricart-users-list__row-btn"
                                    wire:click="toggleRoleActive({{ $role->id }})"
                                >
                                    {{ $role->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button
                                    type="button"
                                    class="agricart-users-list__row-btn agricart-users-list__row-btn--danger"
                                    wire:click="deleteRole({{ $role->id }})"
                                    wire:confirm="Delete this role? This cannot be undone."
                                >
                                    Delete
                                </button>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No roles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-filament-actions::modals />
</div>
