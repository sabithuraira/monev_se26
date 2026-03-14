<?php

namespace Database\Seeders;

use App\Models\MasterKako;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed users from master_kako (one per kab/kota).
     * Name: BPS {kode_bps}, Email: bps{kode_bps}@email.com, Password: password{kode_bps}
     * kode_kab from kode_bps (last 2 digits), kode_kec and kode_desa = "000".
     */
    public function run(): void
    {
        $kakos = MasterKako::orderBy('kode_bps')->get();

        foreach ($kakos as $kako) {
            $kodeBps = $kako->kode_bps;
            $kodeKab = strlen($kodeBps) >= 2 ? substr($kodeBps, -2) : str_pad($kodeBps, 2, '0', STR_PAD_LEFT);

            User::updateOrCreate(
                ['email' => 'bps' . $kodeBps . '@email.com'],
                [
                    'name' => 'BPS ' . $kodeBps,
                    'password' => Hash::make('password' . $kodeBps),
                    'kode_kab' => $kodeKab,
                    'kode_kec' => '000',
                    'kode_desa' => '000',
                ]
            );
        }

        User::updateOrCreate(
            ['email' => 'bps1600@email.com'],
            [
                'name' => 'BPS 1600',
                'password' => Hash::make('pass1600NYA'),
                'kode_kab' => '00',
                'kode_kec' => '000',
                'kode_desa' => '000',
            ]
        );
    }
}
