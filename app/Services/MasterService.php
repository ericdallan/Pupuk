<?php

namespace App\Services;

use App\Models\Master;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class masterService
{
    /**
     * Update master profile
     *
     * @param array $data
     * @param master $master
     * @return master
     * @throws ValidationException
     */
    public function updateProfile(array $data, Master $master)
    {
        $rules = [
            'current_password' => 'required',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:masters,email,' . $master->id,
            'new_password' => 'nullable|min:8|confirmed',
        ];

        $messages = [
            'current_password.required' => 'Kata sandi saat ini diperlukan.',
            'name.required' => 'Nama diperlukan.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'email.required' => 'Email diperlukan.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'email.unique' => 'Email sudah digunakan.',
            'new_password.min' => 'Kata sandi baru harus minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (!Hash::check($data['current_password'], $master->password)) {
            throw new \Exception('Kata sandi saat ini salah.');
        }

        $master->name = $data['name'];
        $master->email = $data['email'];

        if (!empty($data['new_password'])) {
            $master->password = Hash::make($data['new_password']);
        }

        return $master;
    }
}
