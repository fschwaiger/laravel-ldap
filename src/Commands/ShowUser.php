<?php

namespace Fschwaiger\Ldap\Commands;

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
    protected $description = 'Show info for the given username.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = User::whereUsername($this->argument('username'))->firstOrFail();
        $this->showUserInfo($user);
    }

    private function showUserInfo(User $user)
    {
        $this->showPersonalInfo($user);
        $this->showPrivileges($user);
        $this->showGroupMemberships($user);
    }

    private function showPersonalInfo(User $user)
    {
        $this->info('Personal Data:');
        $this->line("  Identifier:  $user->username (ID: $user->id)");
        $this->line("  Email:       $user->email");
        $this->line("  Name:        $user->name");
    }

    private function showPrivileges(User $user)
    {
        $this->info('Privileges:');

        collect(config('privileges'))->filter(function ($dns) use ($user) {
            return $user->isMemberOfAny($dns);
        })->keys()->each(function ($privilege) {
            $this->line('  |__ ' . $privilege);
        });
    }

    private function showGroupMemberships(User $user)
    {
        $this->info("Member of:");
        
        $user->groups()->orderBy('name', 'asc')->get()->each(function ($group) {
            $this->line('  |__ ' . $group->dn);
        });
    }
}
