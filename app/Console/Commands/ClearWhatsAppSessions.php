<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ClearWhatsAppSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:clear-sessions {--force : Force clear without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear WhatsApp bot session files and optionally disconnect the bot';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 WhatsApp Session Cleaner');
        $this->line('');

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to clear all WhatsApp session files?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Clear local session files
        $this->info('Clearing local session files...');
        $this->clearLocalSessionFiles();

        // Try to call bot logout endpoint
        $this->info('Attempting to disconnect bot...');
        $this->disconnectBot();

        $this->line('');
        $this->info('✅ WhatsApp session cleanup completed!');
        $this->line('');
        $this->warn('📋 Next steps:');
        $this->line('1. Restart the WhatsApp bot');
        $this->line('2. Visit /admin/whatsapp to scan the QR code');
        $this->line('3. The bot will be ready for fresh authentication');

        return 0;
    }

    /**
     * Clear local WhatsApp session files
     */
    private function clearLocalSessionFiles()
    {
        try {
            $whatsappBotPath = base_path('whatsapp-bot');
            
            // Directories to clear
            $directoriesToClear = [
                $whatsappBotPath . '/auth_info',
                $whatsappBotPath . '/.wwebjs_auth',
                $whatsappBotPath . '/sessions',
                $whatsappBotPath . '/store'
            ];
            
            $clearedDirs = 0;
            foreach ($directoriesToClear as $dir) {
                if (is_dir($dir)) {
                    $this->deleteDirectory($dir);
                    $this->line("  ✅ Cleared: " . basename($dir));
                    $clearedDirs++;
                }
            }
            
            // Clear auth-related JSON files
            if (is_dir($whatsappBotPath)) {
                $files = glob($whatsappBotPath . '/*.json');
                $clearedFiles = 0;
                foreach ($files as $file) {
                    $filename = basename($file);
                    if (strpos($filename, 'auth') !== false || 
                        strpos($filename, 'session') !== false || 
                        strpos($filename, 'store') !== false) {
                        unlink($file);
                        $this->line("  ✅ Removed: " . $filename);
                        $clearedFiles++;
                    }
                }
            }
            
            if ($clearedDirs === 0 && $clearedFiles === 0) {
                $this->line("  ℹ️  No session files found to clear");
            }
            
        } catch (\Throwable $e) {
            $this->error("  ❌ Failed to clear local session files: " . $e->getMessage());
        }
    }

    /**
     * Try to disconnect the bot via API
     */
    private function disconnectBot()
    {
        try {
            $base = config('services.whatsapp_bot.url', 'http://127.0.0.1:3000');
            $res = Http::timeout(10)->post($base . '/logout');
            $data = $res->json();
            
            if ($data['success'] ?? false) {
                $this->line("  ✅ Bot disconnected successfully");
            } else {
                $this->line("  ⚠️  Bot may not be running or reachable");
            }
        } catch (\Throwable $e) {
            $this->line("  ⚠️  Bot is not reachable: " . $e->getMessage());
        }
    }

    /**
     * Recursively delete directory
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        return rmdir($dir);
    }
}