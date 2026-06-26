<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Hanya admin yang boleh melihat daftar semua user.
     */
    public function viewAny(User $authUser): bool
    {
        return $authUser->is_admin;
    }

    /**
     * Admin boleh lihat semua user; user biasa hanya boleh lihat profil sendiri.
     */
    public function view(User $authUser, User $user): bool
    {
        return $authUser->is_admin || $authUser->id === $user->id;
    }

    /**
     * Hanya admin yang boleh membuat user baru.
     */
    public function create(User $authUser): bool
    {
        return $authUser->is_admin;
    }

    /**
     * Admin boleh update semua user; user biasa hanya boleh update profil sendiri.
     */
    public function update(User $authUser, User $user): bool
    {
        return $authUser->is_admin || $authUser->id === $user->id;
    }

    /**
     * Hanya admin yang boleh menghapus, dan tidak boleh menghapus dirinya sendiri.
     */
    public function delete(User $authUser, User $user): bool
    {
        return $authUser->is_admin && $authUser->id !== $user->id;
    }

    /**
     * Hanya admin yang boleh reset password user lain.
     */
    public function resetPassword(User $authUser, User $user): bool
    {
        return $authUser->is_admin && $authUser->id !== $user->id;
    }
}