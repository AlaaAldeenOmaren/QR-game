<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\AccountRules;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdministrator extends Command
{
    protected $signature = 'qr-game:create-admin {email : E-mailadres van de beheerder}';

    protected $description = 'Maak een beheerder aan of geef een bestaand account beheerdersrechten.';

    public function handle(): int
    {
        if (! Schema::hasColumn('users', 'is_admin')) {
            $this->error('De kolom is_admin ontbreekt. Voer eerst de nieuwe migratie uit.');

            return self::FAILURE;
        }

        $email = Str::lower(trim($this->argument('email')));
        $emailValidator = Validator::make(
            ['email' => $email],
            ['email' => ['required', 'string', 'email', 'max:255']],
            AccountRules::messages()
        );

        if ($emailValidator->fails()) {
            $this->error($emailValidator->errors()->first());

            return self::FAILURE;
        }

        $account = User::where('email', $email)->first();

        if ($account !== null) {
            if ($account->is_admin) {
                $this->info('Dit account is al beheerder. Er is niets gewijzigd.');

                return self::SUCCESS;
            }

            $this->line("Bestaand account: {$account->name} <{$account->email}> (ID {$account->id}).");
            $this->line('Het wachtwoord, de spellen en resultaten blijven behouden.');

            if (! $this->confirm('Wil je dit bestaande account beheerder maken?', false)) {
                $this->info('Geannuleerd. Er is niets gewijzigd.');

                return self::SUCCESS;
            }

            $account->is_admin = true;
            $account->save();

            $this->info('Het bestaande account heeft nu beheerdersrechten.');

            return self::SUCCESS;
        }

        $input = [
            'name' => trim((string) $this->ask('Naam van de beheerder')),
            'email' => $email,
            'password' => $this->secret('Wachtwoord (minimaal 12 tekens)', false),
            'password_confirmation' => $this->secret('Herhaal het wachtwoord', false),
        ];
        $validator = Validator::make($input, AccountRules::rules(), AccountRules::messages());

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $validated = $validator->validated();
        $account = new User();
        $account->name = $validated['name'];
        $account->email = $validated['email'];
        $account->password = Hash::make($validated['password']);
        $account->is_admin = true;

        try {
            $account->save();
        } catch (UniqueConstraintViolationException $exception) {
            $this->error('Dit e-mailadres is ondertussen in gebruik. Er is geen bestaand account gewijzigd.');

            return self::FAILURE;
        }

        $this->info("Beheerder aangemaakt: {$account->email}.");

        return self::SUCCESS;
    }
}
