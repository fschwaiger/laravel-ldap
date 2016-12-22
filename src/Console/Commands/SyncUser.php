<?php

namespace Fschwaiger\Ldap\Console\Commands;

use Fschwaiger\Ldap\Core\Facade as Ldap;
use Illuminate\Console\Command;
use Fschwaiger\Ldap\Core\Entry;
use Fschwaiger\Ldap\Group;
use Fschwaiger\Ldap\User;

class SyncUser extends Command
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
    protected $description = 'Import specified user from active directory server.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->syncUser($this->argument('username'));
        $this->call('user:show', $this->arguments());
    }

    private function syncUser($username = null)
    {
        collect(config('ldap.user_folder_dns'))->flatMap(function ($dn) use ($username) {
            return Ldap::find($dn, "(sAMAccountName=$username)");
        })->each(function (Entry $entry) {
            $user = $this->updateUser($entry);
            $this->updateUserGroups($user, $entry);
        });
    }

    private function updateUser(Entry $entry)
    {
        return User::updateOrCreate([
            'ldap_guid' => $entry->objectGUID,
        ], [
            'email'    => $entry->mail,
            'name'     => $entry->name,
            'username' => $entry->sAMAccountName,
            'ldap_dn'  => $entry->dn,
        ]);
    }

    private function updateUserGroups(User $user, Entry $entry)
    {
        $entries = $this->collectMemberOfEntries($entry);
        $user->groups()->sync(Group::whereIn('ldap_dn', $entries->pluck('dn'))->get());
    }

    private function collectMemberOfEntries(Entry $entry)
    {
        return $entry->memberOf->reject(function ($dn) {
            return starts_with($dn, 'CN=\#'); // TODO generalize with regex
        })->filter(function ($dn) {
            return collect(config('ldap.group_folder_dns'))->contains(function ($folder) use ($dn) { return ends_with($dn, $folder); });
        })->flatMap(function ($dn) {
            return Ldap::find($dn, '(objectclass=group)');
        })->flatMap(function (Entry $entry) {
            return $this->collectMemberOfEntries($entry)->prepend($entry);
        })->unique('dn');
    }
}
