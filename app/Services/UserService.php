<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Apply filters to user query
     */
    public function applyFilters(Builder $query, Request $request): Builder
    {
        // Search filter (name, email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        return $query;
    }

    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['role'] = $data['role'] ?? 'user';

        return User::create($data);
    }

    /**
     * Update user information
     */
    public function update(User $user, array $data): User
    {
        // Update password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->fill($data);
        $user->save();

        return $user->fresh();
    }

    /**
     * Delete user
     */
    public function delete(User $user): bool
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            throw new \Exception('You cannot delete your own account.');
        }

        return $user->delete();
    }
}
