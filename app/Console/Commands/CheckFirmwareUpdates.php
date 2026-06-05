<?php

namespace App\Console\Commands;

use App\Mail\FirmwareUpdateAlert;
use App\Models\UpdateMonitor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

#[Signature('monitor:firmware')]
#[Description('Check Cudy download pages for firmware updates')]
class CheckFirmwareUpdates extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $monitors = UpdateMonitor::all();

        foreach ($monitors as $monitor) {
            $this->info("Checking for updates: {$monitor->name}...");

            try {
                $response = Http::timeout(15)->get($monitor->url);

                if (!$response->ok()) {
                    $this->error("Failed to fetch {$monitor->url}");
                    continue;
                }

                $version = $this->parseVersion($response->body());
                
                if (!$version) {
                    $this->warn("Could not parse version from page.");
                    continue;
                }

                $this->info("Detected version: {$version} | Stored: {$monitor->last_version}");

                if ($version !== $monitor->last_version) {
                    $this->info("🚨 New version detected! Sending email...");

                    Mail::to(config('monitor.notify_email'))
                        ->send(new FirmwareUpdateAlert(
                            deviceName: $monitor->name,
                            newVersion: $version,
                            currentVersion: $monitor->last_version ?? 'unknown',
                            updateUrl: $monitor->url
                        ));

                    $monitor->update([
                        'last_version'    => $version,
                        'last_checked_at' => now(),
                    ]);
                } else {
                    $monitor->update(['last_checked_at' => now()]);
                    $this->info("No change.");
                }

            } catch (\Exception $e) {
                $this->error("Error: {$e->getMessage()}");
            }
        }
    }

    private function parseVersion(string $html): ?string
    {
        // Match the firmware version like "2.3.13" near the firmware section
        if (preg_match('/<div class="dl-row[^"]*left[^"]*">.*?<div class="main">\s*(\d+\.\d+\.\d+)\s*<\/div>/s', $html, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
