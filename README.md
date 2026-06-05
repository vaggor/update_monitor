# Update Monitor

A Laravel-based background service that monitors device firmware download pages for new releases and sends email alerts when a new version is detected.

Built as a practical tool for tracking Cudy networking device firmware, it is designed to be easily extended to monitor any product page that publishes version numbers in HTML.

---

## Features

- **Automated version  fetches HTML from configured URLs and parses firmware version strings using a regex patternscraping** 
- **Change  compares the detected version against the last known version stored in the databasedetection** 
- **Email  sends a formatted HTML email notification when a new version is found, including the previous version, new version, and a direct link to the download pagealerts** 
- **Daily  runs automatically via Laravel's built-in task schedulerscheduling** 

---

## Tech Stack

| Layer        | Technology                        |
|--------------|-----------------------------------|
| Framework    | Laravel 13 (PHP 8.3+)             |
| Database     | DB of choice (configurable)             |
| HTTP Client  | Laravel `Http` facade (Guzzle)    |
| Mail         | Laravel `Mail` facade (Mailable)  |
| Scheduling   | Laravel Task Scheduler            |
| Testing      | PHPUnit 12                        |

---

## Architecture Overview

```

                  Laravel Scheduler                  
          Schedule::command('monitor:firmware')      
                      (daily)                        

                       
    Artisan CommandCheckFirmwareUpdates             
  app/Console/Commands/            
           
                       
   UpdateMonitor::  Eloquent Modelall()             
   (reads from DB)                  
          
  for each monitor entry:                       
   Http::get($  Fetch download page HTMLurl)                  
          
                       
   parseVersion($  Regex version extractionhtml)              
          
                       
              version changed?
             /              \
           YES               NO
                            
  Update timestamp   Send    email        
 alert  only, no email     via    Mail    
      
            
 Update DB with    
 new version +     
 timestamp         
   
```

---

## Project Structure

```
app/
 Console/
 Commands/   
 CheckFirmwareUpdates.php   # Core command: fetch, parse, notify       
 Mail/
 FirmwareUpdateAlert.php        # Mailable for email alerts   
 Models/
 UpdateMonitor.php              # Eloquent model for monitored devices   
config/
 monitor.php                        # Notification email address
database/
 factories/
 UpdateMonitorFactory.php       # Factory for seeding/testing   
 migrations/
 ..._create_update_monitor_table.php   
 seeders/
 UpdateMonitor.php    
resources/views/
 emails/
 firmware-update.blade.php      # HTML email template    
routes/
 console.php                        # Scheduler definition
```

---

## Database Schema

### `update_monitors`

| Column            | Type         | Description                                           |
|-------------------|--------------|-------------------------------------------------------|
| `id`              | bigint       | Primary key                                           |
| `name`            | string       | Human-readable device name (e.g. `AP3000D Firmware`)  |
| `url`             | string       | URL of the firmware download page                     |
| `last_version`    | string/null  | Last detected version string                          |
| `last_checked_at` | timestamp    | When the page was last successfully checked           |
| `created_at`      | timestamp    | Record creation time                                  |
| `updated_at`      | timestamp    | Record last update time                               |

---

## Setup

### Requirements

- PHP 8.3+
- Composer
- Node.js + npm
- An SMTP mail provider (or `log` driver for local dev)

### Install

```bash
git clone <repo-url> update_monitor
cd update_monitor

composer setup
```

The `composer setup` script will:
1. Install PHP and JS dependencies
2. Copy `.env.example` to `.env` and generate an app key
3. Run all database migrations
4. Build frontend assets

### Configure Environment

Edit `.env` and set the following:

```env
# Mail  use 'log' for local testing, 'smtp' for productiondriver 
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=secret
MAIL_FROM_ADDRESS=alerts@example.com

# The address that will receive firmware update alerts
MONITOR_NOTIFY_EMAIL=you@example.com
```

### Seed the Database

To add the default Cudy AP3000D monitor entry:

```bash
php artisan db:seed --class=UpdateMonitor
```

Or insert entries directly via Tinker:

```bash
php artisan tinker
```

```php
\App\Models\UpdateMonitor::create([
    'name'         => 'My Device Firmware',
    'url'          => 'https://example.com/firmware-download-page',
    'last_version' => null,
]);
```

---

## Usage

### Run Manually

```bash
php artisan monitor:firmware
```

Sample output:

```
Checking for updates: AP3000D Firmware...
Detected version: 2.3.14 | Stored: 2.3.13
```

### Run on a Schedule

Ensure the Laravel scheduler is registered with your system's cron by adding one entry:

```cron
* * * * * cd /path/to/update_monitor && php artisan schedule:run >> /dev/null 2>&1
```

The command is configured to run daily (see `routes/console.php`).

### Run the Development Server

```bash
composer dev
```

This starts the Laravel server, queue worker, log viewer (Pail), and Vite in one terminal via `concurrently`.

---

## Email Notification

When a new firmware version is detected, the recipient receives an HTML email:

- **Subject:** `Firmware Update:  v2.3.14 available`AP3000D 
- **Body:** Previous version, new version (highlighted in green), and a button linking directly to the download page

The email template is at `resources/views/emails/firmware-update.blade.php`.

---

## Adding More Devices

This tool is not limited to the AP3000D. Any device whose download page renders the version number inside a `<div class="dl-row ... left ..."><div class="main">X.X.X</div>` HTML structure can be monitored.

To track additional devices, insert new rows into the `update_monitors` table with the appropriate `name` and `url`.

To support pages with a different HTML structure, update or extend the `parseVersion()` method in `CheckFirmwareUpdates.php`.

---

## Testing

```bash
composer test
```

---
