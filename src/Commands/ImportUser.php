<?php

namespace Fschwaiger\Ldap\Commands;

use Carbon\Carbon;
use Fschwaiger\Ldap\Core\Client;
use Fschwaiger\Ldap\Core\Entry;
use Fschwaiger\Ldap\Group;
use Fschwaiger\Ldap\User;
use Illuminate\Console\Command;

class ImportUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:import-user {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the specified user from the directory server.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->importUser($this->argument('username'));
        $this->call('ldap:show-user', $this->arguments());
    }

    private function importUser($username)
    {
        collect(config('ldap.user_folder_dns'))->flatMap(function ($dn) use ($username) {
            return app('ldap')->find($dn, "(sAMAccountName=$username)");
        })->each(function (Entry $entry) {
            $user = $this->updateUser($entry);
            $this->updateUserGroups($user, $entry);
        });
    }

    private function updateUser(Entry $entry)
    {
        return User::updateOrCreate([
            'username' => $entry->sAMAccountName,
        ], [
            'email' => $entry->mail,
            'name' => $entry->name,
        ]);
    }

    private function updateUserGroups(User $user, Entry $entry)
    {
        $memberOf = $this->collectGroupMemberships($entry);
        $user->groups()->sync(Group::whereIn('dn', $memberOf->pluck('dn'))->get());
    }

    private function collectGroupMemberships(Entry $entry)
    {
        return $entry->memberOf->reject(function ($dn) {
            return preg_match(config('ldap.group_ignore_pattern'), $dn);
        })->filter(function ($dn) {
            return collect(config('ldap.group_folder_dns'))->contains(function ($folder) use ($dn) { return ends_with($dn, $folder); });
        })->flatMap(function ($dn) {
            return app('ldap')->find($dn, '(objectclass=group)');
        })->flatMap(function (Entry $entry) {
            return $this->collectGroupMemberships($entry)->prepend($entry);
        })->unique('dn');
    }
}
