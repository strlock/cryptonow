<?php


namespace App\Repositories;


use App\Models\User;
use Illuminate\Support\Collection;

class UsersRepository
{
    public function getAllUsers(): Collection
    {
        return User::orderBy('created_at', 'DESC')->get();
    }
}
