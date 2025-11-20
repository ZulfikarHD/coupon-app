<?php

namespace App\Console\Commands;

use App\Models\BlacklistedName;
use Illuminate\Console\Command;

class ManageBlacklistedNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blacklist:manage
                            {action : The action to perform (add, remove, list)}
                            {name? : The name to add or remove}
                            {--reason= : The reason for blacklisting (only for add action)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage blacklisted first names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $name = $this->argument('name');

        switch ($action) {
            case 'add':
                $this->addName($name);
                break;
            case 'remove':
                $this->removeName($name);
                break;
            case 'list':
                $this->listNames();
                break;
            default:
                $this->error("Invalid action. Use 'add', 'remove', or 'list'");
                return 1;
        }

        return 0;
    }

    /**
     * Add a name to the blacklist
     */
    protected function addName(?string $name): void
    {
        if (!$name) {
            $this->error('Name is required for add action');
            return;
        }

        $reason = $this->option('reason') ?? 'No reason provided';

        $blacklistedName = BlacklistedName::updateOrCreate(
            ['name' => strtolower(trim($name))],
            ['reason' => $reason]
        );

        if ($blacklistedName->wasRecentlyCreated) {
            $this->info("Name '{$name}' has been added to the blacklist.");
        } else {
            $this->info("Name '{$name}' was already in the blacklist. Reason updated.");
        }
    }

    /**
     * Remove a name from the blacklist
     */
    protected function removeName(?string $name): void
    {
        if (!$name) {
            $this->error('Name is required for remove action');
            return;
        }

        $deleted = BlacklistedName::where('name', strtolower(trim($name)))->delete();

        if ($deleted) {
            $this->info("Name '{$name}' has been removed from the blacklist.");
        } else {
            $this->warn("Name '{$name}' was not found in the blacklist.");
        }
    }

    /**
     * List all blacklisted names
     */
    protected function listNames(): void
    {
        $names = BlacklistedName::orderBy('name')->get();

        if ($names->isEmpty()) {
            $this->info('No blacklisted names found.');
            return;
        }

        $this->info('Blacklisted Names:');
        $this->newLine();

        $tableData = $names->map(function ($item) {
            return [
                'ID' => $item->id,
                'Name' => $item->name,
                'Reason' => $item->reason ?? 'N/A',
                'Created' => $item->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $this->table(
            ['ID', 'Name', 'Reason', 'Created'],
            $tableData
        );

        $this->newLine();
        $this->info('Total: ' . $names->count() . ' blacklisted names');
    }
}
