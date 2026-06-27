<div class="agricart-users-list">
    <div class="agricart-users-list__toolbar agricart-users-list__toolbar--split">
        <div class="agricart-users-list__filters">
            <input
                type="search"
                class="agricart-users-list__filter-input"
                placeholder="Search name, email, staff no..."
                wire:model.live.debounce.300ms="search"
            >
            <select class="agricart-users-list__filter-select" wire:model.live="statusFilter">
                <option value="">All Statuses</option>
                @foreach (\App\Core\Authorization\Enums\UserStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
            <select class="agricart-users-list__filter-select" wire:model.live="roleFilter">
                <option value="">All Roles</option>
                @foreach ($this->filterRoles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>

        {{ $this->addStaffAction }}
    </div>

    <div class="agricart-users-list__table-wrap">
        <table class="agricart-users-list__table">
            <thead>
                <tr>
                    <th>Staff No.</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Approval</th>
                    <th>Join Date</th>
                    <th class="agricart-users-list__actions-col"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td>{{ $user->staff_no ?? '—' }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role?->name ?? '—' }}</td>
                        <td>
                            @php($status = $user->status)
                            <span @class([
                                'agricart-users-list__badge',
                                'agricart-users-list__badge--active' => $status === \App\Core\Authorization\Enums\UserStatus::Active,
                                'agricart-users-list__badge--inactive' => $status === \App\Core\Authorization\Enums\UserStatus::Inactive,
                                'agricart-users-list__badge--pending' => $status === \App\Core\Authorization\Enums\UserStatus::Pending,
                                'agricart-users-list__badge--rejected' => $status === \App\Core\Authorization\Enums\UserStatus::Rejected,
                                'agricart-users-list__badge--returned' => $status === \App\Core\Authorization\Enums\UserStatus::ReturnedForCorrection,
                            ])>
                                {{ $status->label() }}
                            </span>
                        </td>
                        <td>
                            @if ($user->approved_at)
                                <span class="agricart-users-list__badge agricart-users-list__badge--approved">Approved</span>
                            @elseif ($user->rejected_at)
                                <span class="agricart-users-list__badge agricart-users-list__badge--rejected">Rejected</span>
                            @elseif ($user->returned_at)
                                <span class="agricart-users-list__badge agricart-users-list__badge--returned">Returned</span>
                            @else
                                <span class="agricart-users-list__badge agricart-users-list__badge--pending">Pending</span>
                            @endif
                        </td>
                        <td>{{ $user->join_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="agricart-users-list__actions-col">
                            <button type="button" class="agricart-users-list__row-btn" aria-label="Edit" disabled>Edit</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-filament-actions::modals />
</div>
