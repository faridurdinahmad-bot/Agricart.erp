@php($isProtected = $this->selectedRole?->isProtected() ?? false)

<div class="agricart-role-form">
    @if ($isProtected)
        <p class="agricart-role-form__note">
            Super Admin is a permanent system role. It cannot be deleted, renamed, deactivated, or have permissions removed.
        </p>
    @endif

    <div class="agricart-role-form__grid">
        <div class="agricart-role-form__field">
            <label class="agricart-role-form__label agricart-role-form__label--required" for="role_name">Role Name</label>
            <input
                id="role_name"
                type="text"
                class="agricart-role-form__control"
                wire:model="roleName"
                @disabled($isProtected)
                placeholder="Role name"
            >
            @error('roleName') <span class="agricart-role-form__error">{{ $message }}</span> @enderror
        </div>

        <div class="agricart-role-form__field">
            <label class="agricart-role-form__label" for="role_description">Description</label>
            <input
                id="role_description"
                type="text"
                class="agricart-role-form__control"
                wire:model="roleDescription"
                @disabled($isProtected)
                placeholder="Optional description"
            >
        </div>

        <div class="agricart-role-form__field agricart-role-form__field--inline">
            <label class="agricart-role-form__checkbox">
                <input type="checkbox" wire:model="roleIsActive" @disabled($isProtected)>
                Active
            </label>
        </div>
    </div>

    <div class="agricart-role-form__permissions">
        <div class="agricart-role-form__permissions-toolbar">
            <h3 class="agricart-role-form__permissions-title">Permissions</h3>
            <div class="agricart-role-form__permissions-actions">
                <button type="button" class="agricart-role-form__link-btn" wire:click="selectAllPermissions" @disabled($isProtected)>Select All</button>
                <button type="button" class="agricart-role-form__link-btn" wire:click="unselectAllPermissions" @disabled($isProtected)>Unselect All</button>
            </div>
        </div>

        @foreach ($this->permissionGroups as $group)
            <div class="agricart-role-form__module" wire:key="module-{{ $group['module'] }}">
                <div class="agricart-role-form__module-header">
                    <h4 class="agricart-role-form__module-title">{{ $group['module_label'] }}</h4>
                    <div class="agricart-role-form__permissions-actions">
                        <button type="button" class="agricart-role-form__link-btn" wire:click="selectModulePermissions('{{ $group['module'] }}')" @disabled($isProtected)>Select All</button>
                        <button type="button" class="agricart-role-form__link-btn" wire:click="unselectModulePermissions('{{ $group['module'] }}')" @disabled($isProtected)>Unselect All</button>
                    </div>
                </div>

                <div class="agricart-role-form__matrix-wrap">
                    <table class="agricart-role-form__matrix">
                        <thead>
                            <tr>
                                <th>Page</th>
                                @foreach (\App\Core\Authorization\Enums\PermissionAction::all() as $action)
                                    <th>{{ $action->label() }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group['pages'] as $page)
                                <tr wire:key="page-{{ $group['module'] }}-{{ $page['page'] }}">
                                    <td>{{ $page['page_label'] }}</td>
                                    @foreach ($page['actions'] as $action)
                                        @php($permissionKey = \App\Core\Authorization\PermissionCatalog::key($group['module'], $page['page'], $action))
                                        <td>
                                            <label class="agricart-role-form__perm-check">
                                                <input
                                                    type="checkbox"
                                                    value="{{ $permissionKey }}"
                                                    wire:model="selectedPermissions"
                                                    @disabled($isProtected)
                                                >
                                            </label>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>
