<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class ProvisionAdmin extends Command
{
    protected $signature = 'farmsuite:provision-admin {email : Email of an existing verified account}';

    protected $description = 'Interactively grant administrator access to an existing verified account';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No account exists with that email address.');

            return self::FAILURE;
        }

        if (! $user->hasVerifiedEmail()) {
            $this->error('Verify the account email before provisioning administrator access.');

            return self::FAILURE;
        }

        if (! $user->is_active) {
            $this->error('Activate the account before provisioning administrator access.');

            return self::FAILURE;
        }

        if (! $this->confirm("Grant administrator access to {$user->email}?")) {
            $this->line('No changes made.');

            return self::SUCCESS;
        }

        $adminRole = Role::findByName('admin', 'web');
        $user->syncRoles($adminRole);

        $this->info("Administrator access granted to {$user->email}.");

        return self::SUCCESS;
    }
}
