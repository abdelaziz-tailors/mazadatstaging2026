<?php

namespace App\Console\Commands;

use App\Support\DataCleanup\ClearOldData as ClearOldDataService;
use Illuminate\Console\Command;

class ClearOldData extends Command
{
    protected $signature = 'data:clear-old
                            {--dry-run : Preview row counts without deleting anything}
                            {--force : Skip interactive confirmation (still requires token in non-interactive mode)}';

    protected $description = 'Delete old transactional data while keeping admins (type=admin) and categories';

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            $result = ClearOldDataService::run(dryRun: true);
            $this->displayCounts($result['counts'], 'Rows that would be affected');
            $this->newLine();
            $this->info('Dry run only. No data was deleted.');
            $this->line('Run with confirmation token to execute:');
            $this->line('  php artisan data:clear-old --force --no-interaction');
            $this->line('  (set DATA_CLEAR_CONFIRM='.ClearOldDataService::CONFIRMATION_TOKEN.' in env for CI)');

            return self::SUCCESS;
        }

        if (! $this->confirmDeletion()) {
            $this->warn('Aborted. No data was deleted.');

            return self::FAILURE;
        }

        try {
            $result = ClearOldDataService::run(confirm: ClearOldDataService::CONFIRMATION_TOKEN);
        } catch (\RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Old data cleared successfully.');
        $this->newLine();
        $this->displayDeleted($result['deleted']);
        $this->newLine();
        $this->displayCounts($result['after'], 'Remaining counts');

        return self::SUCCESS;
    }

    private function confirmDeletion(): bool
    {
        if ($this->option('force')) {
            $envToken = env('DATA_CLEAR_CONFIRM');
            if ($envToken === ClearOldDataService::CONFIRMATION_TOKEN) {
                return true;
            }

            if (! $this->input->isInteractive()) {
                $this->error(
                    'Non-interactive run requires DATA_CLEAR_CONFIRM='.ClearOldDataService::CONFIRMATION_TOKEN
                );

                return false;
            }
        }

        $preview = ClearOldDataService::run(dryRun: true);
        $this->displayCounts($preview['counts'], 'This will delete');
        $this->newLine();
        $this->warn('Keeps: admins (type=admin) and all categories.');
        $this->warn('Deletes: users, vendors, partners, auctions, products, sales, and related records.');
        $this->newLine();

        if (! $this->option('force')) {
            return $this->confirm('Do you want to continue?', false);
        }

        $token = $this->ask('Type "'.ClearOldDataService::CONFIRMATION_TOKEN.'" to confirm deletion');

        return $token === ClearOldDataService::CONFIRMATION_TOKEN;
    }

    private function displayCounts(array $counts, string $title): void
    {
        $this->info($title.':');
        $rows = [];
        foreach ($counts as $key => $value) {
            $rows[] = [str_replace('_', ' ', $key), number_format($value)];
        }
        $this->table(['Metric', 'Count'], $rows);
    }

    private function displayDeleted(array $deleted): void
    {
        $this->info('Deleted rows:');
        $rows = [];
        foreach ($deleted as $table => $count) {
            if ($count > 0) {
                $rows[] = [str_replace('_', ' ', $table), number_format($count)];
            }
        }

        if ($rows === []) {
            $this->line('  (no rows deleted)');

            return;
        }

        $this->table(['Table', 'Deleted'], $rows);
    }
}
