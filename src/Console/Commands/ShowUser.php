<?php

namespace Fschwaiger\Ldap\Console\Commands;

use Fschwaiger\Ldap\User;
use Illuminate\Console\Command;

class ShowUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:show-user {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show user info for the given username.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = User::whereUsername($this->argument('username'))->first();
        $this->showUserInfo($user);
    }

    private function showUserInfo(User $user)
    {
        $this->showPresonalInfo($user);
        $this->showSystemInfo($user);
        $this->showProvileges($user);
        $this->showGroupMemberships($user);
    }

    private function showPresonalInfo(User $user)
    {
        $this->info('Personal Data:');
        $this->line("  Username: $user->username ($user->id)");
        $this->line("  Email:    $user->email");
        $this->line("  Name:     $user->name");
    }

    private function showSystemInfo(User $user)
    {
        $this->info('Ldap Data:');
        $this->line("  Import:   $user->imported_at");
        $this->line("  Path:     $user->dn");
        $this->line("  Guid:     $user->guid");
    }

    private function showProvileges(User $user)
    {
        $this->info('Privileges:');

        $this->line('  Grant:    ' . collect(config('privileges.grant'))->filter(function ($dns) use ($user) {
            return $user->isMemberOfAny($dns);
        })->keys()->implode(', '));

        $this->line('  Deny:     ' . collect(config('privileges.deny'))->filter(function ($dns) use ($user) {
            return $user->isMemberOfAny($dns);
        })->keys()->implode(', '));
    }

    private function showGroupMemberships(User $user)
    {
        $this->info("Member of:");
        
        $user->groups()->orderBy('name', 'asc')->get()->each(function ($group) {
            $this->line('  |__ ' . $group->dn);
        });
    }
}
