<?php

namespace Fschwaiger\Ldap\Console\Commands;

use Fschwaiger\Ldap\Core\Facade as Ldap;
use Illuminate\Console\Command;
use Fschwaiger\Ldap\Core\Entry;
use Fschwaiger\Ldap\Group;

class SyncGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:import-groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all groups from the active directory server.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->logChanges();
        $this->syncGroups();
    }

    private function logChanges()
    {
        Group::created(function (Group $group) { $this->info('Created ' . $group->ldap_dn); });
        Group::updated(function (Group $group) { $this->line('Updated ' . $group->ldap_dn); });
        Group::deleted(function (Group $group) { $this->warn('Deleted ' . $group->ldap_dn); });
    }

    private function syncGroups()
    {
        collect(config('ldap.group_folder_dns'))->flatMap(function ($dn) {
            return Ldap::find($dn, '(objectclass=group)');
        })->reject(function (Entry $entry) {
            return starts_with($entry->dn, 'CN=\#');  // TODO filter by regex
        })->map(function (Entry $entry) {
            return $this->syncGroup($entry);
        })->pluck('ldap_dn')->pipe(function ($dns) {
            Group::whereNotIn('ldap_dn', $dns)->delete();  
        });
    }

    private function syncGroup($entry)
    {
        return Group::updateOrCreate([
            'ldap_guid' => $entry->objectGUID,
        ],[
            'name'      => $entry->name,
            'email'     => $entry->mail,
            'ldap_dn'   => $entry->dn,
        ]);
    }
}
