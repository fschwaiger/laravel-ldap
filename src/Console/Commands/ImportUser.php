<?php

namespace Fschwaiger\Ldap\Console\Commands;

use Fschwaiger\Ldap\Core\Client as LdapClient;
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
    protected $description = 'Import specified user from active directory server.';

    /**
     * Ldap client that performs the search and bind operations.
     *
     * @var Client
     */
    protected $ldap;

    /**
     * Constructor injects dependencies.
     */
    public function __construct(LdapClient $ldap)
    {
        $this->ldap = $ldap;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->syncUser($this->argument('username'));
        $this->call('ldap:show-user', $this->arguments());
    }

    private function syncUser($username = null)
    {
        collect(config('ldap.user_folder_dns'))->flatMap(function ($dn) use ($username) {
            return $this->ldap->find($dn, "(sAMAccountName=$username)");
        })->each(function (Entry $entry) {
            $user = $this->updateUser($entry);
            $this->updateUserGroups($user, $entry);
        });
    }

    private function updateUser(Entry $entry)
    {
        return User::updateOrCreate([
            'guid' => $entry->objectGUID,
        ], [
            'username' => $entry->sAMAccountName,
            'imported_at' => date(),
            'email' => $entry->mail,
            'name' => $entry->name,
            'dn' => $entry->dn,
        ]);
    }

    private function updateUserGroups(User $user, Entry $entry)
    {
        $entries = $this->collectMemberOfEntries($entry);
        $user->groups()->sync(Group::whereIn('dn', $entries->pluck('dn'))->get());
    }

    private function collectMemberOfEntries(Entry $entry)
    {
        return $entry->memberOf->reject(function ($dn) {
            return preg_match(config('ldap.group_ignore_pattern'), $entry->dn);
        })->filter(function ($dn) {
            return collect(config('ldap.group_folder_dns'))->contains(function ($folder) use ($dn) { return ends_with($dn, $folder); });
        })->flatMap(function ($dn) {
            return $this->ldap->find($dn, '(objectclass=group)');
        })->flatMap(function (Entry $entry) {
            return $this->collectMemberOfEntries($entry)->prepend($entry);
        })->unique('dn');
    }
}
