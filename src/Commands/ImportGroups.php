<?php

namespace Fschwaiger\Ldap\Commands;

use Carbon\Carbon;
use Fschwaiger\Ldap\Core\Client;
use Fschwaiger\Ldap\Core\Entry;
use Fschwaiger\Ldap\Group;
use Illuminate\Console\Command;

class ImportGroups extends Command
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
        $this->startLoggingChanges();
        $this->importGroups();
    }

    /**
     * Attach listeners for database events to log all changes to the CLI.
     */
    private function startLoggingChanges()
    {
        Group::created(function (Group $group) { $this->info('Created ' . $group->dn); });
        Group::updated(function (Group $group) { $this->line('Updated ' . $group->dn); });
        Group::deleted(function (Group $group) { $this->warn('Deleted ' . $group->dn); });
    }

    /**
     * Iterate over all LDAP entries and update the database.
     */
    private function importGroups()
    {
        collect(config('ldap.group_folder_dns'))->flatMap(function ($dn) {
            return app('ldap')->find($dn, '(objectclass=group)');
        })->reject(function (Entry $entry) {
            return preg_match(config('ldap.group_ignore_pattern'), $entry->dn);
        })->map(function (Entry $entry) {
            return $this->syncGroup($entry);
        })->pluck('dn')->pipe(function ($dns) {
            Group::whereNotIn('dn', $dns)->delete();  
        });
    }

    /**
     * Update the database with the given ldap Entry. The primary field used
     * to match the entries is the GUID field. Since objects can be moved
     * on the directory server, the DN is not unique enough sometimes.
     */
    private function syncGroup(Entry $entry)
    {
        return Group::updateOrCreate([
            'guid' => $entry->objectGUID,
        ],[
            'imported_at' => Carbon::now(),
            'email' => $entry->mail,
            'name' => $entry->name,
            'dn' => $entry->dn,
        ]);
    }
}
