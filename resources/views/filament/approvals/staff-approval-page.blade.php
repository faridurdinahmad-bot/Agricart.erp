<div class="agricart-users-list">
    <div class="agricart-users-list__table-wrap">
        <table class="agricart-users-list__table">
            <thead>
                <tr>
                    <th>Staff No.</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Source</th>
                    <th>Join Date</th>
                    <th>Status</th>
                    <th>Assign Role</th>
                    <th class="agricart-users-list__actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->pendingUsers as $user)
                    <tr wire:key="pending-user-{{ $user->id }}">
                        <td>{{ $user->staff_no ?? '—' }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ str($user->registration_source->value)->headline() }}</td>
                        <td>{{ $user->join_date?->format('Y-m-d') ?? $user->created_at?->format('Y-m-d') }}</td>
                        <td>
                            <span class="agricart-users-list__badge agricart-users-list__badge--pending">Pending</span>
                        </td>
                        <td>
                            <select class="agricart-approvals__role-select" wire:model="approvalRoles.{{ $user->id }}">
                                <option value="">Select role</option>
                                @foreach ($this->assignableRoles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="agricart-users-list__actions-col">
                            @include('filament.approvals.partials.row-actions', ['userId' => $user->id])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No pending staff awaiting approval.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-filament-actions::modals />
</div>
