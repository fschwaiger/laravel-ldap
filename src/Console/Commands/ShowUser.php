<?php

namespace Fschwaiger\Ldap\Console\Commands;

use Illuminate\Console\Command;
use Fschwaiger\Ldap\User;

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
        $this->info('Personal Info:');
        $this->line("  Name:     $user->name");
        $this->line("  Email:    $user->email");
    }

    private function showSystemInfo(User $user)
    {
        $this->info('System Info:');
        $this->line("  Username: $user->username");
        $this->line("  DN:       $user->ldap_dn");
        $this->line("  GUID:     $user->ldap_guid");
        $this->line("  ID:       $user->id");
    }

    private function showProvileges(User $user)
    {
        $this->info('Privileges:');

        $this->line('  Grant:    ' . collect(config('auth.privileges.grant'))->filter(function ($dns) use ($user) {
            return $user->isMemberOfAny($dns);
        })->keys()->implode(', '));

        $this->line('  Deny:     ' . collect(config('auth.privileges.deny'))->filter(function ($dns) use ($user) {
            return $user->isMemberOfAny($dns);
        })->keys()->implode(', '));
    }

    private function showGroupMemberships(User $user)
    {
        $this->info("Member of:");
        
        $user->groups()->orderBy('name', 'asc')->get()->each(function ($group) {
            $this->line('  |__ ' . $group->ldap_dn);
        });
    }
}
