<?php

namespace Lyre\Commerce\Console\Commands;

use Illuminate\Console\Command;

class InstallCommerceCommand extends Command
{
    protected $signature = 'lyre:commerce:install {--seed-terms : Seed default payment terms if Billing is installed}';
    protected $description = 'Install Lyre Commerce: publish config/migrations and optionally seed payment terms';

    public function handle(): int
    {
        $this->call('vendor:publish', ['--tag' => 'lyre-commerce-config', '--force' => true]);
        $this->call('vendor:publish', ['--tag' => 'lyre-commerce-migrations', '--force' => true]);

        if ($this->option('seed-terms')) {
            $this->seedPaymentTerms();
        }

        $this->info('Lyre Commerce installed. Run php artisan migrate');
        return self::SUCCESS;
    }

    private function seedPaymentTerms(): void
    {
        try {
            if (class_exists('Lyre\\Billing\\Repositories\\Contracts\\PaymentTermRepositoryInterface')) {
                $repo = app('Lyre\\Billing\\Repositories\\Contracts\\PaymentTermRepositoryInterface');
                $defaults = [
                    ['name' => 'prepaid', 'reference' => 'prepaid'],
                    ['name' => 'cod', 'reference' => 'cod'],
                    ['name' => 'postpaid_net30', 'reference' => 'postpaid_net30'],
                    ['name' => 'deposit_balance', 'reference' => 'deposit_balance'],
                    ['name' => 'subscription', 'reference' => 'subscription'],
                ];
                foreach ($defaults as $term) {
                    $repo->firstOrCreate(['reference' => $term['reference']], $term);
                }
                $this->info('Seeded default payment terms.');
            } else {
                $this->warn('Billing payment terms repository not found; skipping.');
            }
        } catch (\Throwable $e) {
            $this->warn('Failed seeding payment terms: ' . $e->getMessage());
        }
    }
}


